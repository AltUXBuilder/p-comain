<?php

namespace App\Jobs;

use App\Models\User;
use App\Mail\TwoFactorOtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTwoFactorEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public readonly User $user,
        public readonly string $otp,
        public readonly string $purpose,
    ) {
        // High-priority queue for 2FA — patients are waiting for this
        $this->onQueue('high');
    }

    public function handle(): void
    {
        Mail::to($this->user->email)->send(new TwoFactorOtp($this->user, $this->otp, $this->purpose));
    }
}
