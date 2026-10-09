<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGateway;
use App\Models\Payment;

class SePayGateway implements PaymentGateway
{
    /**
     * Generate checkout payment presentation data.
     */
    public function generateCheckoutData(Payment $payment): array
    {
        $bankName = config('services.sepay.bank_name', 'MBBank');
        $accountNumber = config('services.sepay.account_number', '0123456789');
        $accountHolder = config('services.sepay.account_holder', 'S-COOL EDUCATION');
        $paymentCode = $payment->external_reference;
        $amount = $payment->amount;

        $qrUrl = sprintf(
            'https://qr.sepay.vn/img?acc=%s&bank=%s&amount=%d&des=%s',
            urlencode($accountNumber),
            urlencode($bankName),
            $amount,
            urlencode($paymentCode)
        );

        return [
            'bank_name' => $bankName,
            'account_number' => $accountNumber,
            'account_holder' => $accountHolder,
            'amount' => $amount,
            'currency' => $payment->currency,
            'payment_code' => $paymentCode,
            'qr_url' => $qrUrl,
        ];
    }

    /**
     * Verify whether the incoming IPN webhook is authentic.
     */
    public function verifyWebhook(array $payload, array $headers): bool
    {
        $expectedToken = config('services.sepay.webhook_token');
        if (! is_string($expectedToken) || trim($expectedToken) === '') {
            return false;
        }

        $authHeader = $headers['authorization'][0] ?? $headers['authorization'] ?? '';
        if (is_array($authHeader)) {
            $authHeader = $authHeader[0] ?? '';
        }

        if (is_string($authHeader) && preg_match('/^(?:Apikey|Bearer)\s+(.+)$/i', trim($authHeader), $matches)) {
            if (hash_equals(trim($expectedToken), trim($matches[1]))) {
                return true;
            }
        }

        $customHeader = $headers['x-sepay-token'][0] ?? $headers['x-sepay-token'] ?? '';
        if (is_array($customHeader)) {
            $customHeader = $customHeader[0] ?? '';
        }

        if (is_string($customHeader) && trim($customHeader) !== '') {
            return hash_equals(trim($expectedToken), trim($customHeader));
        }

        return false;
    }

    /**
     * Parse normalized fields from an authenticated webhook payload.
     */
    public function parseWebhook(array $payload): array
    {
        $providerEventId = (string) ($payload['id'] ?? $payload['transaction_id'] ?? $payload['event_id'] ?? '');

        // Extract reference code from content, description, order_code or reference
        $rawContent = (string) ($payload['content'] ?? $payload['description'] ?? $payload['order_code'] ?? $payload['reference'] ?? '');
        $externalReference = '';

        if (preg_match('/(INV-[A-Za-z0-9_-]+)/', $rawContent, $matches)) {
            $externalReference = $matches[1];
        } else {
            $externalReference = trim($rawContent);
        }

        $amount = (int) ($payload['transferAmount'] ?? $payload['amount'] ?? 0);
        $currency = strtoupper((string) ($payload['currency'] ?? 'VND'));
        $gatewayTransactionId = (string) ($payload['referenceCode'] ?? $payload['gateway_transaction_id'] ?? $payload['id'] ?? '');

        return [
            'provider_event_id' => $providerEventId,
            'external_reference' => $externalReference,
            'amount' => $amount,
            'currency' => $currency,
            'status' => Payment::STATUS_SUCCEEDED,
            'gateway_transaction_id' => $gatewayTransactionId,
        ];
    }
}
