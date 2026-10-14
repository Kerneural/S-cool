<?php

namespace App\Http\Controllers;

use App\Actions\CommunityContent\FeedMutation;
use App\Jobs\SendContentReportedNotification;
use App\Models\Comment;
use App\Models\Community;
use App\Models\ContentReport;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContentReportController extends Controller
{
    /**
     * Submit a content report (member only).
     */
    public function store(Request $request, Community $community): RedirectResponse
    {
        $user = $request->user();

        abort_unless($community->isActive(), 404);

        $isCreator = $community->isCreator($user);
        $isActiveMember = $community->memberships()
            ->where('user_id', $user->id)
            ->effective()
            ->exists();

        abort_unless($isCreator || $isActiveMember, 403);

        $validated = $request->validate([
            'target_type' => ['required', 'string', Rule::in([ContentReport::TARGET_POST, ContentReport::TARGET_COMMENT])],
            'target_id' => ['required', 'integer'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $targetType = $validated['target_type'];
        $targetId = (int) $validated['target_id'];
        $reason = trim($validated['reason']);

        // Verify target belongs to this active community and is visible/not deleted
        if ($targetType === ContentReport::TARGET_POST) {
            $target = $community->posts()->whereNull('deleted_at')->find($targetId);
        } else {
            $target = Comment::query()
                ->whereNull('deleted_at')
                ->whereHas('post', function ($q) use ($community) {
                    $q->where('community_id', $community->id)->whereNull('deleted_at');
                })
                ->find($targetId);
        }

        if (! $target) {
            return back()->withErrors(['target_id' => 'The reported content does not exist or has been deleted.']);
        }

        $postId = $target instanceof Post ? $target->id : $target->post_id;
        $commentId = $target instanceof Comment ? $target->id : null;

        // Serialize against content deletion, membership changes and tenant suspension.
        $created = app(FeedMutation::class)->run(
            $user, $community, $postId, $commentId,
            function (Community $currentCommunity, Post $post, ?Comment $comment) use ($user, $targetType, $targetId, $reason): bool {
                abort_unless($currentCommunity->isActive(), 404);
                $activeMember = $currentCommunity->memberships()
                    ->where('user_id', $user->id)->effective()->exists();
                abort_unless($currentCommunity->isCreator($user) || $activeMember, 403);
                Gate::authorize('view', $post);
                abort_if($targetType === ContentReport::TARGET_COMMENT && (! $comment || $comment->trashed()), 404);

                $report = ContentReport::query()->lockForUpdate()->firstOrCreate([
                    'community_id' => $currentCommunity->id,
                    'reporter_id' => $user->id,
                    'target_type' => $targetType,
                    'target_id' => $targetId,
                ], [
                    'reason' => $reason,
                    'status' => ContentReport::STATUS_OPEN,
                ]);

                if ($report->isOpen()) {
                    // afterCommit also protects replay delivery and transaction retries.
                    SendContentReportedNotification::dispatch($currentCommunity->id, $report->id);
                }

                return $report->wasRecentlyCreated;
            }
        );

        return $created
            ? back()->with('status', 'Content report submitted to community moderator.')
            : back()->with('info', 'Your report for this content was already submitted.');
    }

    /**
     * Display moderation queue (creator only).
     */
    public function index(Request $request, Community $community): View
    {
        abort_unless($community->isActive() && $community->isCreator($request->user()), 404);

        $rawStatus = $request->query('status');
        $status = is_string($rawStatus) ? strtoupper(trim($rawStatus)) : ContentReport::STATUS_OPEN;
        $allowedStatuses = ['ALL', ContentReport::STATUS_OPEN, ContentReport::STATUS_RESOLVED, ContentReport::STATUS_DISMISSED];

        if (! in_array($status, $allowedStatuses, true)) {
            $status = ContentReport::STATUS_OPEN;
        }

        $query = ContentReport::where('community_id', $community->id);

        if ($status !== 'ALL') {
            $query->where('status', $status);
        }

        $reports = $query->with(['reporter', 'resolver'])
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('communities.reports.index', [
            'community' => $community,
            'reports' => $reports,
            'status' => $status,
        ]);
    }

    /**
     * Creator action: Remove reported content and mark report resolved.
     */
    public function remove(Request $request, Community $community, ContentReport $report): RedirectResponse
    {
        abort_unless($community->isActive() && $community->isCreator($request->user()), 404);
        abort_unless((int) $report->community_id === (int) $community->id, 404);

        $validated = $request->validate([
            'resolution_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $postId = null;
        $commentId = null;

        if ($report->target_type === ContentReport::TARGET_POST) {
            $postId = (int) $report->target_id;
        } else {
            $comment = Comment::withTrashed()->find($report->target_id);
            $postId = $comment ? (int) $comment->post_id : null;
            $commentId = $comment ? (int) $comment->id : null;
        }

        app(FeedMutation::class)->run(
            $request->user(),
            $community,
            $postId,
            $commentId,
            function (Community $currentCommunity, ?Post $post, ?Comment $comment) use ($report, $request, $validated): void {
                if (! $currentCommunity->isActive()) {
                    abort(404);
                }

                if (! $currentCommunity->isCreator($request->user())) {
                    abort(403);
                }

                $lockedReport = ContentReport::where('id', $report->id)->lockForUpdate()->firstOrFail();
                abort_unless((int) $lockedReport->community_id === (int) $currentCommunity->id, 404);

                // Terminal state cannot be overwritten
                if (! $lockedReport->isOpen()) {
                    return;
                }

                if ($lockedReport->target_type === ContentReport::TARGET_POST && $post && ! $post->trashed()) {
                    Gate::authorize('delete', $post);
                    $post->delete();
                } elseif ($lockedReport->target_type === ContentReport::TARGET_COMMENT && $comment && ! $comment->trashed()) {
                    Gate::authorize('delete', $comment);
                    $comment->delete();
                }

                $reason = ! empty($validated['resolution_reason'])
                    ? trim($validated['resolution_reason'])
                    : 'Content violated community standards and was removed.';

                $lockedReport->update([
                    'status' => ContentReport::STATUS_RESOLVED,
                    'resolution_action' => ContentReport::ACTION_REMOVED,
                    'resolution_reason' => $reason,
                    'resolved_by' => $request->user()->id,
                    'resolved_at' => now(),
                ]);
            }
        );

        return back()->with('status', 'Content removed and report marked as resolved.');
    }

    /**
     * Creator action: Dismiss report without mutating feed content.
     */
    public function dismiss(Request $request, Community $community, ContentReport $report): RedirectResponse
    {
        abort_unless($community->isActive() && $community->isCreator($request->user()), 404);
        abort_unless((int) $report->community_id === (int) $community->id, 404);

        $validated = $request->validate([
            'resolution_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($community, $report, $request, $validated): void {
            $currentCommunity = Community::where('id', $community->id)->lockForUpdate()->firstOrFail();

            if (! $currentCommunity->isActive()) {
                abort(404);
            }

            if (! $currentCommunity->isCreator($request->user())) {
                abort(403);
            }

            $lockedReport = ContentReport::where('id', $report->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $lockedReport->community_id === (int) $currentCommunity->id, 404);

            // Terminal state cannot be overwritten
            if (! $lockedReport->isOpen()) {
                return;
            }

            $reason = ! empty($validated['resolution_reason'])
                ? trim($validated['resolution_reason'])
                : 'Report was reviewed and dismissed.';

            $lockedReport->update([
                'status' => ContentReport::STATUS_DISMISSED,
                'resolution_action' => ContentReport::ACTION_DISMISSED,
                'resolution_reason' => $reason,
                'resolved_by' => $request->user()->id,
                'resolved_at' => now(),
            ]);
        });

        return back()->with('status', 'Report has been dismissed.');
    }
}
