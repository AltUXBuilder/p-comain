<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Models\User;
use App\Models\Staff;
use App\Models\Consultation;
use App\Models\Order;
use App\Mail\ConsultationSubmitted;
use App\Mail\PrescriptionApproved;
use App\Mail\ConsultationRejectedMail;
use App\Mail\OrderDispatched;
use App\Mail\RenewalReminder;
use App\Mail\TwoFactorOtp;
use App\Mail\StaffWelcomeMail;
use App\Mail\PaymentFailed;

class EmailService
{
    public function sendConsultationSubmitted(User $user, Consultation $consultation): void
    {
        SendEmailJob::dispatch($user->email, new ConsultationSubmitted($user, $consultation));
    }

    public function sendPrescriptionApproved(User $user, Consultation $consultation): void
    {
        SendEmailJob::dispatch($user->email, new PrescriptionApproved($user, $consultation));
    }

    public function sendConsultationRejected(User $user, Consultation $consultation, string $reason): void
    {
        SendEmailJob::dispatch($user->email, new ConsultationRejectedMail($user, $consultation, $reason));
    }

    public function sendOrderDispatched(User $user, Order $order): void
    {
        SendEmailJob::dispatch($user->email, new OrderDispatched($user, $order));
    }

    public function sendRenewalReminder(User $user, $subscription): void
    {
        SendEmailJob::dispatch($user->email, new RenewalReminder($user, $subscription));
    }

    public function sendTwoFactorOtp(User $user, string $otp, string $purpose): void
    {
        // This is dispatched directly (not via SendEmailJob) so it goes on the high-priority queue
        \Illuminate\Support\Facades\Mail::to($user->email)->queue(new TwoFactorOtp($user, $otp, $purpose));
    }

    public function sendStaffWelcome(Staff $staff, string $welcomeUrl): void
    {
        SendEmailJob::dispatch($staff->email, new StaffWelcomeMail($staff, $welcomeUrl));
    }

    public function sendPaymentFailed(User $user): void
    {
        SendEmailJob::dispatch($user->email, new PaymentFailed($user));
    }
}
