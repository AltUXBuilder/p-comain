<?php

namespace App\Http\Controllers\Dispensing;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DispensingLabel;
use App\Models\Prescription;
use App\Models\StockBatch;
use App\Services\DispensingLabelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DispensingController extends Controller
{
    public function __construct(private DispensingLabelService $service) {}

    // ── Dispensing queue ──────────────────────────────────────────────────────

    /**
     * GET /dispensing/queue
     *
     * Lists all prescriptions in 'sent_to_dispense' status.
     * Includes near-expiry batch alerts and cold chain flags.
     */
    public function queue(Request $request)
    {
        $query = Prescription::with(['patient', 'product', 'prescriber'])
            ->where('status', Prescription::STATUS_SENT_TO_DISPENSE)
            ->orderBy('sent_to_dispense_at', 'asc'); // oldest first

        // Cold chain filter
        if ($request->boolean('cold_chain')) {
            $query->whereHas('product', fn ($q) => $q->where('cold_chain', true));
        }

        $prescriptions = $query->paginate(25)->withQueryString();

        // Attach near-expiry batch warnings for each product in the queue
        $nearExpiryWarnings = [];
        foreach ($prescriptions as $rx) {
            $batches = $this->service->nearExpiryBatches($rx->product_id, 90);
            if ($batches->isNotEmpty()) {
                $nearExpiryWarnings[$rx->product_id] = $batches;
            }
        }

        $stats = [
            'total'      => Prescription::where('status', Prescription::STATUS_SENT_TO_DISPENSE)->count(),
            'cold_chain' => Prescription::where('status', Prescription::STATUS_SENT_TO_DISPENSE)
                ->whereHas('product', fn ($q) => $q->where('cold_chain', true))->count(),
        ];

        return view('dispensing.queue', compact('prescriptions', 'nearExpiryWarnings', 'stats'));
    }

    // ── Dispense single prescription ──────────────────────────────────────────

    /**
     * GET /dispensing/{prescription}/dispense
     *
     * Show the dispense screen: choose batch, format, confirm.
     */
    public function showDispense(Prescription $prescription)
    {
        abort_if(
            $prescription->status !== Prescription::STATUS_SENT_TO_DISPENSE,
            422,
            'Prescription is not in dispensing queue.'
        );

        $prescription->load(['patient', 'product', 'prescriber']);

        // Available batches for this product (FEFO ordered)
        $batches = StockBatch::available()
            ->where('product_id', $prescription->product_id)
            ->orderBy('expiry_date')
            ->get();

        // Near-expiry warning
        $nearExpiry = $this->service->nearExpiryBatches($prescription->product_id, 30);

        return view('dispensing.dispense', compact('prescription', 'batches', 'nearExpiry'));
    }

    /**
     * POST /dispensing/{prescription}/dispense
     *
     * Create the label and sign off.
     */
    public function dispense(Request $request, Prescription $prescription)
    {
        $request->validate([
            'stock_batch_id' => ['nullable', 'exists:stock_batches,id'],
            'format'         => ['required', 'in:standard,branded'],
        ]);

        $dispenser = Auth::guard('staff')->user();

        $batch = $request->stock_batch_id
            ? StockBatch::findOrFail($request->stock_batch_id)
            : null;

        try {
            $label = $this->service->createLabel($prescription, $dispenser, $batch, $request->format);
            $this->service->signOff($label, $dispenser);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('dispensing.label', $label)
            ->with('success', 'Prescription dispensed. Label generated.');
    }

    // ── Batch dispense ────────────────────────────────────────────────────────

    /**
     * POST /dispensing/batch
     *
     * Dispense multiple prescriptions at once (FEFO batch auto-selection).
     */
    public function batchDispense(Request $request)
    {
        $request->validate([
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['integer', 'exists:prescriptions,id'],
            'format' => ['required', 'in:standard,branded'],
        ]);

        $dispenser = Auth::guard('staff')->user();
        $results   = $this->service->batchCreate($request->ids, $dispenser, $request->format);

        // Sign off each successfully created label
        foreach ($results['created'] as $labelId) {
            $label = DispensingLabel::find($labelId);
            if ($label) {
                $this->service->signOff($label, $dispenser);
            }
        }

        $created = count($results['created']);
        $failed  = count($results['failed']);
        $message = "{$created} item(s) dispensed.";
        if ($failed) {
            $message .= " {$failed} failed.";
        }

        return back()->with('batch_results', $results)->with('success', $message);
    }

    // ── Label view / print ────────────────────────────────────────────────────

    /**
     * GET /dispensing/labels/{label}
     *
     * Shows label detail with print/download options.
     */
    public function showLabel(DispensingLabel $label)
    {
        $label->load(['prescription', 'patient', 'product', 'dispenser', 'stockBatch']);
        return view('dispensing.label', compact('label'));
    }

    /**
     * GET /dispensing/labels/{label}/pdf
     */
    public function labelPdf(DispensingLabel $label)
    {
        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'dispensing_label_viewed',
            entityType: 'dispensing_label',
            entityId:   $label->id,
            request:    request()
        );
        return $this->service->stream($label);
    }

    /**
     * GET /dispensing/labels/{label}/download
     */
    public function labelDownload(DispensingLabel $label)
    {
        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'dispensing_label_downloaded',
            entityType: 'dispensing_label',
            entityId:   $label->id,
            request:    request()
        );
        return $this->service->download($label);
    }
}
