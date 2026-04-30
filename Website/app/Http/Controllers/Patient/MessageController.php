<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(): View
    {
        // Group messages by consultation thread
        $threads = auth()->user()
            ->consultations()
            ->whereHas('messages', fn($q) => $q->where('internal_only', false))
            ->with([
                'product',
                'messages' => fn($q) => $q->where('internal_only', false)->latest()->limit(1),
            ])
            ->latest()
            ->get();

        $unreadCount = Message::where('user_id', auth()->id())
            ->where('sender_type', 'staff')
            ->where('internal_only', false)
            ->whereNull('read_at')
            ->count();

        return view('patient.messages', compact('threads', 'unreadCount'));
    }

    public function thread(Consultation $consultation): View
    {
        if ($consultation->user_id !== auth()->id()) abort(403);

        $messages = $consultation->messages()
            ->where('internal_only', false)
            ->with('sender')
            ->oldest()
            ->get();

        // Mark staff messages as read
        $consultation->messages()
            ->where('sender_type', 'staff')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('patient.message-thread', compact('consultation', 'messages'));
    }

    public function send(Request $request, Consultation $consultation): RedirectResponse
    {
        if ($consultation->user_id !== auth()->id()) abort(403);

        $request->validate(['body' => 'required|string|max:2000']);

        Message::create([
            'consultation_id' => $consultation->id,
            'user_id'         => auth()->id(),
            'sender_type'     => 'patient',
            'sender_id'       => auth()->id(),
            'body'            => $request->body,
            'internal_only'   => false,
        ]);

        return back()->with('success', 'Message sent.');
    }
}
