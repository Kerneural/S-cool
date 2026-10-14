<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\Payment;
use App\Models\ReferralCommission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReferralCommission>
 */
class ReferralCommissionFactory extends Factory
{
    protected $model = ReferralCommission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = 200000;
        $rate = 15;
        $commission = (int) floor(($amount * $rate) / 100);

        return [
            'community_id' => Community::factory(),
            'payment_id' => Payment::factory(),
            'referrer_id' => User::factory(),
            'payer_id' => User::factory(),
            'community_invitation_id' => null,
            'purpose' => Payment::PURPOSE_COMMUNITY_ACCESS,
            'commission_rate' => $rate,
            'payment_amount' => $amount,
            'commission_amount' => $commission,
            'currency' => 'VND',
            'status' => ReferralCommission::STATUS_SIMULATED_UNPAID,
        ];
    }
}
