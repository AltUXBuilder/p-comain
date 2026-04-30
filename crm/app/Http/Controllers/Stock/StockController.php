<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\StockWriteOff;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    public function __construct(private StockService $service) {}

    // ── Stock dashboard ───────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Stock::with('product')
            ->when($request->input('search'), fn ($q, $s) =>
                $q->whereHas('product', fn ($q2) => $q2->where('name', 'like', "%{$s}%"))
            )
            ->when($request->input('status') === 'low',     fn ($q) => $q->low())
            ->when($request->input('status') === 'out',     fn ($q) => $q->outOfStock())
            ->orderByRaw('quantity_on_hand <= minimum_threshold DESC') // low stock first
            ->orderBy('quantity_on_hand');

        $stock = $query->paginate(25)->withQueryString();

        $stats = [
            'total_products' => Stock::count(),
            'low_stock'      => Stock::low()->count(),
            'out_of_stock'   => Stock::outOfStock()->count(),
        ];

        // Near-expiry batches (next 30 days) for the alert strip
        $nearExpiry30 = $this->service->nearExpiryReport(30);

        return view('stock.index', compact('stock', 'stats', 'nearExpiry30'));
    }

    // ── Product stock detail ──────────────────────────────────────────────────

    public function show(Product $product)
    {
        $stock   = Stock::where('product_id', $product->id)->firstOrFail();
        $batches = StockBatch::with('supplier')
            ->where('product_id', $product->id)
            ->orderByRaw("FIELD(status,'active','depleted','written_off','recalled')")
            ->orderBy('expiry_date')
            ->get();

        $writeOffs = StockWriteOff::with('staff')
            ->where('product_id', $product->id)
            ->latest()
            ->limit(20)
            ->get();

        $suppliers = Supplier::where('active', true)->orderBy('name')->get();

        return view('stock.show', compact('product', 'stock', 'batches', 'writeOffs', 'suppliers'));
    }

    // ── Receive batch ─────────────────────────────────────────────────────────

    public function receiveBatch(Request $request, Product $product)
    {
        $validated = $request->validate([
            'supplier_id'          => ['required', 'exists:suppliers,id'],
            'batch_number'         => ['required', 'string', 'max:100'],
            'expiry_date'          => ['required', 'date', 'after:today'],
            'quantity_received'    => ['required', 'integer', 'min:1'],
            'unit_cost'            => ['required', 'numeric', 'min:0'],
            'received_date'        => ['nullable', 'date'],
            'cold_chain_maintained' => ['boolean'],
        ]);

        $supplier = Supplier::findOrFail($validated['supplier_id']);

        $this->service->receiveBatch(
            product:              $product,
            supplier:             $supplier,
            batchNumber:          $validated['batch_number'],
            expiryDate:           $validated['expiry_date'],
            quantityReceived:     $validated['quantity_received'],
            unitCost:             $validated['unit_cost'],
            receivedBy:           Auth::guard('staff')->user(),
            coldChainMaintained:  $validated['cold_chain_maintained'] ?? true,
            receivedDate:         $validated['received_date'] ?? null
        );

        return back()->with('success', "Batch {$validated['batch_number']} received ({$validated['quantity_received']} units).");
    }

    // ── Write-off ─────────────────────────────────────────────────────────────

    public function writeOff(Request $request, StockBatch $batch)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:' . $batch->quantity_remaining],
            'reason'   => ['required', 'in:' . implode(',', array_keys(StockWriteOff::REASONS))],
            'notes'    => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->service->writeOff(
                $batch,
                $validated['quantity'],
                $validated['reason'],
                Auth::guard('staff')->user(),
                $validated['notes'] ?? null
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', "{$validated['quantity']} unit(s) written off ({$validated['reason']}).");
    }

    // ── Update threshold ──────────────────────────────────────────────────────

    public function updateThreshold(Request $request, Product $product)
    {
        $request->validate(['minimum_threshold' => ['required', 'integer', 'min:0']]);

        Stock::where('product_id', $product->id)
            ->update(['minimum_threshold' => $request->minimum_threshold]);

        return back()->with('success', 'Minimum threshold updated.');
    }

    // ── Reconcile ─────────────────────────────────────────────────────────────

    public function reconcile(Product $product)
    {
        $stock = $this->service->reconcile($product->id, Auth::guard('staff')->user());
        return back()->with('success', "Stock reconciled. On-hand: {$stock->quantity_on_hand}");
    }

    // ── Near-expiry report ────────────────────────────────────────────────────

    public function nearExpiry(Request $request)
    {
        $days    = (int) $request->input('days', 90);
        $batches = $this->service->nearExpiryReport($days);
        return view('stock.near-expiry', compact('batches', 'days'));
    }

    // ── Suppliers ─────────────────────────────────────────────────────────────

    public function suppliers()
    {
        $suppliers = Supplier::withCount('batches')->orderBy('name')->paginate(20);
        return view('stock.suppliers', compact('suppliers'));
    }

    public function storeSupplier(Request $request)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:200'],
            'contact_name' => ['nullable', 'string', 'max:200'],
            'email'        => ['nullable', 'email', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:30'],
            'address'      => ['nullable', 'string', 'max:500'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        Supplier::create($validated);
        return back()->with('success', 'Supplier added.');
    }

    // ── Purchase orders ───────────────────────────────────────────────────────

    public function purchaseOrders()
    {
        $orders = PurchaseOrder::with(['supplier', 'creator', 'items'])
            ->orderByRaw("FIELD(status,'draft','sent','partial','received','cancelled')")
            ->latest()
            ->paginate(20);
        return view('stock.purchase-orders', compact('orders'));
    }

    public function storePurchaseOrder(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'    => ['required', 'exists:suppliers,id'],
            'expected_date'  => ['nullable', 'date'],
            'notes'          => ['nullable', 'string', 'max:1000'],
            'items'          => ['required', 'array', 'min:1'],
            'items.*.product_id'       => ['required', 'exists:products,id'],
            'items.*.quantity_ordered' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost'        => ['nullable', 'numeric', 'min:0'],
        ]);

        $po = PurchaseOrder::create([
            'supplier_id'   => $validated['supplier_id'],
            'created_by'    => Auth::guard('staff')->id(),
            'expected_date' => $validated['expected_date'] ?? null,
            'notes'         => $validated['notes'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            $po->items()->create($item);
        }

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'purchase_order_created',
            entityType: 'purchase_order',
            entityId:   $po->id,
            metadata:   ['po_number' => $po->po_number],
            request:    $request
        );

        return back()->with('success', "Purchase order {$po->po_number} created.");
    }

    public function updatePurchaseOrderStatus(Request $request, PurchaseOrder $po)
    {
        $request->validate(['status' => ['required', 'in:draft,sent,received,partial,cancelled']]);
        $po->update(['status' => $request->status]);
        if ($request->status === 'received') {
            $po->update(['received_date' => today()]);
        }
        return back()->with('success', 'Purchase order updated.');
    }
}
