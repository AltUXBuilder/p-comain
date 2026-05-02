<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// ─────────────────────────────────────────────────────────────────────────────
// Order Dispensed
// ─────────────────────────────────────────────────────────────────────────────

class OrderDispensedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order   $order,
        public Patient $patient
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'Prescribe & Co'),
            subject: "Your order {$this->order->order_number} has been dispensed — Prescribe & Co",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.comms.order-dispensed',
            with: [
                'order'   => $this->order->load('items'),
                'patient' => $this->patient,
            ]
        );
    }

    public function attachments(): array { return []; }
}

// ─────────────────────────────────────────────────────────────────────────────
// Consultation Submitted
// ─────────────────────────────────────────────────────────────────────────────

class ConsultationSubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public \App\Models\Consultation $consultation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'Prescribe & Co'),
            subject: 'We\'ve received your consultation — Prescribe & Co',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.comms.consultation-submitted',
            with: [
                'consultation' => $this->consultation,
                'patient'      => $this->consultation->patient,
            ]
        );
    }

    public function attachments(): array { return []; }
}
