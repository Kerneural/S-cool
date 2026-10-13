<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Community;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display personal in-app notifications inbox for this community (F-08).
     */
    public function index(Request $request, Community $community): View
    {
        $this->authorizeCommunityAccess($request, $community);

        $user = $request->user();

        // Fresh retrieval and access filtering (AC-04)
        $all = AppNotification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('community_id', $community->id)
            ->latest()
            ->get();

        $visible = $all->filter(fn (AppNotification $n) => $n->isAvailableFor($user));
        $unreadCount = $visible->whereNull('read_at')->count();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $items = $visible->slice(($page - 1) * $perPage, $perPage)->values();

        $notifications = new LengthAwarePaginator(
            $items,
            $visible->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        return view('communities.notifications.index', compact('community', 'notifications', 'unreadCount'));
    }

    /**
     * Mark an individual notification as read (AC-03).
     */
    public function markAsRead(Request $request, Community $community, AppNotification $notification): RedirectResponse
    {
        abort_unless($community->isActive(), 404);

        $user = $request->user();

        // Enforce ownership: cannot change another user's notification
        abort_unless(
            (int) $notification->notifiable_id === (int) $user->id
            && (int) $notification->community_id === (int) $community->id,
            403
        );

        $notification->markAsRead();

        return back()->with('status', 'Notification marked as read.');
    }

    /**
     * Mark all visible notifications as read (AC-03).
     */
    public function markAllAsRead(Request $request, Community $community): RedirectResponse
    {
        $this->authorizeCommunityAccess($request, $community);

        $user = $request->user();

        // Bounded strictly to viewer's visible unread notifications in this community
        $visibleUnread = AppNotification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('community_id', $community->id)
            ->whereNull('read_at')
            ->get()
            ->filter(fn (AppNotification $n) => $n->isAvailableFor($user));

        foreach ($visibleUnread as $item) {
            $item->markAsRead();
        }

        return back()->with('status', 'All notifications marked as read.');
    }

    /**
     * Safe deep link navigation with fresh authorization and zero leakage (AC-04).
     */
    public function follow(Request $request, Community $community, AppNotification $notification): RedirectResponse
    {
        abort_unless($community->isActive(), 404);

        $user = $request->user();

        // Enforce ownership
        abort_unless(
            (int) $notification->notifiable_id === (int) $user->id
            && (int) $notification->community_id === (int) $community->id,
            403
        );

        $notification->markAsRead();

        // Stale deep link check: show unavailable without leaking title/name
        if (! $notification->isAvailableFor($user)) {
            return redirect()
                ->route('communities.notifications.index', $community)
                ->with('error', 'The requested content is no longer available.');
        }

        $targetUrl = $notification->targetUrl($community);

        return redirect($targetUrl ?: route('communities.notifications.index', $community));
    }

    protected function authorizeCommunityAccess(Request $request, Community $community): void
    {
        abort_unless($community->isActive(), 404);

        $user = $request->user();
        $isCreator = $community->isCreator($user);
        $isActiveMember = $community->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->exists();

        abort_unless($isCreator || $isActiveMember, 404);
    }
}
