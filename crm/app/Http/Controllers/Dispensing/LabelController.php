<?php

namespace App\Http\Controllers\Dispensing;

use App\Http\Controllers\Controller;
use App\Models\DispensingLabel;
use App\Services\DispensingLabelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LabelController extends Controller
{
    public function __construct(private DispensingLabelService $service) {}

    /**
     * GET /labels
     *
     * Print queue — all labels, filterable. Supports bulk print selection.
     */
    public function index(Request $request)
    {
        $query = DispensingLabel::with(['prescription', 'patient', 'product', 'dispenser'])
            ->orderBy('dispensed_at', 'desc');

        if ($request->boolean('unprinted')) {
            $query->where('printed', false);
        }

        if ($coldChain = $request->boolean('cold_chain')) {
            $query->where('cold_chain', true);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                  ->orWhere('medication_name', 'like', "%{$search}%")
                  ->orWhereHas('prescription', fn ($q2) =>
                      $q2->where('prescription_number', 'like', "%{$search}%")
                  );
            });
        }

        $labels = $query->paginate(30)->withQueryString();

        $stats = [
            'total'     => DispensingLabel::count(),
            'unprinted' => DispensingLabel::where('printed', false)->count(),
            'cold_chain' => DispensingLabel::where('cold_chain', true)->count(),
        ];

        return view('dispensing.labels', compact('labels', 'stats'));
    }

    /**
     * POST /labels/regenerate/{label}
     *
     * Re-generate a label PDF (e.g. if format changes or data corrected).
     */
    public function regenerate(DispensingLabel $label)
    {
        $path = $this->service->generatePdf($label);
        $label->update(['pdf_path' => $path]);

        \App\Models\AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'dispensing_label_regenerated',
            entityType: 'dispensing_label',
            entityId:   $label->id,
            request:    request()
        );

        return back()->with('success', 'Label PDF regenerated.');
    }

    /**
     * POST /labels/mark-printed
     *
     * Mark one or more labels as printed (for physical print tracking).
     */
    public function markPrinted(Request $request)
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer', 'exists:dispensing_labels,id'],
        ]);

        DispensingLabel::whereIn('id', $request->ids)
            ->update(['printed' => true, 'printed_at' => now()]);

        return back()->with('success', count($request->ids) . ' label(s) marked as printed.');
    }
}
