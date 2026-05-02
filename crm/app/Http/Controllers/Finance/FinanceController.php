<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class FinanceController extends Controller
{
    public function __construct(private FinanceService $service) {}

    /**
     * PATCH: dashboard() — add ARR calculation and dunning summary.
     *
     * ARR = MRR × 12 (from active subscriptions).
     * Add to the existing dashboard() return data.
     */
    public function dashboard()
    {
        $revenueByMonth    = $this->service->revenueByMonth(12);
        $revenueByCategory = $this->service->revenueByCategory();
        $outstanding       = $this->service->outstandingPayments();
        $dunning           = $this->service->dunningQueue();

        // MRR from active Cashier subscriptions (simplified: count × avg price)
        $activeSubCount = \Illuminate\Support\Facades\DB::table('subscriptions')
            ->where('stripe_status', 'active')
            ->count();

        // Use revenue-based MRR: last month's revenue / active subscribers
        $lastMonthRevenue = \App\Models\Order::whereNotIn('status', ['cancelled', 'refunded', 'pending_payment'])
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at',  now()->subMonth()->year)
            ->sum('total');

        $mrr = $lastMonthRevenue;
        $arr = $mrr * 12;

        $stats = [
            'total_revenue_30d' => Order::whereNotIn('status', ['cancelled', 'refunded', 'pending_payment'])
                ->where('created_at', '>=', now()->subDays(30))
                ->sum('total'),
            'total_orders_30d'  => Order::whereNotIn('status', ['cancelled', 'refunded', 'pending_payment'])
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'outstanding_count' => $outstanding->count(),
            'outstanding_value' => $outstanding->sum('total'),
            'mrr'               => $mrr,
            'arr'               => $arr,
            'dunning_count'     => $dunning->count(),
        ];

        return view('finance.dashboard', compact(
            'revenueByMonth', 'revenueByCategory', 'outstanding', 'dunning', 'stats'
        ));
    }

    // ── Gap 7: Dunning management ─────────────────────────────────────────────

    /**
     * GET /finance/dunning
     */
    public function dunning()
    {
        $queue = $this->service->dunningQueue();
        return view('finance.dunning', compact('queue'));
    }

    /**
     * POST /finance/dunning/{invoice}/retry
     * Trigger a manual payment retry via Stripe.
     */
    public function retryPayment(Invoice $invoice)
    {
        try {
            $result = $this->service->retryStripePayment($invoice);
            $msg = $result ? 'Payment retry successful.' : 'Retry initiated — awaiting Stripe response.';
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Stripe retry failed: ' . $e->getMessage()]);
        }

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'invoice_payment_retried',
            entityType: 'invoice',
            entityId:   $invoice->id,
            request:    request()
        );

        return back()->with('success', $msg);
    }

    // ── Gap 8: Refund & chargeback management ─────────────────────────────────

    /**
     * GET /finance/refunds
     */
    public function refunds()
    {
        $refunded = Order::where('status', Order::STATUS_REFUNDED)
            ->with(['patient', 'items'])
            ->orderByDesc('updated_at')
            ->paginate(25);

        $chargebacks = \App\Models\StaffNotification::where('type', 'chargeback')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('finance.refunds', compact('refunded', 'chargebacks'));
    }

    /**
     * POST /finance/refunds/{order}/stripe
     * Issue a Stripe refund for an order.
     */
    public function issueRefund(Request $request, Order $order)
    {
        $request->validate([
            'amount_pence' => ['required', 'integer', 'min:1'],
            'reason'       => ['required', 'in:duplicate,fraudulent,requested_by_customer'],
        ]);

        if (! $order->stripe_charge_id && ! $order->stripe_payment_intent_id) {
            return back()->withErrors(['error' => 'No Stripe charge ID on this order. Cannot issue automated refund.']);
        }

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            $refund = $stripe->refunds->create([
                'charge'   => $order->stripe_charge_id,
                'amount'   => $request->amount_pence,
                'reason'   => $request->reason,
                'metadata' => ['order_number' => $order->order_number, 'staff_id' => Auth::guard('staff')->id()],
            ]);

            // Webhook will update the order status, but we can optimistically update here
            if ($refund->status === 'succeeded') {
                $order->update(['status' => Order::STATUS_REFUNDED]);
            }

            AuditLog::record(
                staffId:    Auth::guard('staff')->id(),
                action:     'stripe_refund_issued',
                entityType: 'order',
                entityId:   $order->id,
                metadata:   [
                    'refund_id'    => $refund->id,
                    'amount_pence' => $request->amount_pence,
                    'reason'       => $request->reason,
                ],
                request:    $request
            );

            return back()->with('success', 'Refund of £' . number_format($request->amount_pence / 100, 2) . ' issued successfully.');

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return back()->withErrors(['error' => 'Stripe error: ' . $e->getMessage()]);
        }
    }
}
