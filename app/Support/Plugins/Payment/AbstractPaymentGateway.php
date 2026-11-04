<?php

namespace App\Support\Plugins\Payment;

abstract class AbstractPaymentGateway implements PaymentGateway
{
    protected function http()
    {
        return \Illuminate\Support\Facades\Http::withOptions(['timeout' => 30, 'connect_timeout' => 15]);
    }

    protected function requireSettings(array $settings, array $keys): array
    {
        $missing = array_values(array_filter($keys, fn ($k) => blank($settings[$k] ?? null)));

        if ($missing) {
            return ['error' => __('Missing gateway credentials: :keys. Configure them in Plugin Manager.', ['keys' => implode(', ', $missing)])];
        }

        return [];
    }

    protected function callbackUrl(string $slug): string
    {
        return route('payments.callback', ['plugin' => $slug]);
    }
}
