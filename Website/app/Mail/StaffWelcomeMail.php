<?php

namespace App\Mail;

use App\Models\Staff;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Staff  $staff,
        public readonly string $welcomeUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Welcome to Prescribe & Co — set up your account');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.staff-welcome');
    }
}
