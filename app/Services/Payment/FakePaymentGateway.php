<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGateway;
use App\Models\Payment;

class FakePaymentGateway implements PaymentGateway
{
    protected bool $verificationResult = true;

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $customParsedData = null;

    public function generateCheckoutData(Payment $payment): array
    {
        return [
            'bank_name' => 'FakeBank',
            'account_number' => '9999999999',
            'account_holder' => 'FAKE TEST RECIPIENT',
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'payment_code' => $payment->external_reference,
            'qr_url' => 'https://example.com/fake-qr.png',
        ];
    }

    public function verifyWebhook(array $payload, array $headers): bool
    {
        return $this->verificationResult;
    }

    public function parseWebhook(array $payload): array
    {
        if ($this->customParsedData !== null) {
            return $this->customParsedData;
        }

        $rawContent = (string) ($payload['content'] ?? $payload['order_code'] ?? $payload['external_reference'] ?? '');
        $externalReference = '';

        if (preg_match('/(INV-[A-Za-z0-9_-]+)/', $rawContent, $matches)) {
            $externalReference = $matches[1];
        } else {
            $externalReference = trim($rawContent);
        }

        return [
            'provider_event_id' => (string) ($payload['id'] ?? $payload['provider_event_id'] ?? 'fake-event-1'),
            'external_reference' => $externalReference,
            'amount' => (int) ($payload['amount'] ?? $payload['transferAmount'] ?? 0),
            'currency' => strtoupper((string) ($payload['currency'] ?? 'VND')),
            'status' => (string) ($payload['status'] ?? Payment::STATUS_SUCCEEDED),
            'gateway_transaction_id' => (string) ($payload['transaction_id'] ?? $payload['referenceCode'] ?? 'fake-tx-1'),
        ];
    }

    public function shouldPassWebhookVerification(bool $pass): self
    {
        $this->verificationResult = $pass;

        return $this;
    }

    /**
     * @param  array<string, mixed>|null  $data
     */
    public function overrideParsedData(?array $data): self
    {
        $this->customParsedData = $data;

        return $this;
    }
}
