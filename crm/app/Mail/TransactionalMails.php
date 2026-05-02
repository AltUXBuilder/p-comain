<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// ─────────────────────────────────────────────────────────────────────────────
// Consultation Approved
// ─────────────────────────────────────────────────────────────────────────────

class ConsultationApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public \App\Models\Consultation $consultation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'Prescribe & Co'),
            subject: 'Your consultation has been approved — Prescribe & Co',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.comms.consultation-approved', with: [
            'consultation' => $this->consultation,
            'patient'      => $this->consultation->patient,
        ]);
    }

    public function attachments(): array { return []; }
}

// ─────────────────────────────────────────────────────────────────────────────
// Consultation Rejected
// ─────────────────────────────────────────────────────────────────────────────

class ConsultationRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public \App\Models\Consultation $consultation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'Prescribe & Co'),
            subject: 'Update on your consultation — Prescribe & Co',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.comms.consultation-rejected', with: [
            'consultation' => $this->consultation,
            'patient'      => $this->consultation->patient,
            'rejection'    => $this->consultation->rejection,
        ]);
    }

    public function attachments(): array { return []; }
}

// ─────────────────────────────────────────────────────────────────────────────
// Prescription Ready
// ─────────────────────────────────────────────────────────────────────────────

class PrescriptionReadyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public \App\Models\Consultation $consultation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'Prescribe & Co'),
            subject: 'Your prescription is being prepared — Prescribe & Co',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.comms.prescription-ready', with: [
            'consultation'  => $this->consultation,
            'patient'       => $this->consultation->patient,
        ]);
    }

    public function attachments(): array { return []; }
}

// ─────────────────────────────────────────────────────────────────────────────
// Renewal Reminder
// ─────────────────────────────────────────────────────────────────────────────

class RenewalReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public \App\Models\Patient $patient, public \DateTime $renewalDate) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'Prescribe & Co'),
            subject: 'Your subscription renews in 7 days — Prescribe & Co',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.comms.renewal-reminder', with: [
            'patient'     => $this->patient,
            'renewalDate' => $this->renewalDate,
        ]);
    }

    public function attachments(): array { return []; }
}

// ─────────────────────────────────────────────────────────────────────────────
// New Message notification
// ─────────────────────────────────────────────────────────────────────────────

class NewMessageMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public \App\Models\Message $message,
        public \App\Models\Patient $patient,
        public string $customSubject = 'You have a new message from Prescribe & Co'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'Prescribe & Co'),
            subject: $this->customSubject,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.comms.new-message', with: [
            'message' => $this->message,
            'patient' => $this->patient,
        ]);
    }

    public function attachments(): array { return []; }
}
