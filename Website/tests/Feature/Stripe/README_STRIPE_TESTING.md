# Stripe Testing Guide — Prescribe & Co

## Overview

The Stripe test suite covers four areas:

| File | What it tests |
|---|---|
| `CheckoutFlowTest.php` | Cart page, PaymentController::initiate, confirm, auth guards |
| `WebhookTest.php` | All inbound Stripe webhook events |
| `StripeServiceTest.php` | StripeService DB operations (order creation, VAT, invoice) |
| `OrderCreationTest.php` | Order number sequencing, access control, success/cancelled pages |
| `SubscriptionTest.php` | Subscription management routes and display |
| `StripeTestHelper.php` | Shared test card numbers, fake builders, event constants |

---

## Running the tests

### Unit and feature tests (no real Stripe calls)

```bash
# Run all Stripe tests
php artisan test --filter=Stripe

# Run a specific suite
php artisan test tests/Feature/Stripe/CheckoutFlowTest.php
php artisan test tests/Feature/Stripe/WebhookTest.php
php artisan test tests/Unit/Services/StripeServiceTest.php

# Run with coverage
php artisan test --filter=Stripe --coverage
```

These tests mock all outbound Stripe API calls. No real Stripe account needed.

---

## Testing against real Stripe (test mode)

For integration testing against Stripe's actual test API:

### 1. Set test keys in `.env.testing`

```env
STRIPE_KEY=pk_test_YOUR_TEST_KEY
STRIPE_SECRET=sk_test_YOUR_TEST_SECRET
STRIPE_WEBHOOK_SECRET=whsec_YOUR_WEBHOOK_SIGNING_SECRET
```

### 2. Test card numbers

All cards below use expiry **12/99** and CVC **123**.

| Scenario | Card Number |
|---|---|
| ✅ Successful payment | `4242 4242 4242 4242` |
| ✅ UK Visa debit | `4000 0082 6000 0000` |
| ✅ Mastercard | `5555 5555 5555 4444` |
| ❌ Card declined | `4000 0000 0000 0002` |
| ❌ Insufficient funds | `4000 0000 0000 9995` |
| ❌ Expired card | `4000 0000 0000 0069` |
| ❌ Incorrect CVC | `4000 0000 0000 0127` |
| 🔐 Requires 3D Secure | `4000 0025 0000 3155` |

These constants are available in `StripeTestHelper.php`.

---

## Testing webhooks locally

Use the [Stripe CLI](https://stripe.com/docs/stripe-cli) to forward events to your local server:

```bash
# Install Stripe CLI
brew install stripe/stripe-cli/stripe

# Login
stripe login

# Forward webhooks to your local dev server
stripe listen --forward-to http://localhost:8000/webhooks/stripe

# Trigger specific events
stripe trigger payment_intent.succeeded
stripe trigger invoice.payment_failed
stripe trigger customer.subscription.deleted
```

The CLI will print your local webhook signing secret — add it to `.env` as `STRIPE_WEBHOOK_SECRET`.

---

## VAT handling

UK pharmacy VAT rules applied in `StripeService::createOrderFromPaymentIntent`:

| Product type | VAT rate | Reason |
|---|---|---|
| POM | 0% | Prescription medicines are VAT exempt (HMRC VAT Notice 701/57) |
| P (Pharmacy only) | 20% | Standard rate |
| GSL (General sale) | 20% | Standard rate |

---

## Dummy Stripe IDs for seeding

When seeding test data, use these formats to avoid Stripe API calls:

```php
'stripe_id'         => 'cus_test_' . uniqid(),   // Customer
'stripe_payment_intent_id' => 'pi_test_' . uniqid(),
// Subscription stripe IDs are created by Cashier — don't manually set in production
```
