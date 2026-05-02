<?php

namespace App\Services;

use App\Models\SessionLog;
use App\Models\Staff;
use App\Models\StaffNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SuspiciousLoginDetector
{
    /**
     * Business hours: Mon–Fri 07:00–22:00 Europe/London.
     * Access outside these hours is flagged as "outside hours".
     */
    const BUSINESS_START = 7;   // 07:00
    const BUSINESS_END   = 22;  // 22:00

    /**
     * How many historical successful logins to check for known IPs/devices.
     */
    const HISTORY_DEPTH = 50;

    /**
     * Analyse a successful login and raise alerts if suspicious.
     * Called from FortifyServiceProvider::authenticateUsing() after success.
     */
    public function analyse(Staff $staff, Request $request): void
    {
        $alerts = [];

        if ($this->isNewDevice($staff, $request)) {
            $alerts[] = $this->alert('new_device', $staff, $request,
                "New device login detected for {$staff->full_name}.",
                ['device_fingerprint' => SessionLog::fingerprint($request)]
            );
        }

        if ($this->isUnusualIp($staff, $request)) {
            $alerts[] = $this->alert('unusual_ip', $staff, $request,
                "Login from an unusual IP address for {$staff->full_name}: {$request->ip()}.",
                ['ip' => $request->ip()]
            );
        }

        if ($this->isOutsideHours($request)) {
            $alerts[] = $this->alert('outside_hours', $staff, $request,
                "Outside-hours login for {$staff->full_name} at " . now()->setTimezone('Europe/London')->format('H:i') . " (London time).",
                ['time' => now()->setTimezone('Europe/London')->toTimeString()]
            );
        }

        if (empty($alerts)) {
            return;
        }

        // Notify Super Admins and the staff member themselves
        $notifyIds = Staff::where('active', true)
            ->where('role', 'super_admin')
            ->pluck('id')
            ->push($staff->id)
            ->unique();

        foreach ($alerts as $alert) {
            StaffNotification::insert(
                $notifyIds->map(fn ($id) => [
                    'staff_id'    => $id,
                    'type'        => 'suspicious_login',
                    'message'     => $alert['message'],
                    'entity_type' => 'session_log',
                    'entity_id'   => $alert['session_log_id'] ?? null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ])->toArray()
            );
        }

        Log::warning('CRM suspicious login', [
            'staff_id' => $staff->id,
            'email'    => $staff->email,
            'ip'       => $request->ip(),
            'alerts'   => array_column($alerts, 'type'),
        ]);
    }

    // ── Detection rules ───────────────────────────────────────────────────────

    /**
     * A "new device" is one whose fingerprint has never successfully logged in before.
     */
    private function isNewDevice(Staff $staff, Request $request): bool
    {
        $fingerprint = SessionLog::fingerprint($request);

        $knownFingerprints = SessionLog::where('staff_id', $staff->id)
            ->where('success', true)
            ->latest('created_at')
            ->limit(self::HISTORY_DEPTH)
            ->pluck('device_fingerprint');

        return ! $knownFingerprints->contains($fingerprint);
    }

    /**
     * An "unusual IP" is one that has never successfully logged in for this staff member.
     */
    private function isUnusualIp(Staff $staff, Request $request): bool
    {
        // Skip localhost / RFC-1918 ranges — always considered "known"
        $ip = $request->ip();
        if (in_array($ip, ['127.0.0.1', '::1'])) return false;
        if ($this->isPrivateIp($ip)) return false;

        $knownIps = SessionLog::where('staff_id', $staff->id)
            ->where('success', true)
            ->latest('created_at')
            ->limit(self::HISTORY_DEPTH)
            ->pluck('ip_address');

        return ! $knownIps->contains($ip);
    }

    /**
     * Outside Mon–Fri 07:00–22:00 Europe/London.
     */
    private function isOutsideHours(Request $request): bool
    {
        $now = now()->setTimezone('Europe/London');
        $hour = (int) $now->format('H');
        $dow  = (int) $now->format('N'); // 1 = Mon, 7 = Sun

        // Weekend
        if ($dow >= 6) return true;

        // Outside business hours
        return $hour < self::BUSINESS_START || $hour >= self::BUSINESS_END;
    }

    private function isPrivateIp(string $ip): bool
    {
        return (bool) filter_var($ip, FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }

    private function alert(string $type, Staff $staff, Request $request, string $message, array $meta = []): array
    {
        // Create a dedicated session log entry for this suspicious event
        $log = \App\Models\SessionLog::create([
            'staff_id'           => $staff->id,
            'success'            => true,
            'failure_reason'     => null,
            'ip_address'         => $request->ip(),
            'user_agent'         => $request->userAgent(),
            'device_fingerprint' => SessionLog::fingerprint($request),
            'created_at'         => now(),
        ]);

        \App\Models\AuditLog::record(
            staffId:    $staff->id,
            action:     "suspicious_login.{$type}",
            entityType: 'session_log',
            entityId:   $log->id ?? null,
            metadata:   $meta,
            request:    $request
        );

        return [
            'type'           => $type,
            'message'        => $message,
            'session_log_id' => $log->id ?? null,
        ];
    }
}
