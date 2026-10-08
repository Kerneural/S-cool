<?php

namespace App\Http\Controllers;

use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(Request $request, Community $community): View
    {
        Gate::authorize('view', $community);

        $user = $request->user();
        $isCreator = $community->isCreator($user);
        $isActiveMember = $community->memberships()->where('user_id', $user->id)->where('status', 'ACTIVE')->exists();

        $coursesQuery = $community->courses()->ordered();

        if (! $isCreator) {
            $coursesQuery->published();
        }

        $courses = $coursesQuery->with([
            'sections' => function ($query) use ($isCreator) {
                $query->ordered();
                if (! $isCreator) {
                    $query->whereHas('lessons', fn ($q) => $q->published());
                }
            },
            'sections.lessons' => function ($query) use ($isCreator, $user) {
                $query->ordered();
                if (! $isCreator) {
                    $query->published();
                }
                $query->with(['progresses' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                }]);
            },
        ])->get();

        return view('classroom.index', compact('community', 'courses', 'isCreator', 'isActiveMember'));
    }
}
