<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActiveSessionController extends Controller
{
    /**
     * Active session viewer.
     *
     * Queries the database sessions table to find all staff with a live session.
     * Sessions are stored as: user_id = staff.id (set by the staff guard).
     *
     * GET /staff/sessions
     */
    public function index()
    {
        // Raw session rows, filtered to sessions active in the last 8 hours
        // (matches the session lifetime in config/crm.php)
        $sessionRows = DB::table('sessions')
            ->where('last_activity', '>=', now()->subHours(8)->timestamp)
            ->whereNotNull('user_id')
            ->get();

        $staffIds = $sessionRows->pluck('user_id')->unique();
        $staffMap = Staff::whereIn('id', $staffIds)->get()->keyBy('id');

        $sessions = $sessionRows->map(function ($row) use ($staffMap) {
            $staff = $staffMap->get($row->user_id);

            // Decode payload to extract IP (stored by Laravel's session handler)
            $payload  = @unserialize(base64_decode($row->payload));
            $ip       = $row->ip_address ?? null;

            return (object) [
                'id'           => $row->id,
                'staff'        => $staff,
                'staff_id'     => $row->user_id,
                'ip_address'   => $ip,
                'user_agent'   => $row->user_agent ?? null,
                'last_activity' => \Carbon\Carbon::createFromTimestamp($row->last_activity),
                'is_current'   => $row->id === session()->getId(),
            ];
        })
        ->sortByDesc('last_activity')
        ->values();

        // Recent suspicious login alerts (last 7 days)
        $suspiciousAlerts = \App\Models\AuditLog::with('staff')
            ->where('action', 'like', 'suspicious_login.%')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'active_sessions_viewed',
            entityType: null, entityId: null,
            request:    request()
        );

        return view('staff.sessions', compact('sessions', 'suspiciousAlerts'));
    }

    /**
     * Terminate a specific session by its session ID.
     *
     * POST /staff/sessions/{sessionId}/terminate
     */
    public function terminate(string $sessionId)
    {
        // Cannot terminate own session through this route
        if ($sessionId === session()->getId()) {
            return back()->withErrors(['error' => 'Use the Sign Out button to end your own session.']);
        }

        $row = DB::table('sessions')->where('id', $sessionId)->first();
        if ($row) {
            DB::table('sessions')->where('id', $sessionId)->delete();

            AuditLog::record(
                staffId:    Auth::guard('staff')->id(),
                action:     'staff_session_terminated',
                entityType: 'staff',
                entityId:   $row->user_id,
                metadata:   ['session_id' => $sessionId, 'ip' => $row->ip_address],
                request:    request()
            );
        }

        return back()->with('success', 'Session terminated.');
    }

    /**
     * Terminate ALL sessions for a given staff member.
     * Same as forceLogout on StaffController but accessible from the sessions view.
     *
     * POST /staff/sessions/terminate-all/{staff}
     */
    public function terminateAll(Staff $staff)
    {
        DB::table('sessions')->where('user_id', $staff->id)->delete();

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'staff_force_logged_out',
            entityType: 'staff',
            entityId:   $staff->id,
            request:    request()
        );

        return back()->with('success', "{$staff->full_name} has been signed out of all sessions.");
    }
}
