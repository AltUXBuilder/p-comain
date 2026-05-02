<?php

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Models\GpSurgery;
use App\Models\SavedFilterPreset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * PATCH additions to PatientController
 * Add these methods to the existing PatientController class.
 */
class PatientFilterController extends Controller
{
    // ── Gap 1: Saved filter presets ───────────────────────────────────────────

    /**
     * Save the current search filters as a named preset for this staff member.
     * POST /patients/presets
     */
    public function savePreset(Request $request)
    {
        $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'filters' => ['required', 'array'],
        ]);

        $staff = Auth::guard('staff')->user();

        // Max 10 presets per staff member
        $count = SavedFilterPreset::where('staff_id', $staff->id)->count();
        if ($count >= 10) {
            return back()->withErrors(['presets' => 'Maximum 10 saved filter presets reached. Delete one to save a new one.']);
        }

        SavedFilterPreset::create([
            'staff_id' => $staff->id,
            'name'     => $request->name,
            'filters'  => $request->filters,
        ]);

        return back()->with('success', "Filter preset '{$request->name}' saved.");
    }

    /**
     * Delete a saved preset.
     * DELETE /patients/presets/{preset}
     */
    public function deletePreset(SavedFilterPreset $preset)
    {
        // Only owner can delete
        abort_unless($preset->staff_id === Auth::guard('staff')->id(), 403);
        $preset->delete();
        return back()->with('success', 'Filter preset deleted.');
    }

    // ── Gap 4: GP Surgery ODS search ─────────────────────────────────────────

    /**
     * Search GP surgeries by name or ODS code (JSON response for autocomplete).
     * GET /gp-surgeries/search?q=...
     */
    public function gpSurgerySearch(Request $request)
    {
        $q = $request->input('q', '');

        $surgeries = GpSurgery::where(function ($query) use ($q) {
            $query->where('name', 'like', "%{$q}%")
                  ->orWhere('ods_code', 'like', "%{$q}%")
                  ->orWhere('address', 'like', "%{$q}%");
        })
        ->orderBy('name')
        ->limit(15)
        ->get(['id', 'name', 'address', 'ods_code', 'phone']);

        return response()->json($surgeries);
    }
}
