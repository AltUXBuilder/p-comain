<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Cashier\Subscription;

class SubscriptionController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $subscriptions = auth()->user()
            ->subscriptions()
            ->with('product')
            ->get();

        return view('patient.subscriptions', compact('subscriptions'));
    }

    public function pause(Request $request, Subscription $subscription): RedirectResponse
    {
        $this->authorise($subscription);
        $subscription->pause();
        return back()->with('success', 'Your subscription has been paused.');
    }

    public function resume(Request $request, Subscription $subscription): RedirectResponse
    {
        $this->authorise($subscription);
        $subscription->resume();
        return back()->with('success', 'Your subscription has been resumed.');
    }

    public function cancel(Request $request, Subscription $subscription): RedirectResponse
    {
        $this->authorise($subscription);
        $subscription->cancel();
        return back()->with('success', 'Your subscription has been cancelled. It will remain active until the end of the current billing period.');
    }

    private function authorise(Subscription $subscription): void
    {
        if ($subscription->user_id !== auth()->id()) abort(403);
    }
}
