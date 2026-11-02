<?php

namespace App\Http\Controllers;

use App\Actions\CommunityContent\FeedMutation;
use App\Models\Comment;
use App\Models\Community;
use App\Models\Post;
use App\Models\SocialAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class SocialAttachmentController extends Controller
{
    public function show(Community $community, SocialAttachment $attachment)
    {
        abort_unless((int) $attachment->community_id === (int) $community->id, 404);
        $post = $community->posts()->findOrFail($attachment->post_id);
        Gate::authorize('view', $post);
        if ($attachment->comment_id !== null) {
            abort_unless($post->comments()->whereKey($attachment->comment_id)->exists(), 404);
        }
        abort_unless(preg_match('~\Asocial/[a-zA-Z0-9]{40}\.[a-z0-9]+\z~D', $attachment->path) === 1, 404);
        $disk = Storage::disk('community_media');
        abort_unless($disk->exists($attachment->path), 404);

        return $disk->response($attachment->path, $attachment->name, [
            'Content-Type' => $attachment->mime,
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ], $attachment->is_image ? 'inline' : 'attachment');
    }

    public function destroy(Request $request, Community $community, SocialAttachment $attachment)
    {
        abort_unless((int) $attachment->community_id === (int) $community->id, 404);
        app(FeedMutation::class)->run($request->user(), $community, $attachment->post_id, $attachment->comment_id, function (Community $current, Post $post, ?Comment $comment) use ($attachment): void {
            Gate::authorize('update', $comment ?? $post);
            $attachment->delete();
            // Retain private bytes: no file deletion inside retried DB transactions.
        });

        return back()->with('status', __('Attachment removed.'));
    }
}
