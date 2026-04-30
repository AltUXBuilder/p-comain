<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user()->load([
            'consultations' => fn($q) => $q->latest()->limit(3)->with('product'),
            'orders'        => fn($q) => $q->latest()->limit(3)->with('items.product'),
            'prescriptions' => fn($q) => $q->latest()->limit(3)->with('product'),
        ]);

        // Active subscriptions
        $subscriptions = $user->subscriptions()
            ->where('stripe_status', 'active')
            ->with('product')
            ->get();

        // Pending consultations awaiting prescriber review
        $pendingConsultations = $user->consultations()
            ->whereIn('status', ['submitted', 'under_review'])
            ->with('product')
            ->get();

        // Latest order in transit
        $activeOrder = $user->orders()
            ->where('status', 'dispatched')
            ->latest()
            ->first();

        // Unread messages
        $unreadMessages = $user->messages()
            ->where('sender_type', 'staff')
            ->whereNull('read_at')
            ->count();

        return view('patient.dashboard', compact(
            'user', 'subscriptions', 'pendingConsultations', 'activeOrder', 'unreadMessages'
        ));
    }
}
