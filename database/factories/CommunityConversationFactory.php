<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\CommunityConversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityConversation>
 */
class CommunityConversationFactory extends Factory
{
    protected $model = CommunityConversation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        [$one, $two] = CommunityConversation::canonicalParticipants($u1->id, $u2->id);

        return [
            'community_id' => Community::factory(),
            'user_one_id' => $one,
            'user_two_id' => $two,
            'last_message_at' => now('UTC'),
        ];
    }
}
