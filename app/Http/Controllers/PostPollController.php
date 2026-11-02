<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Post;
use App\Services\PostPolls;
use Illuminate\Http\Request;

class PostPollController extends Controller
{
    public function vote(Request $request, Community $community, Post $post, PostPolls $polls)
    {
        $input = $request->validate(['option_id' => ['required', 'integer', 'min:1']]);
        $polls->vote($request->user(), $community, $post->id, $input['option_id']);

        return back()->with('status', __('Vote saved.'));
    }

    public function close(Request $request, Community $community, Post $post, PostPolls $polls)
    {
        $polls->close($request->user(), $community, $post->id);

        return back()->with('status', __('Poll closed.'));
    }
}
