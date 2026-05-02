<?php

namespace App\Services;

use App\Models\Order;

/**
 * CarrierService — tracked shipment label generation.
 *
 * Integrates with Royal Mail Click & Drop API, DPD API, and Evri API.
 * Each method returns a tracking number and label URL/PDF.
 *
 * Set API credentials in .env:
 *   ROYAL_MAIL_API_KEY=
 *   ROYAL_MAIL_OBA_NUMBER=
 *   DPD_USERNAME=
 *   DPD_PASSWORD=
 *   DPD_ACCOUNT_NUMBER=
 *   EVRI_CLIENT_ID=
 *   EVRI_CLIENT_SECRET=
 */
class CarrierService
{
    // ── Royal Mail Click & Drop ───────────────────────────────────────────────

    /**
     * Create a Royal Mail tracked shipment via the Click & Drop API.
     * Returns ['tracking_number' => string, 'label_url' => string].
     *
     * API docs: https://developer.royalmail.net/
     */
    public function createRoyalMailShipment(Order $order): array
    {
        $apiKey = config('services.royal_mail.api_key');

        if (! $apiKey) {
            // Fall back to manual entry if no API key configured
            return ['tracking_number' => null, 'label_url' => null, 'manual' => true];
        }

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ])->post('https://api.royalmail.net/shipping/v3/shipments', [
            'serviceCode'  => $order->requires_cold_chain ? 'TP6' : 'TF',  // Special Delivery vs Tracked 48
            'recipient'    => [
                'name'        => $order->delivery_name,
                'addressLine1' => $order->delivery_address_line_1,
                'addressLine2' => $order->delivery_address_line_2,
                'city'         => $order->delivery_city,
                'postcode'     => $order->delivery_postcode,
                'countryCode'  => $order->delivery_country ?? 'GB',
            ],
            'packages' => [[
                'weightInGrams' => 500, // Default — override with actual weight
            ]],
        ]);

        if (! $response->successful()) {
            \Illuminate\Support\Facades\Log::error('Royal Mail API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'order'  => $order->order_number,
            ]);
            return ['tracking_number' => null, 'label_url' => null, 'error' => $response->body()];
        }

        $data = $response->json();
        return [
            'tracking_number' => $data['shipmentId'] ?? null,
            'label_url'       => $data['label']['url'] ?? null,
        ];
    }

    // ── DPD ───────────────────────────────────────────────────────────────────

    /**
     * Create a DPD shipment via the DPD REST API.
     * Returns ['tracking_number' => string, 'label_url' => string].
     *
     * API docs: https://developer.dpd.co.uk/
     */
    public function createDpdShipment(Order $order): array
    {
        $username = config('services.dpd.username');
        $password = config('services.dpd.password');
        $account  = config('services.dpd.account_number');

        if (! $username || ! $account) {
            return ['tracking_number' => null, 'label_url' => null, 'manual' => true];
        }

        // Step 1: Authenticate
        $authResponse = \Illuminate\Support\Facades\Http::post(
            'https://api.dpd.co.uk/user/?action=login',
            ['username' => $username, 'password' => $password]
        );

        if (! $authResponse->successful()) {
            return ['tracking_number' => null, 'label_url' => null, 'error' => 'DPD auth failed'];
        }

        $token     = $authResponse->json('data.geoSession');
        $accountId = $authResponse->json('data.account');

        // Step 2: Create shipment
        $shipResponse = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'Content-Type'  => 'application/json',
        ])->post('https://api.dpd.co.uk/shipping/shipment', [
            'jobId'      => null,
            'collectionOnDelivery' => false,
            'invoice'    => null,
            'collectionDate' => now()->format('Y-m-d'),
            'consolidate'    => false,
            'consignment' => [[
                'consignmentNumber' => null,
                'consignmentRef'    => $order->order_number,
                'parcels'           => [[
                    'packageNumber' => null,
                    'weight'        => 0.5,
                    'packageType'   => 'cardboard_box',
                ]],
                'collectionDetails' => [
                    'contactDetails' => ['contactName' => config('crm.pharmacy.name')],
                    'address'        => ['postcode' => 'YOUR_POSTCODE', 'countryCode' => 'GB'],
                ],
                'deliveryDetails' => [
                    'contactDetails' => ['contactName' => $order->delivery_name],
                    'address' => [
                        'street'      => $order->delivery_address_line_1,
                        'locality'    => $order->delivery_city,
                        'postcode'    => $order->delivery_postcode,
                        'countryCode' => 'GB',
                    ],
                    'notificationDetails' => ['email' => $order->patient?->email],
                ],
                'networkCode'  => $order->requires_cold_chain ? '2^02' : '1^12',
                'numberOfParcels' => 1,
                'totalWeight' => 0.5,
            ]],
        ]);

        if (! $shipResponse->successful()) {
            return ['tracking_number' => null, 'label_url' => null, 'error' => $shipResponse->body()];
        }

        $shipData       = $shipResponse->json('data.consignment.0');
        $trackingNumber = $shipData['parcel'][0]['parcelNumber'] ?? null;

        return [
            'tracking_number' => $trackingNumber,
            'label_url'       => "https://api.dpd.co.uk/shipping/shipment/{$trackingNumber}/label",
        ];
    }

    // ── Evri ──────────────────────────────────────────────────────────────────

    /**
     * Create an Evri (Hermes) shipment via the Evri API.
     * Returns ['tracking_number' => string, 'label_url' => string].
     *
     * API docs: https://developer.evri.com/
     */
    public function createEvriShipment(Order $order): array
    {
        $clientId     = config('services.evri.client_id');
        $clientSecret = config('services.evri.client_secret');

        if (! $clientId) {
            return ['tracking_number' => null, 'label_url' => null, 'manual' => true];
        }

        // Step 1: Get OAuth token
        $tokenResponse = \Illuminate\Support\Facades\Http::asForm()->post(
            'https://api.evri.com/oauth2/token',
            [
                'grant_type'    => 'client_credentials',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'scope'         => 'shipment',
            ]
        );

        if (! $tokenResponse->successful()) {
            return ['tracking_number' => null, 'label_url' => null, 'error' => 'Evri auth failed'];
        }

        $token = $tokenResponse->json('access_token');

        // Step 2: Create shipment
        $shipResponse = \Illuminate\Support\Facades\Http::withToken($token)
            ->post('https://api.evri.com/v1/shipments', [
                'recipient' => [
                    'title'     => '',
                    'firstName' => explode(' ', $order->delivery_name)[0] ?? $order->delivery_name,
                    'lastName'  => implode(' ', array_slice(explode(' ', $order->delivery_name), 1)) ?: 'Customer',
                    'address'   => [
                        'line1'    => $order->delivery_address_line_1,
                        'line2'    => $order->delivery_address_line_2,
                        'city'     => $order->delivery_city,
                        'postcode' => $order->delivery_postcode,
                        'country'  => 'GB',
                    ],
                    'email' => $order->patient?->email,
                ],
                'parcels' => [[
                    'weight'       => 0.5,
                    'dimensions'   => ['length' => 30, 'width' => 20, 'height' => 10],
                    'orderNumber'  => $order->order_number,
                ]],
                'serviceCode' => 'H2S', // Standard tracked
            ]);

        if (! $shipResponse->successful()) {
            return ['tracking_number' => null, 'label_url' => null, 'error' => $shipResponse->body()];
        }

        $data = $shipResponse->json();
        return [
            'tracking_number' => $data['trackingNumber'] ?? null,
            'label_url'       => $data['labelUrl']       ?? null,
        ];
    }

    // ── Unified dispatch ──────────────────────────────────────────────────────

    /**
     * Create a shipment using the appropriate carrier.
     * Returns ['tracking_number', 'label_url', 'manual' (bool), 'error' (string|null)].
     */
    public function createShipment(Order $order, string $carrier): array
    {
        return match($carrier) {
            'royal_mail' => $this->createRoyalMailShipment($order),
            'dpd'        => $this->createDpdShipment($order),
            'evri'       => $this->createEvriShipment($order),
            default      => ['tracking_number' => null, 'label_url' => null, 'manual' => true],
        };
    }
}
