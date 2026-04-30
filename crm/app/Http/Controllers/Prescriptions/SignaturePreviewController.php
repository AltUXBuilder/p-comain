<?php

namespace App\Http\Controllers\Prescriptions;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SignaturePreviewController extends Controller
{
    /**
     * Serve the authenticated staff member's own signature PNG.
     * Never exposes another staff member's signature.
     *
     * GET /profile/signature/preview
     */
    public function __invoke()
    {
        $staff = Auth::guard('staff')->user();

        if (! $staff->signature_path) {
            abort(404);
        }

        if (! Storage::disk('private')->exists($staff->signature_path)) {
            abort(404);
        }

        return response()->file(
            Storage::disk('private')->path($staff->signature_path),
            ['Content-Type' => 'image/png']
        );
    }
}
