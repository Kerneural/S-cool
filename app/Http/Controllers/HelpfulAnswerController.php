<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Community;
use App\Models\Post;
use App\Services\HelpfulAnswerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HelpfulAnswerController extends Controller
{
    public function __construct(
        protected HelpfulAnswerService $helpfulAnswerService
    ) {}

    /**
     * Mark a comment as the creator-accepted helpful answer.
     */
    public function store(Request $request, Community $community, Post $post, Comment $comment): JsonResponse|RedirectResponse
    {
        $this->helpfulAnswerService->accept($community, $post, $comment, $request->user());

        if ($request->expectsJson() || $request->isJson()) {
            return response()->json([
                'status' => 'success',
                'message' => __('Helpful answer accepted.'),
                'accepted_comment_id' => $comment->id,
            ]);
        }

        return back()->with('status', 'helpful-answer-accepted');
    }

    /**
     * Revoke an accepted helpful answer.
     */
    public function destroy(Request $request, Community $community, Post $post, Comment $comment): JsonResponse|RedirectResponse
    {
        $this->helpfulAnswerService->revoke($community, $post, $comment, $request->user());

        if ($request->expectsJson() || $request->isJson()) {
            return response()->json([
                'status' => 'success',
                'message' => __('Helpful answer revoked.'),
                'accepted_comment_id' => null,
            ]);
        }

        return back()->with('status', 'helpful-answer-revoked');
    }
}
