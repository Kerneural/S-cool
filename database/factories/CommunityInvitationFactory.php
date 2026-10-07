<?php

namespace Database\Factories;

use App\Models\Community;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommunityInvitationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'community_id' => Community::factory(),
            'inviter_id' => fn (array $attributes) => Community::findOrFail($attributes['community_id'])->creator_id,
            'normalized_email' => strtolower(fake()->unique()->safeEmail()),
            'token_hash' => hash('sha256', bin2hex(random_bytes(32))),
            'status' => 'PENDING',
            'expires_at' => now()->addDays(7),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => ['status' => 'EXPIRED', 'expires_at' => now()->subMinute()]);
    }

    public function revoked(): static
    {
        return $this->state(fn () => ['status' => 'REVOKED', 'revoked_at' => now()]);
    }

    public function accepted(): static
    {
        return $this->state(fn () => ['status' => 'ACCEPTED', 'accepted_at' => now()]);
    }
}
