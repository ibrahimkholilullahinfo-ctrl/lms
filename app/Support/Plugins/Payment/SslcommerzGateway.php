<?php

namespace App\Support\Plugins\Payment;

use App\Models\Payment;

/**
 * SSLCommerz hosted checkout (v4). Requires store_id and store_passwd.
 */
class SslcommerzGateway extends AbstractPaymentGateway
{
    public function slug(): string
    {
        return 'sslcommerz';
    }

    public function name(): string
    {
        return 'SSLCommerz';
    }

    public function settingsSchema(): array
    {
        return [
            ['key' => 'store_id', 'label' => 'Store ID', 'type' => 'text', 'required' => true],
            ['key' => 'store_passwd', 'label' => 'Store Password', 'type' => 'password', 'required' => true],
            ['key' => 'sandbox', 'label' => 'Sandbox Mode (1 = yes)', 'type' => 'text', 'required' => false],
        ];
    }

    private function endpoint(array $settings): string
    {
        return (($settings['sandbox'] ?? '0') === '1')
            ? 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php'
            : 'https://securepay.sslcommerz.com/gwprocess/v4/api.php';
    }

    private function validatorEndpoint(array $settings): string
    {
        return (($settings['sandbox'] ?? '0') === '1')
            ? 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php'
            : 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php';
    }

    public function initiate(Payment $payment, array $settings): array
    {
        if ($err = $this->requireSettings($settings, ['store_id', 'store_passwd'])) {
            return $err;
        }

        $data = [
            'store_id' => $settings['store_id'],
            'store_passwd' => $settings['store_passwd'],
            'total_amount' => number_format((float) $payment->amount, 2, '.', ''),
            'currency' => $payment->currency ?: 'BDT',
            'tran_id' => $payment->transaction_id,
            'success_url' => $this->callbackUrl($this->slug()).'?payment='.$payment->id,
            'fail_url' => route('payments.failed', ['payment' => $payment->id]),
            'cancel_url' => route('payments.failed', ['payment' => $payment->id]),
            'ipn_url' => route('payments.ipn', ['plugin' => $this->slug()]),
            'cus_name' => $payment->student?->name ?? 'Student',
            'cus_email' => $payment->student?->email ?? 'student@example.com',
            'cus_add1' => 'Dhaka',
            'cus_city' => 'Dhaka',
            'cus_country' => 'Bangladesh',
            'cus_phone' => $payment->student?->phone ?? '01700000000',
            'shipping_method' => 'NO',
            'product_name' => 'Course enrollment',
            'product_category' => 'Education',
            'product_profile' => 'general',
        ];

        try {
            $response = $this->http()->asForm()->post($this->endpoint($settings), $data);
        } catch (\Throwable $e) {
            report($e);

            return ['error' => __('SSLCommerz connection error: :msg', ['msg' => $e->getMessage()])];
        }

        $body = $response->json() ?? [];

        if ($response->successful() && ($body['status'] ?? null) === 'SUCCESS' && ! empty($body['GatewayPageURL'])) {
            return ['redirect_url' => $body['GatewayPageURL']];
        }

        return ['error' => __('SSLCommerz rejected the request: :msg', ['msg' => $body['failedreason'] ?? $response->body()])];
    }

    public function verify(array $requestData, array $settings): array
    {
        $valId = $requestData['val_id'] ?? null;

        if (! $valId) {
            return [false, ['error' => 'Missing val_id']];
        }

        try {
            $response = $this->http()->get($this->validatorEndpoint($settings), [
                'val_id' => $valId,
                'store_id' => $settings['store_id'],
                'store_passwd' => $settings['store_passwd'],
                'format' => 'json',
            ]);
        } catch (\Throwable $e) {
            return [false, ['error' => $e->getMessage()]];
        }

        $body = $response->json() ?? [];
        $valid = $response->successful()
            && in_array($body['status'] ?? '', ['VALID', 'VALIDATED'], true)
            && ($body['tran_id'] ?? null) === ($requestData['tran_id'] ?? null);

        return [$valid, $body];
    }
}
