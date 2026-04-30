<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderDispatchedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address', 'noreply@prescribeandco.co.uk'),
                'Prescribe & Co'
            ),
            subject: "Your order {$this->order->order_number} has been dispatched",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.dispatched',
            with: [
                'order'       => $this->order,
                'patient'     => $this->order->patient,
                'trackingUrl' => $this->order->trackingLink(),
                'carrierName' => $this->order->carrierLabel(),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
