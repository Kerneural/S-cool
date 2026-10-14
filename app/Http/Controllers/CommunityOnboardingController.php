<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\OnboardingStepCompletion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommunityOnboardingController extends Controller
{
    /**
     * Acknowledge viewing the community introduction video.
     */
    public function acknowledgeVideo(Request $request, Community $community): RedirectResponse
    {
        if (! $community->isActive()) {
            abort(404);
        }

        $user = $request->user();
        $isCreator = $community->isCreator($user);
        $isMember = $community->memberships()->where('user_id', $user->id)->effective()->exists();

        if (! $isCreator && ! $isMember) {
            abort(404);
        }

        if (empty($community->intro_video_url)) {
            return back()->withErrors(['video' => 'No introduction video is configured for this community.']);
        }

        OnboardingStepCompletion::markStep($community, $user, 'video');

        return back()->with('status', 'video-acknowledged');
    }
}
