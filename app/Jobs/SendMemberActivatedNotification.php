<?php

namespace App\Jobs;

use App\Models\AppNotification;
use App\Models\Community;
use App\Models\CommunityMembership;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMemberActivatedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $communityId,
        public int $membershipId,
        public string $sourceType,
        public int $sourceId,
    ) {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $community = Community::query()->find($this->communityId);
        if (! $community || ! $community->isActive()) {
            return;
        }

        $membership = CommunityMembership::query()->find($this->membershipId);
        if (! $membership || ! $membership->hasEffectiveAccess()) {
            return;
        }

        $creatorId = (int) $community->creator_id;
        $dedupeKey = "member_activated:{$this->sourceType}_{$this->sourceId}:recipient_{$creatorId}";

        AppNotification::createNotification(
            userId: $creatorId,
            communityId: $community->id,
            type: 'member_activated',
            dedupeKey: $dedupeKey,
            data: [
                'membership_id' => $membership->id,
                'member_id' => $membership->user_id,
                'source_type' => $this->sourceType,
                'source_id' => $this->sourceId,
            ],
        );
    }
}
