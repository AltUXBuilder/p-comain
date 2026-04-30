<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ConsultationService;
use App\Services\ConsultationDraftService;
use App\Services\TwoFactorService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(
        private ConsultationService $consultationService,
        private ConsultationDraftService $draftService,
        private TwoFactorService $twoFactorService,
    ) {}

    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name'       => ['required', 'string', 'max:100'],
            'last_name'        => ['required', 'string', 'max:100'],
            'email'            => ['required', 'email', 'max:255', 'unique:users'],
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'date_of_birth'    => ['required', 'date', 'before:-18 years'],
            'address_line_1'   => ['required', 'string', 'max:255'],
            'address_line_2'   => ['nullable', 'string', 'max:255'],
            'city'             => ['required', 'string', 'max:100'],
            'county'           => ['nullable', 'string', 'max:100'],
            'postcode'         => ['required', 'string', 'max:10', 'regex:/^[A-Z]{1,2}[0-9][0-9A-Z]?\s?[0-9][A-Z]{2}$/i'],
            'terms_accepted'   => ['required', 'accepted'],
            'gdpr_marketing_consent' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'first_name'             => $request->first_name,
            'last_name'              => $request->last_name,
            'email'                  => $request->email,
            'password'               => Hash::make($request->password),
            'date_of_birth'          => $request->date_of_birth,
            'address_line_1'         => $request->address_line_1,
            'address_line_2'         => $request->address_line_2,
            'city'                   => $request->city,
            'county'                 => $request->county,
            'postcode'               => strtoupper(str_replace(' ', '', $request->postcode)),
            'terms_accepted'         => true,
            'terms_accepted_at'      => now(),
            'gdpr_marketing_consent' => (bool) $request->gdpr_marketing_consent,
            'gdpr_consent_at'        => now(),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // ── Flush consultation session/draft ──────────────────────────────────
        // If the patient registered mid-questionnaire, carry their answers over

        $sessionDraft = $this->draftService->getFromSession($request);
        $cookieUuid   = $request->cookie($this->draftService->cookieName());

        if ($sessionDraft) {
            $consultation = $this->consultationService->flushSessionToConsultation($user, $sessionDraft);
            $this->draftService->clearSession($request);
            if ($consultation) {
                return redirect()->route('patient.consultation.submitted', $consultation)
                    ->with('status', 'consultation-submitted');
            }
        } elseif ($cookieUuid) {
            $draft = $this->draftService->findByUuid($cookieUuid);
            if ($draft) {
                $consultation = $this->consultationService->flushDraftToConsultation($user, $draft);
                if ($consultation) {
                    return redirect()->route('patient.consultation.submitted', $consultation)
                        ->withCookie(\Cookie::forget($this->draftService->cookieName()))
                        ->with('status', 'consultation-submitted');
                }
            }
        }

        return redirect()->route('verification.notice');
    }
}
