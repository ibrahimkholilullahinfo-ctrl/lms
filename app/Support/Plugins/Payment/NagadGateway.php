<?php

namespace App\Support\Plugins\Payment;

use App\Models\Payment;

/**
 * Nagad Payment Gateway (checkout). Requires merchant_id, merchant_private_key,
 * pg_public_key and a sandbox/live base URL supplied by Nagad.
 */
class NagadGateway extends AbstractPaymentGateway
{
    public function slug(): string
    {
        return 'nagad';
    }

    public function name(): string
    {
        return 'Nagad';
    }

    public function settingsSchema(): array
    {
        return [
            ['key' => 'merchant_id', 'label' => 'Merchant ID', 'type' => 'text', 'required' => true],
            ['key' => 'merchant_private_key', 'label' => 'Merchant Private Key', 'type' => 'textarea', 'required' => true],
            ['key' => 'pg_public_key', 'label' => 'PG Public Key', 'type' => 'textarea', 'required' => true],
            ['key' => 'base_url', 'label' => 'Base URL', 'type' => 'text', 'required' => true, 'help' => 'Sandbox: http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs'],
        ];
    }

    public function initiate(Payment $payment, array $settings): array
    {
        if ($err = $this->requireSettings($settings, ['merchant_id', 'merchant_private_key', 'pg_public_key', 'base_url'])) {
            return $err;
        }

        try {
            $dateTime = now()->format('YmdHis');
            $orderId = $payment->transaction_id;
            $sensitive = [
                'merchantId' => $settings['merchant_id'],
                'datetime' => $dateTime,
                'orderId' => $orderId,
                'challenge' => bin2hex(random_bytes(20)),
            ];

            $publicKey = "-----BEGIN PUBLIC KEY-----\n".chunk_split(preg_replace('/\s+/', '', $settings['pg_public_key']), 64, "\n")."-----END PUBLIC KEY-----";
            openssl_public_encrypt(json_encode($sensitive), $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
            $base64 = base64_encode($encrypted);

            $response = $this->http()->withHeaders([
                'Content-Type' => 'application/json',
                'X-KM-Api-Version' => 'v-0.2.0',
                'X-KM-IP-V4' => request()->ip(),
                'X-KM-Client-Type' => 'PC_WEB',
            ])->post(rtrim($settings['base_url'], '/').'/check-out/initialize/'.$settings['merchant_id'].'/'.$orderId, [
                'accountNumber' => $payment->student?->phone ?? '01700000000',
                'dateTime' => $dateTime,
                'sensitiveData' => $base64,
                'signature' => base64_encode(hash_hmac('sha256', $base64, $settings['merchant_private_key'], true)),
            ]);

            if (! $response->successful()) {
                return ['error' => __('Nagad initialize failed: :msg', ['msg' => $response->body()])];
            }

            $body = $response->json();
            $paymentReferenceId = $body['paymentReferenceId'] ?? null;

            if (! $paymentReferenceId) {
                return ['error' => __('Nagad did not return a payment reference id.')];
            }

            $payment->update(['gateway_reference' => $paymentReferenceId]);

            $completeSensitive = [
                'merchantId' => $settings['merchant_id'],
                'orderId' => $orderId,
                'currencyCode' => '050',
                'amount' => number_format((float) $payment->amount, 2, '.', ''),
                'challenge' => $body['challenge'] ?? null,
            ];
            openssl_public_encrypt(json_encode($completeSensitive), $encryptedComplete, $publicKey, OPENSSL_PKCS1_PADDING);
            $completeBase64 = base64_encode($encryptedComplete);

            $complete = $this->http()->withHeaders([
                'Content-Type' => 'application/json',
                'X-KM-Api-Version' => 'v-0.2.0',
                'X-KM-IP-V4' => request()->ip(),
                'X-KM-Client-Type' => 'PC_WEB',
            ])->post(rtrim($settings['base_url'], '/').'/check-out/complete/'.$paymentReferenceId, [
                'sensitiveData' => $completeBase64,
                'signature' => base64_encode(hash_hmac('sha256', $completeBase64, $settings['merchant_private_key'], true)),
                'merchantCallbackURL' => $this->callbackUrl($this->slug()),
                'additionalMerchantInfo' => new \stdClass,
            ]);

            if (! $complete->successful() || empty($complete->json('callBackUrl'))) {
                return ['error' => __('Nagad complete failed.')];
            }

            return ['redirect_url' => $complete->json('callBackUrl')];
        } catch (\Throwable $e) {
            report($e);

            return ['error' => __('Nagad error: :msg', ['msg' => $e->getMessage()])];
        }
    }

    public function verify(array $requestData, array $settings): array
    {
        $status = $requestData['status'] ?? null;
        $reference = $requestData['payment_ref_id'] ?? $requestData['paymentReferenceId'] ?? null;

        if (! $reference) {
            return [false, ['error' => 'Missing payment_ref_id']];
        }

        try {
            $response = $this->http()->withHeaders([
                'Content-Type' => 'application/json',
                'X-KM-Api-Version' => 'v-0.2.0',
            ])->get(rtrim($settings['base_url'], '/').'/verify/payment/'.$reference);
        } catch (\Throwable $e) {
            return [false, ['error' => $e->getMessage()]];
        }

        $body = $response->json() ?? [];

        return [$response->successful() && ($body['status'] ?? $status) === 'Success', $body];
    }
}
