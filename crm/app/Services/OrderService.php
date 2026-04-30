<?php

namespace App\Services;

use App\Mail\OrderDispatchedMail;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Staff;
use Illuminate\Support\Facades\Mail;

class OrderService
{
    // ── Dispatch single order ─────────────────────────────────────────────────

    public function dispatch(
        Order  $order,
        Staff  $staff,
        string $carrier,
        string $trackingNumber,
        ?string $trackingUrl = null
    ): Order {
        if ($order->status !== Order::STATUS_PROCESSING) {
            throw new \RuntimeException("Order {$order->order_number} is not in 'Processing' status.");
        }

        $order->update([
            'status'         => Order::STATUS_DISPATCHED,
            'carrier'        => $carrier,
            'tracking_number' => $trackingNumber,
            'tracking_url'   => $trackingUrl ?? $order->trackingLink(),
            'dispatched_by'  => $staff->id,
            'dispatched_at'  => now(),
        ]);

        // Send dispatch confirmation email to patient
        $this->sendDispatchEmail($order);

        AuditLog::record(
            staffId:    $staff->id,
            action:     'order_dispatched',
            entityType: 'order',
            entityId:   $order->id,
            metadata:   [
                'order_number'    => $order->order_number,
                'carrier'         => $carrier,
                'tracking_number' => $trackingNumber,
            ],
            request: request()
        );

        return $order->fresh();
    }

    // ── Batch dispatch ────────────────────────────────────────────────────────

    /**
     * Dispatch multiple orders at once.
     * Each order in $dispatchData: ['id' => int, 'tracking_number' => string]
     * Carrier and format are shared across the batch.
     */
    public function batchDispatch(array $dispatchData, string $carrier, Staff $staff, ?string $manifestBatch = null): array
    {
        $results = ['dispatched' => [], 'failed' => []];
        $batchRef = $manifestBatch ?? 'MANIFEST-' . now()->format('Ymd-His');

        foreach ($dispatchData as $item) {
            try {
                $order = Order::findOrFail($item['id']);
                $this->dispatch($order, $staff, $carrier, $item['tracking_number']);

                if ($manifestBatch || $carrier === 'royal_mail') {
                    $order->update(['manifest_batch' => $batchRef]);
                }

                $results['dispatched'][] = $order->order_number;
            } catch (\Throwable $e) {
                $results['failed'][] = ['id' => $item['id'], 'reason' => $e->getMessage()];
            }
        }

        return $results;
    }

    // ── Mark delivered ────────────────────────────────────────────────────────

    public function markDelivered(Order $order, Staff $staff): Order
    {
        $order->update([
            'status'       => Order::STATUS_DELIVERED,
            'delivered_at' => now(),
        ]);

        AuditLog::record(
            staffId:    $staff->id,
            action:     'order_delivered',
            entityType: 'order',
            entityId:   $order->id,
            request:    request()
        );

        return $order;
    }

    // ── Failed delivery ───────────────────────────────────────────────────────

    public function markFailedDelivery(Order $order, Staff $staff, string $notes): Order
    {
        $order->update([
            'status'                => Order::STATUS_FAILED_DELIVERY,
            'failed_delivery_notes' => $notes,
        ]);

        AuditLog::record(
            staffId:    $staff->id,
            action:     'order_failed_delivery',
            entityType: 'order',
            entityId:   $order->id,
            metadata:   ['notes' => $notes],
            request:    request()
        );

        return $order;
    }

    // ── Returns ───────────────────────────────────────────────────────────────

    public function markReturned(Order $order, Staff $staff, string $reason): Order
    {
        $order->update([
            'status'             => Order::STATUS_RETURNED,
            'return_reason'      => $reason,
            'return_received_at' => now(),
            'return_handled_by'  => $staff->id,
        ]);

        AuditLog::record(
            staffId:    $staff->id,
            action:     'order_returned',
            entityType: 'order',
            entityId:   $order->id,
            metadata:   ['reason' => $reason],
            request:    request()
        );

        return $order;
    }

    // ── Reship ────────────────────────────────────────────────────────────────

    /**
     * Create a reship: clone the original order into a new 'processing' order
     * and link it back to the original.
     */
    public function reship(Order $original, Staff $staff): Order
    {
        $reship = Order::create([
            'order_number'          => 'ORD-RESHIP-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'user_id'               => $original->user_id,
            'prescription_id'       => $original->prescription_id,
            'status'                => Order::STATUS_PROCESSING,
            'subtotal'              => $original->subtotal,
            'vat_amount'            => $original->vat_amount,
            'shipping_cost'         => $original->shipping_cost,
            'total'                 => $original->total,
            'currency'              => $original->currency,
            'payment_method'        => $original->payment_method,
            'requires_cold_chain'   => $original->requires_cold_chain,
            'delivery_name'         => $original->delivery_name,
            'delivery_address_line_1' => $original->delivery_address_line_1,
            'delivery_address_line_2' => $original->delivery_address_line_2,
            'delivery_city'         => $original->delivery_city,
            'delivery_postcode'     => $original->delivery_postcode,
            'delivery_country'      => $original->delivery_country,
            'fulfilment_notes'      => 'RESHIP of ' . $original->order_number,
        ]);

        // Clone order items
        foreach ($original->items as $item) {
            $reship->items()->create($item->only([
                'product_id', 'product_name', 'product_strength',
                'product_form', 'quantity', 'unit_price', 'line_total', 'vat_rate',
            ]));
        }

        // Link original to this reship
        $original->update(['reship_order_id' => $reship->id]);

        AuditLog::record(
            staffId:    $staff->id,
            action:     'order_reshipped',
            entityType: 'order',
            entityId:   $reship->id,
            metadata:   ['original_order_id' => $original->id, 'original_order_number' => $original->order_number],
            request:    request()
        );

        return $reship;
    }

    // ── Royal Mail manifest CSV ───────────────────────────────────────────────

    /**
     * Generate a Royal Mail manifest CSV for a batch of dispatched orders.
     */
    public function royalMailManifest(string $manifestBatch): string
    {
        $orders = Order::with(['patient', 'items'])
            ->where('carrier', 'royal_mail')
            ->where('manifest_batch', $manifestBatch)
            ->where('status', Order::STATUS_DISPATCHED)
            ->get();

        $rows = [
            ['Tracking Number', 'Recipient Name', 'Address Line 1', 'Address Line 2', 'City', 'Postcode', 'Country', 'Order Number', 'Weight (g)', 'Cold Chain'],
        ];

        foreach ($orders as $order) {
            $rows[] = [
                $order->tracking_number,
                $order->delivery_name,
                $order->delivery_address_line_1,
                $order->delivery_address_line_2 ?? '',
                $order->delivery_city,
                $order->delivery_postcode,
                $order->delivery_country,
                $order->order_number,
                '', // weight — not stored, left blank for manual entry
                $order->requires_cold_chain ? 'YES' : 'NO',
            ];
        }

        $csv = '';
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', $v) . '"', $row)) . "\n";
        }

        return $csv;
    }

    // ── Dispatch email ────────────────────────────────────────────────────────

    private function sendDispatchEmail(Order $order): void
    {
        try {
            $patient = $order->patient;
            if ($patient?->email) {
                Mail::to($patient->email)->queue(new OrderDispatchedMail($order));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Dispatch email failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
