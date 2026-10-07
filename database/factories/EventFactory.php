<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = Carbon::now()->addDays(fake()->numberBetween(1, 14))->setMinute(0)->setSecond(0);
        $endsAt = $startsAt->copy()->addHours(2);

        return [
            'community_id' => Community::factory(),
            'creator_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'timezone' => 'Asia/Ho_Chi_Minh',
            'meeting_url' => fake()->url(),
            'status' => 'SCHEDULED',
        ];
    }

    /**
     * Indicate that the event is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'CANCELLED',
        ]);
    }
}
