<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Order;
use App\Models\Prescription;
use App\Models\Product;
use App\Services\StripeService;
use App\Services\EmailService;
use App\Services\AuditService;
use App\Jobs\ProcessWorkflowRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private StripeService $stripeService,
        private EmailService  $emailService,
        private AuditService  $auditService,
    ) {}

    /**
     * Create a PaymentIntent and return the client_secret to the frontend.
     * Called via AJAX from the checkout page.
     */
    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id'      => 'required|integer|exists:products,id',
            'consultation_id' => 'nullable|integer|exists:consultations,id',
            'quantity'        => 'required|integer|min:1|max:12',
            'plan_type'       => 'required|in:one_off,subscription',
            'stripe_price_id' => 'nullable|string', // for subscription plans
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $user    = auth()->user();

        if ($validated['plan_type'] === 'one_off') {
            if (!$product->price_one_off) {
                return response()->json(['error' => 'One-off purchase is not available for this product.'], 422);
            }

            $intent = $this->stripeService->createOneOffPaymentIntent(
                $user,
                $product,
                $validated['quantity'],
                ['consultation_id' => $validated['consultation_id'] ?? '']
            );

            return response()->json([
                'client_secret'      => $intent->client_secret,
                'payment_intent_id'  => $intent->id,
                'amount'             => $intent->amount,
                'currency'           => $intent->currency,
            ]);
        }

        // Subscription: create a SetupIntent so the frontend can collect card details
        $setupIntent = $user->createSetupIntent([
            'metadata' => [
                'user_id'         => $user->id,
                'product_id'      => $product->id,
                'stripe_price_id' => $validated['stripe_price_id'],
                'plan_label'      => $validated['plan_type'],
            ],
        ]);

        return response()->json([
            'client_secret' => $setupIntent->client_secret,
            'setup_intent'  => true,
            'stripe_price_id' => $validated['stripe_price_id'],
        ]);
    }

    /**
     * Confirm a completed one-off payment and create the order.
     * Called after Stripe.js confirms payment on the frontend.
     */
    public function confirm(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
            'product_id'        => 'required|integer|exists:products,id',
            'consultation_id'   => 'nullable|integer|exists:consultations,id',
            'quantity'          => 'required|integer|min:1',
            'delivery_address'  => 'required|array',
            'delivery_address.name'           => 'required|string|max:255',
            'delivery_address.address_line_1' => 'required|string|max:255',
            'delivery_address.address_line_2' => 'nullable|string|max:255',
            'delivery_address.city'           => 'required|string|max:100',
            'delivery_address.postcode'       => 'required|string|max:10',
        ]);

        $user    = auth()->user();
        $product = Product::findOrFail($validated['product_id']);

        // Verify the PaymentIntent belongs to this customer and is successful
        $intent = $this->stripeService->retrievePaymentIntent($validated['payment_intent_id']);

        if ($intent->status !== 'succeeded') {
            return response()->json(['error' => 'Payment has not been completed.'], 422);
        }

        if ($intent->customer !== $user->stripe_id) {
            return response()->json(['error' => 'Payment mismatch.'], 403);
        }

        // Get linked prescription if POM
        $prescription = null;
        if ($validated['consultation_id']) {
            $consultation = Consultation::where('id', $validated['consultation_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();
            $prescription = $consultation->prescription;
        }

        // Create the order
        $order = $this->stripeService->createOrderFromPaymentIntent(
            $user,
            $product,
            $validated['payment_intent_id'],
            $validated['quantity'],
            $prescription,
            $validated['delivery_address']
        );

        // Update prescription status to sent_to_dispense
        if ($prescription) {
            $prescription->update(['status' => 'sent_to_dispense']);
        }

        // Trigger workflows
        ProcessWorkflowRule::dispatch('order.created', ['order_id' => $order->id]);

        // Send confirmation email
        $this->emailService->sendOrderDispatched($user, $order);

        // Audit
        $this->auditService->log(null, 'order.created', 'Order', $order->id, [
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'total'      => $order->total,
        ]);

        return response()->json([
            'success'   => true,
            'order_id'  => $order->id,
            'redirect'  => route('checkout.success', ['order' => $order->id]),
        ]);
    }

    /**
     * Confirm a subscription signup after SetupIntent completes.
     */
    public function confirmSubscription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'setup_intent_id'  => 'required|string',
            'product_id'       => 'required|integer|exists:products,id',
            'stripe_price_id'  => 'required|string',
            'plan_label'       => 'required|string',
            'payment_method_id'=> 'required|string',
        ]);

        $user    = auth()->user();
        $product = Product::findOrFail($validated['product_id']);

        $subscription = $this->stripeService->createSubscription(
            $user,
            $product,
            $validated['stripe_price_id'],
            $validated['plan_label'],
            $validated['payment_method_id']
        );

        ProcessWorkflowRule::dispatch('subscription.created', ['subscription_id' => $subscription->id]);

        return response()->json([
            'success'  => true,
            'redirect' => route('checkout.success'),
        ]);
    }

    /**
     * Order success page.
     */
    public function success(Request $request): View
    {
        $order = null;
        if ($request->order) {
            $order = Order::where('id', $request->order)
                ->where('user_id', auth()->id())
                ->with(['items.product', 'prescription'])
                ->first();
        }
        return view('checkout.confirmation', compact('order'));
    }

    /**
     * Payment cancelled / back from Stripe.
     */
    public function cancelled(Request $request): View
    {
        return view('checkout.cancelled');
    }
}
