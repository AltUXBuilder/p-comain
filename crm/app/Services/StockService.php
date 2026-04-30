<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Staff;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\StockWriteOff;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class StockService
{
    // ── Batch intake ──────────────────────────────────────────────────────────

    /**
     * Record a new stock batch received from a supplier.
     * Updates the product's aggregate stock level.
     */
    public function receiveBatch(
        Product   $product,
        Supplier  $supplier,
        string    $batchNumber,
        string    $expiryDate,
        int       $quantityReceived,
        float     $unitCost,
        Staff     $receivedBy,
        bool      $coldChainMaintained = true,
        ?string   $receivedDate = null
    ): StockBatch {
        return DB::transaction(function () use (
            $product, $supplier, $batchNumber, $expiryDate,
            $quantityReceived, $unitCost, $receivedBy,
            $coldChainMaintained, $receivedDate
        ) {
            $batch = StockBatch::create([
                'product_id'           => $product->id,
                'supplier_id'          => $supplier->id,
                'batch_number'         => $batchNumber,
                'expiry_date'          => $expiryDate,
                'quantity_received'    => $quantityReceived,
                'quantity_remaining'   => $quantityReceived,
                'unit_cost'            => $unitCost,
                'received_date'        => $receivedDate ?? today()->toDateString(),
                'received_by'          => $receivedBy->id,
                'cold_chain_maintained' => $coldChainMaintained,
                'status'               => 'active',
            ]);

            // Update aggregate stock
            $stock = Stock::firstOrCreate(
                ['product_id' => $product->id],
                ['minimum_threshold' => 10]
            );
            $stock->increment('quantity_on_hand', $quantityReceived);

            // Reset low-stock alert flag now we have stock
            if ($stock->alert_sent && ! $stock->isBelowThreshold()) {
                $stock->update(['alert_sent' => false]);
            }

            AuditLog::record(
                staffId:    $receivedBy->id,
                action:     'stock_batch_received',
                entityType: 'stock_batch',
                entityId:   $batch->id,
                metadata:   [
                    'product_id'        => $product->id,
                    'batch_number'      => $batchNumber,
                    'quantity_received' => $quantityReceived,
                ],
                request: request()
            );

            return $batch;
        });
    }

    // ── Write-off ─────────────────────────────────────────────────────────────

    /**
     * Write off quantity from a batch (damaged, expired, returned, recalled).
     */
    public function writeOff(
        StockBatch $batch,
        int        $quantity,
        string     $reason,
        Staff      $staff,
        ?string    $notes = null
    ): StockWriteOff {
        if ($quantity > $batch->quantity_remaining) {
            throw new \RuntimeException("Cannot write off {$quantity} — only {$batch->quantity_remaining} remaining in this batch.");
        }

        return DB::transaction(function () use ($batch, $quantity, $reason, $staff, $notes) {
            $writeOff = StockWriteOff::create([
                'stock_batch_id' => $batch->id,
                'product_id'     => $batch->product_id,
                'written_off_by' => $staff->id,
                'quantity'       => $quantity,
                'reason'         => $reason,
                'notes'          => $notes,
            ]);

            $batch->decrement('quantity_remaining', $quantity);

            if ($batch->quantity_remaining <= 0) {
                $batch->update(['status' => $reason === 'recalled' ? 'recalled' : 'written_off']);
            }

            // Update aggregate stock
            Stock::where('product_id', $batch->product_id)
                ->decrement('quantity_on_hand', $quantity);

            AuditLog::record(
                staffId:    $staff->id,
                action:     'stock_written_off',
                entityType: 'stock_write_off',
                entityId:   $writeOff->id,
                metadata:   [
                    'batch_number' => $batch->batch_number,
                    'quantity'     => $quantity,
                    'reason'       => $reason,
                ],
                request: request()
            );

            // Check threshold after write-off
            $this->checkThreshold($batch->product_id, $staff);

            return $writeOff;
        });
    }

    // ── Reconciliation ────────────────────────────────────────────────────────

    /**
     * Reconcile aggregate stock against sum of active batch quantities.
     * Corrects any drift from unrecorded movements.
     */
    public function reconcile(int $productId, Staff $staff): Stock
    {
        $actual = StockBatch::where('product_id', $productId)
            ->where('status', 'active')
            ->sum('quantity_remaining');

        $stock = Stock::where('product_id', $productId)->firstOrFail();
        $before = $stock->quantity_on_hand;

        $stock->update([
            'quantity_on_hand'   => $actual,
            'last_reconciled_at' => now(),
        ]);

        AuditLog::record(
            staffId:    $staff->id,
            action:     'stock_reconciled',
            entityType: 'stock',
            entityId:   $stock->id,
            metadata:   ['product_id' => $productId, 'before' => $before, 'after' => $actual],
            request:    request()
        );

        return $stock->fresh();
    }

    // ── Threshold check ───────────────────────────────────────────────────────

    public function checkThreshold(int $productId, ?Staff $actor = null): void
    {
        $stock = Stock::where('product_id', $productId)->first();
        if (! $stock) return;

        if ($stock->isBelowThreshold() && ! $stock->alert_sent) {
            // Dispatch notification to superintendent pharmacist and super admin
            \App\Models\StaffNotification::insert(
                \App\Models\Staff::whereIn('role', ['super_admin', 'superintendent_pharmacist'])
                    ->get()
                    ->map(fn ($s) => [
                        'staff_id'   => $s->id,
                        'type'       => 'low_stock',
                        'message'    => "Low stock alert: " . optional($stock->product)->name . " ({$stock->quantity_on_hand} remaining, threshold: {$stock->minimum_threshold})",
                        'entity_type' => 'stock',
                        'entity_id'  => $stock->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                    ->toArray()
            );

            $stock->update(['alert_sent' => true]);
        }
    }

    // ── Near-expiry report ────────────────────────────────────────────────────

    public function nearExpiryReport(int $thresholdDays = 90): \Illuminate\Database\Eloquent\Collection
    {
        return StockBatch::with(['product', 'supplier'])
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays($thresholdDays))
            ->orderBy('expiry_date')
            ->get();
    }
}
