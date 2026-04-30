<?php

namespace App\Services;

use App\Models\TwoFactorToken;
use App\Models\User;
use App\Jobs\SendTwoFactorEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TwoFactorService
{
    /**
     * Generate and dispatch an OTP email for a patient 2FA trigger.
     */
    public function sendOtp(User $user, string $purpose, string $ip, string $fingerprint): TwoFactorToken
    {
        // Invalidate any existing unused tokens for this user + purpose
        TwoFactorToken::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->where('used', false)
            ->update(['used' => true]);

        $plainOtp = $this->generateOtp();
        $expiry   = config('pharmacy.two_factor.otp_expiry_minutes', 10);

        $token = TwoFactorToken::create([
            'user_id'            => $user->id,
            'token'              => Hash::make($plainOtp),
            'purpose'            => $purpose,
            'ip_address'         => $ip,
            'device_fingerprint' => $fingerprint,
            'expires_at'         => now()->addMinutes($expiry),
        ]);

        // Send OTP email via queue
        SendTwoFactorEmail::dispatch($user, $plainOtp, $purpose);

        return $token;
    }

    /**
     * Verify an OTP submitted by a patient.
     */
    public function verifyOtp(User $user, string $purpose, string $plainOtp): bool
    {
        $token = TwoFactorToken::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$token) {
            return false;
        }

        return $token->verify($plainOtp);
    }

    /**
     * Determine if 2FA should be triggered for a given action.
     * Checks if purpose is in configured triggers and device is not trusted.
     */
    public function shouldTrigger(User $user, string $purpose, string $fingerprint): bool
    {
        $triggers = config('pharmacy.two_factor_triggers', []);

        if (!in_array($purpose, $triggers)) {
            return false;
        }

        // Don't trigger if device is already trusted for login purposes
        if ($purpose === 'new_device_login' && $user->isTrustedDevice($fingerprint)) {
            return false;
        }

        // For sensitive actions (prescriptions, payment, address) always trigger
        return true;
    }

    /**
     * Generate a numeric OTP of configured length.
     */
    private function generateOtp(): string
    {
        $length = config('pharmacy.two_factor.otp_length', 6);
        return str_pad((string) random_int(0, (int) str_repeat('9', $length)), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a device fingerprint from request data.
     */
    public static function generateFingerprint(string $userAgent, string $ip, string $acceptLanguage = ''): string
    {
        return hash('sha256', $userAgent . $ip . $acceptLanguage);
    }
}
