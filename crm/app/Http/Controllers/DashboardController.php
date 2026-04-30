<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $staff = Auth::guard('staff')->user();

        $stats = [
            'consultations_pending' => Consultation::where('status', 'awaiting_review')->count(),
            'consultations_flagged' => Consultation::where('status', 'flagged')->count(),
            'patients_total'        => Patient::where('deceased', false)->count(),
            'patients_flagged'      => Patient::where('risk_flagged', true)->count(),
        ];

        $recentConsultations = Consultation::with(['patient', 'product'])
            ->where('status', 'awaiting_review')
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'recentConsultations', 'staff'));
    }
}
