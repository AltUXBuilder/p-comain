<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Mail\StaffWelcomeMail;
use App\Models\AuditLog;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $staff = Staff::withTrashed()
            ->when($request->input('search'), fn ($q, $s) =>
                $q->where(fn ($q2) =>
                    $q2->where('first_name', 'like', "%{$s}%")
                       ->orWhere('last_name', 'like', "%{$s}%")
                       ->orWhere('email', 'like', "%{$s}%")
                )
            )
            ->when($request->input('role'), fn ($q, $r) => $q->where('role', $r))
            ->orderBy('last_name')
            ->paginate(25)
            ->withQueryString();

        return view('staff.index', compact('staff'));
    }

    public function create()
    {
        return view('staff.create', ['roles' => Staff::ROLES]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'  => ['required', 'string', 'max:100'],
            'last_name'   => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', 'max:255', 'unique:staff,email'],
            'role'        => ['required', Rule::in(array_keys(Staff::ROLES))],
            'gphc_number' => [
                Rule::requiredIf(fn () => in_array($request->role, Staff::ROLES_REQUIRING_GPHC)),
                'nullable',
                'regex:/^\d{7}$/',  // GPhC numbers are exactly 7 digits
            ],
            'max_daily_consultations' => ['nullable', 'integer', 'min:1', 'max:500'],
        ], [
            'gphc_number.required' => 'A GPhC number is required for this role.',
            'gphc_number.regex'    => 'GPhC numbers must be exactly 7 digits.',
        ]);

        $staff = Staff::create([
            ...$validated,
            'active'   => false, // activated after welcome flow + 2FA
            'password' => null,
        ]);

        // Generate welcome token and dispatch email
        $rawToken = $staff->generateWelcomeToken();
        Mail::to($staff->email)->queue(new StaffWelcomeMail($staff, $rawToken));

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'staff_created',
            entityType: 'staff',
            entityId:   $staff->id,
            metadata:   ['role' => $staff->role, 'email' => $staff->email],
            request:    $request
        );

        return redirect()->route('staff.index')
            ->with('success', "Account created for {$staff->full_name}. Welcome email dispatched.");
    }

    public function show(Staff $staff)
    {
        $staff->load(['sessionLogs' => fn ($q) => $q->latest()->limit(20)]);
        return view('staff.show', compact('staff'));
    }

    public function edit(Staff $staff)
    {
        return view('staff.edit', ['staff' => $staff, 'roles' => Staff::ROLES]);
    }

    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'first_name'  => ['required', 'string', 'max:100'],
            'last_name'   => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', 'max:255', Rule::unique('staff')->ignore($staff->id)],
            'role'        => ['required', Rule::in(array_keys(Staff::ROLES))],
            'gphc_number' => [
                Rule::requiredIf(fn () => in_array($request->role, Staff::ROLES_REQUIRING_GPHC)),
                'nullable',
                'regex:/^\d{7}$/',
            ],
            'max_daily_consultations' => ['nullable', 'integer', 'min:1', 'max:500'],
            'out_of_office'           => ['boolean'],
        ]);

        $staff->update($validated);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'staff_updated',
            entityType: 'staff',
            entityId:   $staff->id,
            metadata:   ['fields' => array_keys($validated)],
            request:    $request
        );

        return redirect()->route('staff.show', $staff)
            ->with('success', 'Staff account updated.');
    }

    public function deactivate(Staff $staff)
    {
        // Prevent deactivating own account
        if ($staff->id === Auth::guard('staff')->id()) {
            return back()->withErrors(['error' => 'You cannot deactivate your own account.']);
        }

        $staff->update(['active' => false]);
        $staff->delete(); // soft delete

        // Terminate active session
        $this->terminateSession($staff->id);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'staff_deactivated',
            entityType: 'staff',
            entityId:   $staff->id,
            request:    request()
        );

        return back()->with('success', "{$staff->full_name}'s account has been deactivated.");
    }

    public function reactivate(Staff $staff)
    {
        $staff->restore();
        $staff->update(['active' => true]);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'staff_reactivated',
            entityType: 'staff',
            entityId:   $staff->id,
            request:    request()
        );

        return back()->with('success', "{$staff->full_name}'s account reactivated.");
    }

    public function resendWelcome(Staff $staff)
    {
        if ($staff->welcome_completed_at) {
            return back()->withErrors(['error' => 'This staff member has already completed their welcome setup.']);
        }

        $rawToken = $staff->generateWelcomeToken();
        Mail::to($staff->email)->queue(new StaffWelcomeMail($staff, $rawToken));

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'staff_welcome_resent',
            entityType: 'staff',
            entityId:   $staff->id,
            request:    request()
        );

        return back()->with('success', 'Welcome email resent.');
    }

    public function forceLogout(Staff $staff)
    {
        $this->terminateSession($staff->id);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'staff_force_logged_out',
            entityType: 'staff',
            entityId:   $staff->id,
            request:    request()
        );

        return back()->with('success', "{$staff->full_name} has been signed out.");
    }

    // ─── Private ─────────────────────────────────────────────────────────────

    private function terminateSession(int $staffId): void
    {
        // Invalidate all database sessions for this staff member
        \Illuminate\Support\Facades\DB::table('sessions')
            ->where('user_id', $staffId)
            ->delete();
    }
}
