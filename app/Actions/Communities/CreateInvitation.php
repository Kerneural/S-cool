<?php

namespace App\Actions\Communities;

use App\Jobs\SendCommunityInvitation;
use App\Models\Community;
use App\Models\CommunityInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CreateInvitation
{
    public function handle(Community $community, User $creator, string $email, ?User $referrer = null): CommunityInvitation
    {
        return DB::transaction(function () use ($community, $creator, $email, $referrer): CommunityInvitation {
            $current = Community::query()->whereKey($community->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($creator)->authorize('update', $current);
            $normalized = strtolower(trim($email));
            if ($normalized === strtolower($creator->email)) {
                throw ValidationException::withMessages(['email' => 'The creator already has access.']);
            }
            if ($referrer && $normalized === strtolower($referrer->email)) {
                throw ValidationException::withMessages(['email' => 'Referrers cannot refer themselves.']);
            }
            $existing = $current->invitations()->where('normalized_email', $normalized)->where('status', 'PENDING')->get();
            if ($existing->contains(fn ($invite) => $invite->isPending())) {
                throw ValidationException::withMessages(['email' => 'A pending invitation already exists. Revoke it before sending another.']);
            }
            foreach ($existing as $expired) {
                $expired->status = 'EXPIRED';
                $expired->save();
            }
            $invite = new CommunityInvitation;
            $invite->forceFill([
                'community_id' => $current->id,
                'inviter_id' => $creator->id,
                'referrer_id' => $referrer?->id,
                'normalized_email' => $normalized,
                'expires_at' => now()->addDays(7),
            ])->save();
            SendCommunityInvitation::dispatch($invite->id)->afterCommit();

            return $invite;
        }, 3);
    }
}
