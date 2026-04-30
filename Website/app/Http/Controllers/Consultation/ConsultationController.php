<?php

namespace App\Http\Controllers\Consultation;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Product;
use App\Services\ConsultationDraftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function __construct(private ConsultationDraftService $draftService) {}

    /**
     * Start a consultation for a product.
     * Handles all scenarios: guest, returning guest with draft, logged-in patient.
     */
    public function start(Request $request, Product $product): View|RedirectResponse
    {
        // Product must be active and have a questionnaire
        if (!$product->active) {
            abort(404);
        }

        // Scenario 4 — product with no questionnaire: redirect straight to checkout
        if (!$product->has_questionnaire) {
            if (!auth()->check()) {
                $request->session()->put('url.intended', route('checkout.index'));
                return redirect()->route('login')
                    ->with('status', 'Please sign in to purchase ' . $product->name . '.');
            }
            return redirect()->route('checkout.index')->with('product_id', $product->id);
        }

        // Check for a recoverable draft via cookie
        $draftUuid = null;
        $cookieUuid = $request->cookie($this->draftService->cookieName());
        if ($cookieUuid) {
            $draft = $this->draftService->findByUuid($cookieUuid);
            if ($draft && $draft->product_id === $product->id && !$draft->isExpired()) {
                $draftUuid = $cookieUuid;
            }
        }

        return view('consultation.start', compact('product', 'draftUuid'));
    }

    /**
     * Resume a draft consultation by UUID.
     */
    public function resume(Request $request, string $uuid): View|RedirectResponse
    {
        $draft = $this->draftService->findByUuid($uuid);

        if (!$draft || $draft->isExpired()) {
            return redirect()->route('treatments.index')
                ->with('error', 'This consultation draft has expired. Please start a new consultation.');
        }

        // Restore draft to session so the Livewire component picks it up
        $this->draftService->restoreToSession($request, $draft);

        return redirect()->route('consultation.start', $draft->product);
    }

    /**
     * Save session data (called via AJAX for keepalive before session expires).
     */
    public function saveSession(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'product_id'       => 'required|integer|exists:products,id',
            'questionnaire_id' => 'nullable|integer',
            'answers'          => 'required|array',
            'current_step'     => 'required|integer|min:0',
        ]);

        // Check if session is about to expire — if so, persist to DB
        $sessionAge = time() - $request->session()->get('_token_time', time());
        $sessionLifetime = config('session.lifetime', 120) * 60;

        if ($sessionAge > ($sessionLifetime * 0.8)) {
            // Session is 80% through its lifetime — persist to DB
            $uuid = $this->draftService->persistToDB(
                $data,
                auth()->id()
            );

            return response()->json([
                'status' => 'persisted',
                'uuid'   => $uuid,
                'cookie_name' => $this->draftService->cookieName(),
            ]);
        }

        $this->draftService->saveToSession($request, $data['product_id'], $data['questionnaire_id'], $data['answers'], $data['current_step']);

        return response()->json(['status' => 'saved']);
    }

    /**
     * Patient-facing: show consultation submitted confirmation.
     */
    public function submitted(Request $request, Consultation $consultation): View|RedirectResponse
    {
        // Ensure the consultation belongs to the logged-in patient
        if ($consultation->user_id !== auth()->id()) {
            abort(403);
        }

        return view('consultation.submitted', compact('consultation'));
    }

    /**
     * Patient portal: list all consultations.
     */
    public function index(Request $request): View
    {
        $consultations = auth()->user()
            ->consultations()
            ->with(['product.treatment.category', 'prescription'])
            ->latest()
            ->paginate(10);

        return view('patient.consultations', compact('consultations'));
    }

    /**
     * Patient portal: show a single consultation.
     */
    public function show(Request $request, Consultation $consultation): View
    {
        if ($consultation->user_id !== auth()->id()) {
            abort(403);
        }

        $consultation->load(['product', 'questionnaire.questions', 'prescription', 'messages']);

        return view('patient.consultation-detail', compact('consultation'));
    }

    /**
     * Checkout-ready redirect after prescription approval.
     * Called from approval email link.
     */
    public function checkoutReady(Request $request, Product $product): RedirectResponse
    {
        // Verify patient has an approved consultation for this product
        $consultation = auth()->user()
            ->consultations()
            ->where('product_id', $product->id)
            ->where('status', 'approved')
            ->latest()
            ->first();

        if (!$consultation) {
            return redirect()->route('patient.consultations.index')
                ->with('error', 'No approved consultation found for this treatment.');
        }

        return redirect()->route('checkout.index')->with([
            'product_id'      => $product->id,
            'consultation_id' => $consultation->id,
        ]);
    }
}
