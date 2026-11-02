<?php

namespace App\Support;

use App\Models\Community;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class CommunityIdentity
{
    public static function prime(Community $community, array $ids): void
    {
        $ids = array_values(array_unique(array_filter($ids)));
        if ($ids === []) {
            return;
        }
        $active = $community->memberships()->effective()->whereIn('user_id', $ids)->pluck('user_id')->all();
        $active[] = $community->creator_id;
        foreach ($ids as $id) {
            request()->attributes->set('social-profile.'.$community->id.'.'.$id,
                in_array((int) $id, array_map('intval', $active), true)
                    ? route('communities.directory.show', [$community, $id]) : false);
        }
    }

    public static function profile(Community $community, ?User $user): ?string
    {
        $request = request();
        $accessKey = 'social-profile-access.'.$community->id;
        if (! $request->attributes->has($accessKey)) {
            $request->attributes->set($accessKey, Gate::allows('view', $community));
        }
        if (! $user || ! $request->attributes->get($accessKey)) {
            return null;
        }
        $key = 'social-profile.'.$community->id.'.'.$user->id;
        if (! $request->attributes->has($key)) {
            $available = $community->isCreator($user)
                || $community->memberships()->where('user_id', $user->id)->effective()->exists();
            $request->attributes->set($key, $available ? route('communities.directory.show', [$community, $user]) : false);
        }

        return $request->attributes->get($key) ?: null;
    }
}
