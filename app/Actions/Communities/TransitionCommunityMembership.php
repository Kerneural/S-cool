<?php

namespace App\Actions\Communities;

use App\Jobs\SendMemberActivatedNotification;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\CommunityMembershipAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransitionCommunityMembership
{
    public function handle(
        User $actor,
        Community $community,
        CommunityMembership $membership,
        string $target,
        string $reason,
    ): CommunityMembership {
        return DB::transaction(function () use ($actor, $community, $membership, $target, $reason): CommunityMembership {
            /** @var Community $lockedCommunity */
            $lockedCommunity = Community::query()->lockForUpdate()->findOrFail($community->id);

            abort_unless($lockedCommunity->isActive() && $lockedCommunity->isCreator($actor), 404);

            /** @var CommunityMembership $lockedMembership */
            $lockedMembership = $lockedCommunity->memberships()
                ->whereKey($membership->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_if($lockedMembership->user_id === $lockedCommunity->creator_id, 403);

            $previousStatus = $lockedMembership->status;
            $lockedMembership->transitionTo($target);

            $action = CommunityMembershipAction::create([
                'actor_id' => $actor->id,
                'community_id' => $lockedCommunity->id,
                'membership_id' => $lockedMembership->id,
                'action' => match ($target) {
                    'SUSPENDED' => 'SUSPEND',
                    'ACTIVE' => 'REACTIVATE',
                    'REMOVED' => 'REMOVE',
                    default => $target,
                },
                'previous_status' => $previousStatus,
                'new_status' => $target,
                'reason' => $reason,
                'created_at' => now(),
            ]);

            if ($target === 'ACTIVE') {
                SendMemberActivatedNotification::dispatch($lockedCommunity->id, $lockedMembership->id, 'action', $action->id);
            }

            return $lockedMembership->refresh();
        });
    }
}
