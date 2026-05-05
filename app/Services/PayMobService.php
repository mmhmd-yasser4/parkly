<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PayMobService
{
    protected string $baseUrl = 'https://accept.paymob.com/api';
    protected string $apiKey;
    protected int $integrationId;

    public function __construct()
    {
        $this->apiKey        = config('services.paymob.api_key');
        $this->integrationId = config('services.paymob.integration_id');
    }

    // Step 1 — get auth token from PayMob
    public function authenticate(): string
    {
        $response = Http::post("{$this->baseUrl}/auth/tokens", [
            'api_key' => $this->apiKey,
        ]);
    
        return $response->json('token');
    }

    // Step 2 — register an order with PayMob
    public function registerOrder(string $authToken, int $amountCents, string $currency = 'EGP'): int
    {
        $response = Http::post("{$this->baseUrl}/ecommerce/orders", [
            'auth_token'     => $authToken,
            'delivery_needed' => false,
            'amount_cents'   => $amountCents,
            'currency'       => $currency,
            'items'          => [],
        ]);

        return $response->json('id');
    }

    // Step 3 — get a payment key for Flutter to use
    public function getPaymentKey(
        string $authToken,
        int $orderId,
        int $amountCents,
        array $billingData
    ): string {
        $response = Http::post("{$this->baseUrl}/acceptance/payment_keys", [
            'auth_token'     => $authToken,
            'amount_cents'   => $amountCents,
            'expiration'     => 3600,
            'order_id'       => $orderId,
            'billing_data'   => $billingData,
            'currency'       => 'EGP',
            'integration_id' => $this->integrationId,
            'lock_order_when_paid' => false,
        ]);

        return $response->json('token');
    }

    // Charge a saved card using its token (for overstay auto-charge)
    public function chargeWithToken(
        string $authToken,
        int $orderId,
        int $amountCents,
        string $cardToken,
        array $billingData
    ): array {
        $paymentKey = $this->getPaymentKey($authToken, $orderId, $amountCents, $billingData);

        $response = Http::post("{$this->baseUrl}/acceptance/payments/pay", [
            'source' => [
                'identifier' => $cardToken,
                'subtype'    => 'TOKEN',
            ],
            'payment_token' => $paymentKey,
        ]);

        return $response->json();
    }

    // Verify webhook signature so we know it really came from PayMob
    public function verifyHmac(array $data, string $receivedHmac): bool
    {
        $hmacSecret = config('services.paymob.hmac_secret');

        $fields = [
            'amount_cents', 'created_at', 'currency', 'error_occured',
            'has_parent_transaction', 'id', 'integration_id', 'is_3d_secure',
            'is_auth', 'is_capture', 'is_refunded', 'is_standalone_payment',
            'is_voided', 'order', 'owner', 'pending', 'source_data_pan',
            'source_data_sub_type', 'source_data_type', 'success',
        ];

        $concatenated = '';
        foreach ($fields as $field) {
            $concatenated .= $data[$field] ?? '';
        }

        $hash = hash_hmac('sha512', $concatenated, $hmacSecret);

        return $hash === $receivedHmac;
    }
}