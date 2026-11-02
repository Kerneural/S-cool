<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Post;
use App\Services\SocialLinkPreview;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class SocialLinkPreviewController extends Controller
{
    public function show(Community $community, Post $post, SocialLinkPreview $preview)
    {
        Gate::authorize('view', $post);
        abort_unless((int) $post->community_id === (int) $community->id, 404);
        $url = $post->link_url;
        $data = $url ? Cache::remember('social-link.'.hash('sha256', $url), 3600, fn () => $preview->fetch($url)) : [];

        return response()->json($data)->header('Cache-Control', 'private, no-store');
    }
}
