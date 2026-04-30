<?php namespace App\Mail;
use App\Models\User; use App\Models\Order;
use Illuminate\Bus\Queueable; use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content; use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderDispatched extends Mailable {
    use Queueable, SerializesModels;
    public function __construct(public readonly User $user, public readonly Order $order) {}
    public function envelope(): Envelope { return new Envelope(subject: 'Your order is on its way — Prescribe & Co'); }
    public function content(): Content   { return new Content(view: 'emails.order-dispatched'); }
}
