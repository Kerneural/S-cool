<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\CommunityInvitation;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_reference' => 'INV-'.strtoupper(Str::random(10)),
            'community_id' => Community::factory(),
            'user_id' => User::factory(),
            'community_invitation_id' => null,
            'amount' => 100000,
            'currency' => 'VND',
            'status' => Payment::STATUS_PENDING,
            'gateway_provider' => Payment::PROVIDER_SEPAY_SANDBOX,
            'gateway_transaction_id' => null,
            'paid_at' => null,
            'payload' => null,
        ];
    }

    public function succeeded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Payment::STATUS_SUCCEEDED,
            'gateway_transaction_id' => 'TXN-'.Str::random(12),
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Payment::STATUS_FAILED,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Payment::STATUS_CANCELLED,
        ]);
    }

    public function forInvitation(CommunityInvitation $invitation): static
    {
        return $this->state(fn (array $attributes) => [
            'community_id' => $invitation->community_id,
            'community_invitation_id' => $invitation->id,
        ]);
    }
}
