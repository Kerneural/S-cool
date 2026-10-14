<?php

namespace Database\Factories;

use App\Models\CommunityConversation;
use App\Models\CommunityMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityMessage>
 */
class CommunityMessageFactory extends Factory
{
    protected $model = CommunityMessage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => CommunityConversation::factory(),
            'sender_id' => User::factory(),
            'body' => fake()->paragraph(),
            'client_send_id' => null,
            'read_at' => null,
        ];
    }
}
