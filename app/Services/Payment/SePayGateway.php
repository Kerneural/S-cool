<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGateway;
use App\Models\Payment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SePayGateway implements PaymentGateway
{
    /**
     * Generate checkout payment presentation data.
     */
    public function generateCheckoutData(Payment $payment): array
    {
        abort_unless(config('services.sepay.test_mode') === true, 503, 'SePay Test Mode must be explicitly configured.');
        abort_unless(is_string(config('services.sepay.account_number')) && trim(config('services.sepay.account_number')) !== '', 503, 'Configure the SePay Test Mode account.');
        $bankName = config('services.sepay.bank_name', 'MBBank');
        $accountNumber = config('services.sepay.account_number', '0123456789');
        $accountHolder = config('services.sepay.account_holder', 'S-COOL EDUCATION');
        $paymentCode = $payment->external_reference;
        $amount = $payment->amount;

        // Test Mode transactions are simulated in SePay, never paid through a banking app.
        $qrUrl = '';

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
        if (config('services.sepay.test_mode') !== true) {
            return false;
        }
        $expectedToken = config('services.sepay.webhook_token');
        if (! is_string($expectedToken) || trim($expectedToken) === '') {
            return false;
        }

        $authHeader = $headers['authorization'][0] ?? $headers['authorization'] ?? '';
        if (is_array($authHeader)) {
            $authHeader = $authHeader[0] ?? '';
        }

        if (is_string($authHeader) && preg_match('/^Apikey\s+(.+)$/i', trim($authHeader), $matches)) {
            if (hash_equals(trim($expectedToken), trim($matches[1]))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse normalized fields from an authenticated webhook payload.
     */
    public function parseWebhook(array $payload): array
    {
        Validator::make($payload, [
            'id' => ['required', 'integer', 'min:1'],
            'content' => ['required', 'string', 'max:2000'],
            'transferType' => ['required', 'in:in'],
            'transferAmount' => ['required', 'integer', 'min:1', 'max:4294967295'],
            'currency' => ['sometimes', 'required', 'in:VND'],
            'referenceCode' => ['required', 'string', 'max:128'],
            'accountNumber' => ['required', 'string', 'max:128'],
        ])->validate();
        if ($payload['accountNumber'] !== config('services.sepay.account_number')) {
            throw ValidationException::withMessages(['accountNumber' => 'Unexpected Test Mode destination account.']);
        }
        $providerEventId = (string) $payload['id'];

        // Extract reference code from content, description, order_code or reference
        $rawContent = $payload['content'];
        $externalReference = '';

        if (preg_match_all('/(?<![A-Z0-9_-])((?:INV|CRS|RNW)-[A-Z0-9]{10})(?![A-Z0-9_-])/', $rawContent, $matches) === 1) {
            $externalReference = $matches[1][0];
        } else {
            throw ValidationException::withMessages(['content' => 'A single valid payment reference is required.']);
        }

        $amount = (int) $payload['transferAmount'];
        $currency = strtoupper((string) ($payload['currency'] ?? 'VND'));
        $gatewayTransactionId = $payload['referenceCode'];

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
