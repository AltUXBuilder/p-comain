<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('marketing.contact');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:255',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:2000',
        ]);

        // In production, dispatch a job to send email to support
        // For now, log and redirect with success
        \Illuminate\Support\Facades\Log::info('Contact form submission', $validated);

        return back()->with('success', 'Thank you for your message. Our team will be in touch within 1–2 business days.');
    }
}
