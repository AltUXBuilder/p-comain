<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CartController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $productId      = $request->session()->get('product_id') ?? $request->input('product_id');
        $consultationId = $request->session()->get('consultation_id') ?? $request->input('consultation_id');

        if (!$productId) {
            return redirect()->route('treatments.index')
                ->with('error', 'No product selected. Please start a consultation first.');
        }

        $product = Product::with('treatment.category')->findOrFail($productId);

        // Verify age if required
        if ($product->requires_age_verification && !auth()->user()->isAdult()) {
            return redirect()->route('treatments.index')
                ->with('error', 'You must be 18 or over to purchase this treatment.');
        }

        // For POM products: verify an approved consultation/prescription exists
        $consultation = null;
        if ($product->isPom()) {
            $consultation = $consultationId
                ? Consultation::where('id', $consultationId)
                    ->where('user_id', auth()->id())
                    ->where('status', 'approved')
                    ->first()
                : auth()->user()
                    ->consultations()
                    ->where('product_id', $product->id)
                    ->where('status', 'approved')
                    ->latest()
                    ->first();

            if (!$consultation) {
                return redirect()->route('treatments.index')
                    ->with('error', 'An approved consultation is required before purchasing this treatment.');
            }
        }

        $user = auth()->user();

        return view('checkout.index', compact('product', 'consultation', 'user'));
    }
}
