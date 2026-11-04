<?php

namespace App\Support\Plugins\Payment;

use App\Models\Payment;

interface PaymentGateway
{
    /** Unique slug used by the plugin manager and payments table. */
    public function slug(): string;

    public function name(): string;

    /**
     * Settings fields the institute/admin must fill in before the gateway works.
     *
     * @return array<int, array{key:string,label:string,type:string,required:bool,help?:string}>
     */
    public function settingsSchema(): array;

    /** Begin a payment: returns ['redirect_url' => string] or ['error' => string]. */
    public function initiate(Payment $payment, array $settings): array;

    /** Validate the gateway callback/return and return [success, payload]. */
    public function verify(array $requestData, array $settings): array;
}
