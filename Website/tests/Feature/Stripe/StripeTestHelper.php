<?php

namespace Tests\Feature\Stripe;

/**
 * Stripe test card numbers and utilities.
 *
 * Use these dummy card details when running tests against Stripe's test mode API.
 * Never use real card details in tests.
 *
 * @see https://stripe.com/docs/testing
 */
class StripeTestHelper
{
    /**
     * Standard test cards — all succeed.
     */
    public const VISA_SUCCESS             = '4242424242424242';
    public const VISA_DEBIT_SUCCESS       = '4000056655665556';
    public const MASTERCARD_SUCCESS       = '5555555555554444';
    public const AMEX_SUCCESS             = '378282246310005';
    public const UK_DEBIT_SUCCESS         = '4000008260000000'; // UK Visa debit

    /**
     * Cards that always decline.
     */
    public const CARD_DECLINED            = '4000000000000002';
    public const CARD_INSUFFICIENT_FUNDS  = '4000000000009995';
    public const CARD_EXPIRED             = '4000000000000069';
    public const CARD_INCORRECT_CVC       = '4000000000000127';
    public const CARD_PROCESSING_ERROR    = '4000000000000119';

    /**
     * Cards that require 3D Secure authentication.
     */
    public const CARD_3DS_REQUIRED        = '4000002500003155';
    public const CARD_3DS_OPTIONAL        = '4000002760003184';

    /**
     * UK specific test card.
     */
    public const CARD_UK_VISA             = '4000008260000000';

    /**
     * Standard test expiry and CVC valid for all test cards.
     */
    public const EXPIRY_MONTH = '12';
    public const EXPIRY_YEAR  = '2099';
    public const CVC          = '123';
    public const CVC_AMEX     = '1234';

    /**
     * Build a PaymentMethod-style array for Stripe test API calls.
     */
    public static function testCard(string $cardNumber = self::VISA_SUCCESS): array
    {
        return [
            'type' => 'card',
            'card' => [
                'number'    => $cardNumber,
                'exp_month' => self::EXPIRY_MONTH,
                'exp_year'  => self::EXPIRY_YEAR,
                'cvc'       => self::CVC,
            ],
        ];
    }

    /**
     * Build a fake PaymentIntent as returned by Stripe in test mode.
     */
    public static function fakePaymentIntent(array $overrides = []): object
    {
        return (object) array_merge([
            'id'            => 'pi_test_' . uniqid(),
            'object'        => 'payment_intent',
            'amount'        => 3999,
            'currency'      => 'gbp',
            'status'        => 'succeeded',
            'client_secret' => 'pi_test_secret_' . uniqid(),
            'customer'      => 'cus_test_123',
            'metadata'      => (object)[],
        ], $overrides);
    }

    /**
     * Build a fake Stripe webhook event payload.
     * Sign it correctly using the given secret so it passes signature verification.
     */
    public static function buildSignedWebhookPayload(array $payload, string $secret): array
    {
        $json      = json_encode($payload);
        $timestamp = time();
        $sig       = hash_hmac('sha256', "{$timestamp}.{$json}", $secret);

        return [
            'payload' => $json,
            'header'  => "t={$timestamp},v1={$sig}",
        ];
    }

    /**
     * Build a fake Stripe Customer object.
     */
    public static function fakeCustomer(string $userId, string $email): object
    {
        return (object)[
            'id'       => 'cus_test_' . uniqid(),
            'object'   => 'customer',
            'email'    => $email,
            'metadata' => (object)['user_id' => $userId],
        ];
    }

    /**
     * Build a fake Stripe Subscription object.
     */
    public static function fakeSubscription(string $customerId, string $priceId, string $status = 'active'): object
    {
        return (object)[
            'id'       => 'sub_test_' . uniqid(),
            'object'   => 'subscription',
            'customer' => $customerId,
            'status'   => $status,
            'items'    => (object)[
                'data' => [
                    (object)[
                        'id'    => 'si_test_' . uniqid(),
                        'price' => (object)['id' => $priceId],
                    ]
                ]
            ],
        ];
    }

    /**
     * Webhook event types used in tests — for reference and autocomplete.
     */
    public const EVENT_PAYMENT_INTENT_SUCCEEDED      = 'payment_intent.succeeded';
    public const EVENT_PAYMENT_INTENT_FAILED         = 'payment_intent.payment_failed';
    public const EVENT_INVOICE_PAYMENT_SUCCEEDED     = 'invoice.payment_succeeded';
    public const EVENT_INVOICE_PAYMENT_FAILED        = 'invoice.payment_failed';
    public const EVENT_SUBSCRIPTION_UPDATED          = 'customer.subscription.updated';
    public const EVENT_SUBSCRIPTION_DELETED          = 'customer.subscription.deleted';
    public const EVENT_DISPUTE_CREATED               = 'charge.dispute.created';
}
