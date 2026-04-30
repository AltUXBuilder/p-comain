<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(private OrderService $service) {}

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Order::with(['patient', 'items', 'dispatchedBy'])
            ->orderBy('created_at', 'desc');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', fn ($q2) =>
                      $q2->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name',  'like', "%{$search}%")
                         ->orWhere('email',       'like', "%{$search}%")
                  )
                  ->orWhere('tracking_number', 'like', "%{$search}%");
            });
        }
        if ($request->boolean('cold_chain')) {
            $query->where('requires_cold_chain', true);
        }
        if ($carrier = $request->input('carrier')) {
            $query->where('carrier', $carrier);
        }

        $orders = $query->paginate(25)->withQueryString();

        $counts = [];
        foreach (Order::STATUSES as $key => $label) {
            $counts[$key] = Order::where('status', $key)->count();
        }

        return view('orders.index', compact('orders', 'counts'));
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(Order $order)
    {
        $order->load(['patient', 'items.product', 'prescription', 'dispatchedBy', 'returnHandledBy', 'reshipOrder']);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'order_viewed',
            entityType: 'order',
            entityId:   $order->id,
            request:    request()
        );

        return view('orders.show', compact('order'));
    }

    // ── Dispatch single ───────────────────────────────────────────────────────

    public function dispatch(Request $request, Order $order)
    {
        $validated = $request->validate([
            'carrier'         => ['required', 'in:' . implode(',', array_keys(Order::CARRIERS))],
            'tracking_number' => ['required', 'string', 'max:100'],
            'tracking_url'    => ['nullable', 'url'],
        ]);

        try {
            $this->service->dispatch(
                $order,
                Auth::guard('staff')->user(),
                $validated['carrier'],
                $validated['tracking_number'],
                $validated['tracking_url'] ?? null
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('orders.show', $order)
            ->with('success', "Order {$order->order_number} dispatched. Patient notified by email.");
    }

    // ── Batch dispatch ────────────────────────────────────────────────────────

    /**
     * POST /orders/batch-dispatch
     *
     * Expects JSON body or form data with:
     *   carrier: string
     *   orders: [{ id: int, tracking_number: string }]
     */
    public function batchDispatch(Request $request)
    {
        $request->validate([
            'carrier'                    => ['required', 'in:' . implode(',', array_keys(Order::CARRIERS))],
            'orders'                     => ['required', 'array', 'min:1'],
            'orders.*.id'               => ['required', 'integer', 'exists:orders,id'],
            'orders.*.tracking_number'  => ['required', 'string'],
        ]);

        $results = $this->service->batchDispatch(
            $request->orders,
            $request->carrier,
            Auth::guard('staff')->user()
        );

        $dispatched = count($results['dispatched']);
        $failed     = count($results['failed']);

        return back()
            ->with('batch_results', $results)
            ->with('success', "{$dispatched} order(s) dispatched." . ($failed ? " {$failed} failed." : ''));
    }

    // ── Status transitions ────────────────────────────────────────────────────

    public function markDelivered(Order $order)
    {
        $this->service->markDelivered($order, Auth::guard('staff')->user());
        return back()->with('success', 'Order marked as delivered.');
    }

    public function markFailedDelivery(Request $request, Order $order)
    {
        $request->validate(['notes' => ['required', 'string', 'max:500']]);
        $this->service->markFailedDelivery($order, Auth::guard('staff')->user(), $request->notes);
        return back()->with('success', 'Order marked as failed delivery.');
    }

    public function markReturned(Request $request, Order $order)
    {
        $request->validate(['reason' => ['required', 'string', 'max:500']]);
        $this->service->markReturned($order, Auth::guard('staff')->user(), $request->reason);
        return back()->with('success', 'Return recorded.');
    }

    // ── Reship ────────────────────────────────────────────────────────────────

    public function reship(Order $order)
    {
        $reship = $this->service->reship($order, Auth::guard('staff')->user());
        return redirect()->route('orders.show', $reship)
            ->with('success', "Reship order {$reship->order_number} created.");
    }

    // ── Royal Mail manifest ───────────────────────────────────────────────────

    public function manifestExport(Request $request)
    {
        $request->validate(['manifest_batch' => ['required', 'string']]);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'royal_mail_manifest_exported',
            entityType: 'order',
            entityId:   null,
            metadata:   ['manifest_batch' => $request->manifest_batch],
            request:    $request
        );

        $csv      = $this->service->royalMailManifest($request->manifest_batch);
        $filename = "royal-mail-manifest-{$request->manifest_batch}.csv";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ── Update fulfilment notes ───────────────────────────────────────────────

    public function updateNotes(Request $request, Order $order)
    {
        $request->validate([
            'fulfilment_notes' => ['nullable', 'string', 'max:2000'],
            'packing_notes'    => ['nullable', 'string', 'max:2000'],
        ]);
        $order->update($request->only(['fulfilment_notes', 'packing_notes']));
        return back()->with('success', 'Notes saved.');
    }
}
