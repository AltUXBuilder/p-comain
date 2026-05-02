<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FinanceService
{
    // ── Revenue metrics ───────────────────────────────────────────────────────

    /**
     * MRR: sum of monthly subscription revenue for the current period.
     * Uses active subscriptions with monthly billing from the subscriptions table.
     */
    public function mrr(): float
    {
        return (float) DB::table('subscriptions')
            ->join('subscription_items', 'subscriptions.id', '=', 'subscription_items.subscription_id')
            ->where('subscriptions.stripe_status', 'active')
            ->sum('subscription_items.quantity');
        // Note: actual £ value requires Stripe price lookup — this returns quantity units.
        // Override with Stripe SDK call in production for accurate MRR.
    }

    /**
     * Total revenue for a given period, grouped by month.
     */
    public function revenueByMonth(int $months = 12): \Illuminate\Support\Collection
    {
        return DB::table('orders')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total) as revenue, COUNT(*) as orders')
            ->where('status', 'NOT IN', ['cancelled', 'refunded', 'pending_payment'])
            ->whereRaw('created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)', [$months])
            ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m")')
            ->orderBy('month')
            ->get();
    }

    /**
     * Revenue breakdown by treatment category.
     */
    public function revenueByCategory(\DateTime $from = null, \DateTime $to = null): \Illuminate\Support\Collection
    {
        $query = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products',    'order_items.product_id', '=', 'products.id')
            ->join('treatments',  'products.treatment_id', '=', 'treatments.id')
            ->join('treatment_categories', 'treatments.treatment_category_id', '=', 'treatment_categories.id')
            ->selectRaw('treatment_categories.name as category, SUM(order_items.line_total) as revenue, COUNT(DISTINCT orders.id) as orders')
            ->whereNotIn('orders.status', ['cancelled', 'refunded', 'pending_payment'])
            ->groupBy('treatment_categories.id', 'treatment_categories.name')
            ->orderByDesc('revenue');

        if ($from) $query->where('orders.created_at', '>=', $from);
        if ($to)   $query->where('orders.created_at', '<=', $to);

        return $query->get();
    }

    // ── VAT report ────────────────────────────────────────────────────────────

    /**
     * VAT summary for a period.
     * Most POMs are zero-rated; this groups by VAT rate.
     */
    public function vatReport(\DateTime $from, \DateTime $to): array
    {
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->selectRaw('order_items.vat_rate, SUM(order_items.line_total) as net, SUM(order_items.line_total * order_items.vat_rate / 100) as vat_total, COUNT(*) as line_items')
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->whereBetween('orders.created_at', [$from, $to])
            ->groupBy('order_items.vat_rate')
            ->get();

        $summary = [
            'period_from'  => $from->format('d M Y'),
            'period_to'    => $to->format('d M Y'),
            'rows'         => $rows,
            'total_net'    => $rows->sum('net'),
            'total_vat'    => $rows->sum('vat_total'),
            'total_gross'  => $rows->sum('net') + $rows->sum('vat_total'),
        ];

        return $summary;
    }

    /**
     * Export VAT report as CSV.
     */
    public function vatReportCsv(\DateTime $from, \DateTime $to): string
    {
        $report = $this->vatReport($from, $to);

        $rows = [
            ['VAT Rate (%)', 'Net Revenue (£)', 'VAT Amount (£)', 'Gross (£)', 'Line Items'],
        ];

        foreach ($report['rows'] as $row) {
            $net   = number_format($row->net, 2);
            $vat   = number_format($row->vat_total, 2);
            $gross = number_format($row->net + $row->vat_total, 2);
            $rows[] = [
                number_format($row->vat_rate, 2),
                $net,
                $vat,
                $gross,
                $row->line_items,
            ];
        }

        $rows[] = ['', '', '', '', ''];
        $rows[] = [
            'TOTAL',
            number_format($report['total_net'], 2),
            number_format($report['total_vat'], 2),
            number_format($report['total_gross'], 2),
            '',
        ];

        $csv = '';
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"', $row)) . "\n";
        }

        return $csv;
    }

    // ── Invoice generation ────────────────────────────────────────────────────

    public function generateInvoice(Order $order): Invoice
    {
        // Idempotent — return existing invoice if one exists for this order
        $existing = Invoice::where('order_id', $order->id)->where('status', '!=', 'void')->first();
        if ($existing) return $existing;

        $invoice = Invoice::create([
            'user_id'    => $order->user_id,
            'order_id'   => $order->id,
            'subtotal'   => $order->subtotal,
            'vat_amount' => $order->vat_amount,
            'total'      => $order->total,
            'currency'   => $order->currency,
            'status'     => 'issued',
            'issued_at'  => now(),
        ]);

        $pdfPath = $this->generateInvoicePdf($invoice);
        $invoice->update(['pdf_path' => $pdfPath]);

        return $invoice;
    }

    public function generateInvoicePdf(Invoice $invoice): string
    {
        $invoice->load(['patient', 'order.items']);

        $pharmacy = [
            'name'        => config('crm.pharmacy.name'),
            'address'     => config('crm.pharmacy.address'),
            'gphc_number' => config('crm.pharmacy.gphc_number'),
            'email'       => config('crm.pharmacy.email'),
        ];

        $pdf = Pdf::loadView('finance.invoice-pdf', compact('invoice', 'pharmacy'))
            ->setPaper('a4', 'portrait')
            ->setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => false]);

        $path = "invoices/{$invoice->invoice_number}.pdf";
        Storage::disk('private')->put($path, $pdf->output());
        return $path;
    }

    public function streamInvoice(Invoice $invoice): \Symfony\Component\HttpFoundation\Response
    {
        if (! $invoice->pdf_path || ! Storage::disk('private')->exists($invoice->pdf_path)) {
            $this->generateInvoicePdf($invoice);
        }
        return response()->file(
            Storage::disk('private')->path($invoice->pdf_path),
            ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline']
        );
    }

    // ── Outstanding payments ──────────────────────────────────────────────────

    public function outstandingPayments(): \Illuminate\Database\Eloquent\Collection
    {
        return Invoice::with(['patient', 'order'])
            ->where('status', 'issued')
            ->whereNull('paid_at')
            ->where('issued_at', '<=', now()->subDays(7)) // overdue after 7 days
            ->orderBy('issued_at')
            ->get();
    }

    // ── Stripe webhook reconciliation ─────────────────────────────────────────

    /**
     * Reconcile an incoming Stripe webhook event.
     * Updates invoice status based on payment_intent or charge events.
     */
    public function reconcileStripeEvent(string $eventType, array $stripeObject): void
    {
        $intentId = $stripeObject['id'] ?? null;
        $chargeId  = $stripeObject['latest_charge'] ?? $stripeObject['id'] ?? null;

        match($eventType) {
            'payment_intent.succeeded' => $this->markPaidByStripeIntent($intentId),
            'charge.refunded'          => $this->markRefundedByCharge($chargeId),
            'charge.dispute.created'   => $this->flagDisputeByCharge($chargeId),
            default                    => null,
        };
    }

    private function markPaidByStripeIntent(string $intentId): void
    {
        Order::where('stripe_payment_intent_id', $intentId)->update([
            'status' => Order::STATUS_PAYMENT_CONFIRMED,
        ]);

        Invoice::whereHas('order', fn ($q) => $q->where('stripe_payment_intent_id', $intentId))
            ->update(['status' => 'paid', 'paid_at' => now()]);
    }

    private function markRefundedByCharge(string $chargeId): void
    {
        Order::where('stripe_charge_id', $chargeId)->update([
            'status' => Order::STATUS_REFUNDED,
        ]);
    }

    private function flagDisputeByCharge(string $chargeId): void
    {
        \Illuminate\Support\Facades\Log::warning('Stripe chargeback dispute created', ['charge_id' => $chargeId]);

        // Notify finance staff
        \App\Models\StaffNotification::insert(
            \App\Models\Staff::whereIn('role', ['super_admin', 'finance'])->get()
                ->map(fn ($s) => [
                    'staff_id'    => $s->id,
                    'type'        => 'chargeback',
                    'message'     => "Stripe chargeback dispute received for charge {$chargeId}.",
                    'entity_type' => 'order',
                    'entity_id'   => null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ])->toArray()
        );
    }

    // ── Dunning queue ──────────────────────────────────────────────────────────

    public function dunningQueue(): Collection
    {
        return Invoice::with(['patient', 'order'])
            ->where('status', 'issued')
            ->whereNull('paid_at')
            ->where('issued_at', '<=', now()->subDays(7))
            ->orderBy('issued_at')
            ->get()
            ->map(function (Invoice $inv) {
                $daysOverdue = $inv->issued_at->diffInDays(now());
                $inv->days_overdue = $daysOverdue;
                $inv->dunning_tier = match(true) {
                    $daysOverdue >= 30 => 'final',
                    $daysOverdue >= 14 => 'second',
                    default            => 'first',
                };
                return $inv;
            });
    }

    // ── Gap 8: Retry Stripe payment ───────────────────────────────────────────

    /**
     * Attempt to collect payment on a past-due Stripe invoice.
     */
    public function retryStripePayment(Invoice $invoice): bool
    {
        if (! $invoice->stripe_invoice_id) {
            throw new \RuntimeException('Invoice has no Stripe invoice ID. Cannot retry.');
        }

        $stripe   = new \Stripe\StripeClient(config('services.stripe.secret'));
        $stripeInv = $stripe->invoices->pay($invoice->stripe_invoice_id, [
            'forgive' => false,
        ]);

        if ($stripeInv->status === 'paid') {
            $invoice->update(['status' => 'paid', 'paid_at' => now()]);
            return true;
        }

    public function retryStripePayment(Invoice $invoice): bool
    {
        if (! $invoice->stripe_invoice_id) {
            throw new \RuntimeException('Invoice has no Stripe invoice ID. Cannot retry.');
        }

        $stripe   = new \Stripe\StripeClient(config('services.stripe.secret'));
        $stripeInv = $stripe->invoices->pay($invoice->stripe_invoice_id, [
            'forgive' => false,
        ]);

        if ($stripeInv->status === 'paid') {
            $invoice->update(['status' => 'paid', 'paid_at' => now()]);
            return true;
        }

        return false;
    }
}
}
