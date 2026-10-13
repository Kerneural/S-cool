<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class DirectoryController extends Controller
{
    /**
     * Display a listing of active community members and creator.
     */
    public function index(Request $request, Community $community): View
    {
        Gate::authorize('view', $community);

        $rawQ = $request->query('q');
        $search = is_string($rawQ) ? trim(mb_substr($rawQ, 0, 100)) : '';

        $membersQuery = User::query()
            ->where(function ($query) use ($community): void {
                $query->where('id', $community->creator_id)
                    ->orWhereHas('memberships', function ($q) use ($community): void {
                        $q->where('community_id', $community->id)->where('status', 'ACTIVE');
                    });
            });

        if ($search !== '') {
            $escaped = addcslashes($search, '%_\\');
            $membersQuery->where('name', 'like', "%{$escaped}%");
        }

        $members = $membersQuery->orderBy('name')->paginate(18)->withQueryString();

        return view('communities.directory.index', [
            'community' => $community,
            'members' => $members,
            'searchQuery' => $search,
        ]);
    }

    /**
     * Display the specified member profile within this community context.
     */
    public function show(Community $community, User $user): View
    {
        Gate::authorize('view', $community);

        $isCreator = $community->creator_id === $user->id;
        $membership = $community->memberships()->where('user_id', $user->id)->where('status', 'ACTIVE')->first();

        if (! $isCreator && ! $membership) {
            abort(404);
        }

        $role = $isCreator ? 'Creator' : 'Member';
        $joinedAt = $isCreator ? $community->created_at : $membership->created_at;

        return view('communities.directory.show', [
            'community' => $community,
            'member' => $user,
            'role' => $role,
            'joinedAt' => $joinedAt,
        ]);
    }

    /**
     * Stream or display a community member's avatar.
     */
    public function avatar(Community $community, User $user): SymfonyResponse
    {
        Gate::authorize('view', $community);

        $isCreator = $community->creator_id === $user->id;
        $isMember = $community->memberships()->where('user_id', $user->id)->where('status', 'ACTIVE')->exists();

        if (! $isCreator && ! $isMember) {
            abort(404);
        }

        $path = $user->avatar_path;
        $disk = Storage::disk('local');

        if (is_string($path) && preg_match('/\Aavatars\/[a-zA-Z0-9_\-\.]+\.(?:jpg|jpeg|png|webp)\z/D', $path) === 1 && $disk->exists($path)) {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = in_array($extension, ['jpg', 'jpeg'], true) ? 'image/jpeg' : 'image/'.$extension;

            return $disk->response($path, 'avatar.'.$extension, [
                'Content-Type' => $mime,
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ], 'inline');
        }

        return response(
            '<svg xmlns="http://www.w3.org/2000/svg" width="96" height="96" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>',
            200,
            [
                'Content-Type' => 'image/svg+xml',
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }
}
