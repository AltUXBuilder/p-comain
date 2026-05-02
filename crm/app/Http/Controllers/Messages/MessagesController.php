<?php

namespace App\Http\Controllers\Messages;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Consultation;
use App\Models\Message;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessagesController extends Controller
{
    // ── Inbox (all threads) ───────────────────────────────────────────────────

    public function index(Request $request)
    {
        // Group messages by patient, showing latest message per patient
        $threads = Message::with(['patient', 'consultation'])
            ->select('user_id')
            ->selectRaw('MAX(id) as latest_message_id')
            ->where('internal_only', false)
            ->groupBy('user_id')
            ->orderByDesc('latest_message_id')
            ->paginate(25);

        // Hydrate with latest message content
        $latestIds  = $threads->pluck('latest_message_id');
        $latestMsgs = Message::with(['patient', 'senderStaff'])
            ->whereIn('id', $latestIds)
            ->get()
            ->keyBy('id');

        // Count unread per patient
        $unreadCounts = Message::where('internal_only', false)
            ->whereNull('read_at')
            ->where('sender_type', 'patient')
            ->selectRaw('user_id, COUNT(*) as unread')
            ->groupBy('user_id')
            ->pluck('unread', 'user_id');

        return view('messages.index', compact('threads', 'latestMsgs', 'unreadCounts'));
    }

    // ── Consultation thread ───────────────────────────────────────────────────

    public function thread(Consultation $consultation)
    {
        $consultation->load(['patient', 'product']);

        $messages = Message::with(['senderStaff'])
            ->where('consultation_id', $consultation->id)
            ->orderBy('created_at')
            ->get();

        // Mark patient messages as read
        Message::where('consultation_id', $consultation->id)
            ->where('sender_type', 'patient')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'messages_thread_viewed',
            entityType: 'consultation',
            entityId:   $consultation->id,
            request:    request()
        );

        return view('messages.thread', compact('consultation', 'messages'));
    }

    // ── Patient thread (no consultation context) ──────────────────────────────

    public function patientThread(Patient $patient)
    {
        $messages = Message::with(['senderStaff', 'consultation'])
            ->where('user_id', $patient->id)
            ->where('internal_only', false)
            ->orderBy('created_at')
            ->get();

        Message::where('user_id', $patient->id)
            ->where('sender_type', 'patient')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('messages.patient-thread', compact('patient', 'messages'));
    }

    // ── Send message ──────────────────────────────────────────────────────────

    public function send(Request $request)
    {
        $request->validate([
            'patient_id'       => ['required', 'exists:users,id'],
            'consultation_id'  => ['nullable', 'exists:consultations,id'],
            'body'             => ['required', 'string', 'max:5000'],
            'internal_only'    => ['boolean'],
            'attachment'       => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $staff = Auth::guard('staff')->user();

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store(
                "messages/{$request->patient_id}",
                'private'
            );
        }

        $message = Message::create([
            'consultation_id' => $request->consultation_id,
            'user_id'         => $request->patient_id,
            'sender_type'     => 'staff',
            'sender_id'       => $staff->id,
            'body'            => $request->body,
            'internal_only'   => $request->boolean('internal_only'),
            'attachment_path' => $attachmentPath,
        ]);

        // Queue email notification to patient (unless internal-only)
        if (! $message->internal_only) {
            $patient = Patient::find($request->patient_id);
            if ($patient?->email) {
                \Illuminate\Support\Facades\Mail::to($patient->email)
                    ->queue(new \App\Mail\NewMessageMail($message, $patient));
            }
        }

        AuditLog::record(
            staffId:    $staff->id,
            action:     'message_sent',
            entityType: 'message',
            entityId:   $message->id,
            metadata:   ['internal_only' => $message->internal_only],
            request:    $request
        );

        if ($request->consultation_id) {
            return redirect()->route('messages.thread', $request->consultation_id)
                ->with('success', 'Message sent.');
        }
        return redirect()->route('messages.patient', $request->patient_id)
            ->with('success', 'Message sent.');
    }

    // ── Bulk messaging ────────────────────────────────────────────────────────

    public function bulk(Request $request)
    {
        if ($request->isMethod('GET')) {
            $categories = \App\Models\TreatmentCategory::orderBy('name')->get();
            return view('messages.bulk', compact('categories'));
        }

        $request->validate([
            'subject'             => ['required', 'string', 'max:200'],
            'body'                => ['required', 'string'],
            'filter_category_id'  => ['nullable', 'exists:treatment_categories,id'],
            'filter_status'       => ['nullable', 'string'],
        ]);

        // Build patient query based on filters
        $query = Patient::where('active', true)->whereNull('deceased');

        if ($catId = $request->filter_category_id) {
            $query->whereHas('consultations.product.treatment.category', fn ($q) => $q->where('id', $catId));
        }

        if ($status = $request->filter_status) {
            $query->whereHas('subscriptions', fn ($q) => $q->where('stripe_status', $status));
        }

        $recipients = $query->pluck('id');

        // Dispatch a queued job to send to each patient
        \App\Jobs\SendBulkMessage::dispatch(
            recipientIds: $recipients->toArray(),
            subject:      $request->subject,
            body:         $request->body,
            staffId:      Auth::guard('staff')->id()
        );

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'bulk_message_dispatched',
            entityType: null, entityId: null,
            metadata:   ['recipient_count' => $recipients->count(), 'subject' => $request->subject],
            request:    $request
        );

        return back()->with('success', "Bulk message queued for {$recipients->count()} patient(s).");
    }
}
