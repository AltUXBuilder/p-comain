<?php

namespace App\Mail;

use App\Models\Staff;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $welcomeUrl;

    public function __construct(
        public Staff $staff,
        string $rawToken
    ) {
        $this->welcomeUrl = url('/welcome/' . $rawToken);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address', 'noreply@prescribeandco.co.uk'),
                config('mail.from.name', 'Prescribe & Co')
            ),
            subject: 'Welcome to Prescribe & Co — Activate Your Account',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.staff.welcome',
            with: [
                'staff'      => $this->staff,
                'welcomeUrl' => $this->welcomeUrl,
                'expiresIn'  => '72 hours',
                'roleLabel'  => $this->staff->roleLabel(),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
