<?php

namespace App\Actions\Communities;

use App\Models\Community;
use App\Models\CommunityInvitation;
use App\Models\CommunityMembership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcceptInvitation
{
    public function handle(int $id, User $user, string $token): Community
    {
        abort_unless(preg_match('/\A[a-f0-9]{64}\z/D', $token) === 1, 404);
        // Initial lookup supplies only the tenant key; no metadata is rendered.
        $tenantId = CommunityInvitation::query()->whereKey($id)->value('community_id');
        abort_if($tenantId === null, 404);

        return DB::transaction(function () use ($id, $user, $token, $tenantId): Community {
            $actor = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $community = Community::query()->whereKey($tenantId)->lockForUpdate()->firstOrFail();
            $invite = $community->invitations()->whereKey($id)->lockForUpdate()->firstOrFail();
            abort_unless($actor->hasVerifiedEmail() && $community->isActive()
                && $invite->isPending() && $invite->matchesToken($token)
                && $invite->normalized_email === strtolower(trim($actor->email))
                && ! $community->isCreator($actor), 404);

            if ($community->access_mode !== 'FREE') {
                throw ValidationException::withMessages(['invitation' => 'Paid access is not available until checkout is implemented. No access was granted.']);
            }
            $membership = $community->memberships()->where('user_id', $actor->id)->lockForUpdate()->first();
            abort_if($membership && ! in_array($membership->status, ['ACTIVE', 'LEFT'], true), 404);
            if (! $membership) {
                $membership = new CommunityMembership;
                $membership->forceFill(['community_id' => $community->id, 'user_id' => $actor->id]);
            }
            $membership->status = 'ACTIVE';
            $membership->save();
            $invite->forceFill(['status' => 'ACCEPTED', 'accepted_at' => now(), 'accepted_by_user_id' => $actor->id])->save();

            return $community;
        }, 3);
    }
}
