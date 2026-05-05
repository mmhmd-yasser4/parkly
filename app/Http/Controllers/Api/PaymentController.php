<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PayMobService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PayMobService $paymob;

    public function __construct(PayMobService $paymob)
    {
        $this->paymob = $paymob;
    }

    // Flutter calls this to get the iframe URL to show the card entry screen
    public function initiateCardSave(Request $request)
    {
        $user = $request->user();

        // Amount 1 EGP (100 cents) just to verify the card — will not actually charge
        $amountCents = 100;

        $billingData = [
            'first_name'      => $user->full_name,
            'last_name'       => 'N/A',
            'email'           => $user->email,
            'phone_number'    => $user->phone_number ?? '01000000000',
            'apartment'       => 'N/A',
            'floor'           => 'N/A',
            'street'          => 'N/A',
            'building'        => 'N/A',
            'shipping_method' => 'N/A',
            'postal_code'     => 'N/A',
            'city'            => 'Cairo',
            'country'         => 'EG',
            'state'           => 'Cairo',
        ];

        $authToken   = $this->paymob->authenticate();
        $orderId     = $this->paymob->registerOrder($authToken, $amountCents);
        $paymentKey  = $this->paymob->getPaymentKey($authToken, $orderId, $amountCents, $billingData);
        $iframeId    = config('services.paymob.iframe_id');

        return response()->json([
            'payment_key' => $paymentKey,
            'iframe_url'  => "https://accept.paymob.com/api/acceptance/iframes/{$iframeId}?payment_token={$paymentKey}",
        ]);
    }
    // PayMob calls this after every payment — no Sanctum auth here
public function webhook(Request $request)
{
    $data         = $request->all();
    $receivedHmac = $request->query('hmac');

    // Verify it really came from PayMob
    if (!$this->paymob->verifyHmac($data['obj'] ?? $data, $receivedHmac)) {
        return response()->json(['message' => 'Invalid signature'], 403);
    }

    $obj     = $data['obj'];
    $success = $obj['success'] === true || $obj['success'] === 'true';

    if (!$success) {
        return response()->json(['message' => 'Payment not successful']);
    }

    // If PayMob sends back a card token — save it as a payment method
    $cardToken = $obj['source_data']['token'] ?? null;

    if ($cardToken) {
        $userId = $obj['order']['shipping_data']['email']
            ? \App\Models\User::where('email', $obj['order']['shipping_data']['email'])->value('id')
            : null;

        if ($userId) {
            $isFirst = \App\Models\PaymentMethod::where('user_id', $userId)->count() === 0;

            \App\Models\PaymentMethod::create([
                'user_id'    => $userId,
                'token'      => $cardToken,
                'card_brand' => strtolower($obj['source_data']['type']) === 'visa' ? 'Visa' : 'Mastercard',
                'last_four'  => $obj['source_data']['pan'],
                'expiry'     => 'N/A',
                'is_default' => $isFirst,
            ]);
        }
    }

    return response()->json(['message' => 'Webhook received']);
}
}