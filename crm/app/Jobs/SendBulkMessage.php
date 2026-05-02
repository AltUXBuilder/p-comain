<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBulkMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array  $recipientIds,
        public string $subject,
        public string $body,
        public int    $staffId
    ) {}

    public function handle(): void
    {
        foreach ($this->recipientIds as $patientId) {
            try {
                $patient = Patient::find($patientId);
                if (! $patient || ! $patient->email) continue;

                $message = Message::create([
                    'consultation_id' => null,
                    'user_id'         => $patient->id,
                    'sender_type'     => 'staff',
                    'sender_id'       => $this->staffId,
                    'body'            => $this->body,
                    'internal_only'   => false,
                ]);

                Mail::to($patient->email)->queue(new \App\Mail\NewMessageMail($message, $patient, $this->subject));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Bulk message failed for patient {$patientId}: " . $e->getMessage());
            }
        }
    }
}
