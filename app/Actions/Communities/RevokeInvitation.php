<?php

namespace App\Actions\Communities;

use App\Models\Community;
use App\Models\CommunityInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RevokeInvitation
{
    public function handle(Community $community, CommunityInvitation $invitation, User $creator): void
    {
        DB::transaction(function () use ($community, $invitation, $creator): void {
            $current = Community::query()->whereKey($community->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($creator)->authorize('update', $current);
            $invite = $current->invitations()->whereKey($invitation->id)->lockForUpdate()->firstOrFail();
            abort_unless($invite->isPending(), 404);
            $invite->forceFill(['status' => 'REVOKED', 'revoked_at' => now()])->save();
        }, 3);
    }
}
