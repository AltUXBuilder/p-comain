<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConsultationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User         $user,
        public readonly Consultation $consultation,
        public readonly string       $reason,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Update on your consultation — Prescribe & Co');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.consultation-rejected');
    }
}
