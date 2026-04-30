<?php

namespace App\Http\Controllers\Prescriptions;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\PrescriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SignatureController extends Controller
{
    public function __construct(private PrescriptionService $service) {}

    /**
     * Show signature setup page.
     * Used in two contexts:
     *   1. First-login prompt for prescribers with no signature on file
     *   2. Profile Settings → Signature tab
     *
     * GET /profile/signature
     */
    public function show()
    {
        $staff = Auth::guard('staff')->user();
        return view('profile.signature', compact('staff'));
    }

    /**
     * Save signature from canvas pad.
     *
     * POST /profile/signature
     *
     * Expects { signature: "data:image/png;base64,..." } JSON body
     * (sent by the canvas component's Alpine fetch handler).
     */
    public function save(Request $request)
    {
        $request->validate([
            'signature' => ['required', 'string', 'starts_with:data:image/png;base64,'],
        ]);

        $staff = Auth::guard('staff')->user();
        $path  = $this->service->savePrescriberSignature($staff, $request->signature);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'path' => $path]);
        }

        return back()->with('success', 'Signature saved successfully.');
    }

    /**
     * Delete saved signature (Super Admin or own account only).
     *
     * DELETE /profile/signature
     */
    public function destroy()
    {
        $staff = Auth::guard('staff')->user();

        \Illuminate\Support\Facades\Storage::disk('private')->delete($staff->signature_path ?? '');

        $staff->update([
            'signature_path'   => null,
            'signature_set_at' => null,
        ]);

        AuditLog::record(
            staffId:    $staff->id,
            action:     'signature_deleted',
            entityType: 'staff',
            entityId:   $staff->id,
            request:    request()
        );

        return back()->with('success', 'Signature removed.');
    }
}
