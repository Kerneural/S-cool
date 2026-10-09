<?php

namespace App\Contracts;

use App\Models\Payment;

interface PaymentGateway
{
    /**
     * Generate checkout payment presentation data (QR code, account details, reference code).
     *
     * @return array{
     *     bank_name: string,
     *     account_number: string,
     *     account_holder: string,
     *     amount: int,
     *     currency: string,
     *     payment_code: string,
     *     qr_url: string
     * }
     */
    public function generateCheckoutData(Payment $payment): array;

    /**
     * Verify whether the incoming IPN webhook is authentic.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $headers
     */
    public function verifyWebhook(array $payload, array $headers): bool;

    /**
     * Parse normalized fields from an authenticated webhook payload.
     *
     * @param  array<string, mixed>  $payload
     * @return array{
     *     provider_event_id: string,
     *     external_reference: string,
     *     amount: int,
     *     currency: string,
     *     status: string,
     *     gateway_transaction_id: ?string
     * }
     */
    public function parseWebhook(array $payload): array;
}
