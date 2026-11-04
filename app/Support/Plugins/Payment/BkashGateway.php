<?php

namespace App\Support\Plugins\Payment;

use App\Models\Payment;

/**
 * bKash Tokenized Checkout (PGW). Requires a merchant account with
 * app_key, app_secret, username and password issued by bKash.
 */
class BkashGateway extends AbstractPaymentGateway
{
    public function slug(): string
    {
        return 'bkash';
    }

    public function name(): string
    {
        return 'bKash';
    }

    public function settingsSchema(): array
    {
        return [
            ['key' => 'app_key', 'label' => 'App Key', 'type' => 'text', 'required' => true],
            ['key' => 'app_secret', 'label' => 'App Secret', 'type' => 'password', 'required' => true],
            ['key' => 'username', 'label' => 'Username', 'type' => 'text', 'required' => true],
            ['key' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true],
            ['key' => 'base_url', 'label' => 'Base URL', 'type' => 'text', 'required' => true, 'help' => 'Sandbox: https://tokenized.sandbox.bka.sh/v1.2.0-beta ; Live: https://tokenized.pay.bka.sh/v1.2.0-beta'],
        ];
    }

    private function base(array $settings): string
    {
        return rtrim($settings['base_url'], '/');
    }

    private function token(array $settings): ?string
    {
        $response = $this->http()
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'username' => $settings['username'],
                'password' => $settings['password'],
            ])
            ->post($this->base($settings).'/tokenized/checkout/token/grant', [
                'app_key' => $settings['app_key'],
                'app_secret' => $settings['app_secret'],
            ]);

        if (! $response->successful()) {
            return null;
        }

        return $response->json('id_token');
    }

    public function createPayment(Payment $payment, array $settings): array
    {
        if ($err = $this->requireSettings($settings, ['app_key', 'app_secret', 'username', 'password', 'base_url'])) {
            return $err;
        }

        $token = $this->token($settings);

        if (! $token) {
            return ['error' => __('bKash authentication failed. Check the app credentials and base URL.')];
        }

        $response = $this->http()
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => $token,
                'X-APP-Key' => $settings['app_key'],
            ])
            ->post($this->base($settings).'/tokenized/checkout/create', [
                'mode' => '0011',
                'payerReference' => (string) $payment->student_id,
                'callbackURL' => $this->callbackUrl($this->slug()).'?payment='.$payment->id,
                'amount' => number_format((float) $payment->amount, 2, '.', ''),
                'currency' => 'BDT',
                'intent' => 'sale',
                'merchantInvoiceNumber' => $payment->transaction_id,
            ]);

        if (! $response->successful()) {
            return ['error' => __('bKash create payment failed: :msg', ['msg' => $response->json('statusMessage', $response->body())])];
        }

        return ['redirect_url' => $response->json('bkashURL')];
    }

    public function execute(string $paymentId, array $settings): array
    {
        $token = $this->token($settings);

        $response = $this->http()
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => $token,
                'X-APP-Key' => $settings['app_key'],
            ])
            ->post($this->base($settings).'/tokenized/checkout/execute', ['paymentID' => $paymentId]);

        return [$response->successful(), $response->json() ?? []];
    }

    public function initiate(Payment $payment, array $settings): array
    {
        return $this->createPayment($payment, $settings);
    }

    public function verify(array $requestData, array $settings): array
    {
        $paymentId = $requestData['paymentID'] ?? null;

        if (! $paymentId) {
            return [false, ['error' => 'Missing paymentID']];
        }

        [$ok, $body] = $this->execute($paymentId, $settings);

        return [$ok && ($body['statusCode'] ?? null) === '0000', $body];
    }
}
