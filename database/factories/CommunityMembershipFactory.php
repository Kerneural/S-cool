<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommunityMembershipFactory extends Factory
{
    public function definition(): array
    {
        return ['community_id' => Community::factory(), 'user_id' => User::factory(), 'status' => 'PENDING_PAYMENT'];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => 'ACTIVE']);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => 'SUSPENDED']);
    }

    public function removed(): static
    {
        return $this->state(fn () => ['status' => 'REMOVED']);
    }

    public function left(): static
    {
        return $this->state(fn () => ['status' => 'LEFT']);
    }
}
