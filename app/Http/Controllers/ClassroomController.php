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

        $coursesQuery = $community->courses()->ordered();

        if (! $isCreator) {
            $coursesQuery->published();
        }

        $courseOrder = $isCreator ? (clone $coursesQuery)->pluck('id')->all() : [];
        $courses = $coursesQuery->withCount(['lessons as visible_lessons_count' => function ($query) use ($isCreator) {
            if (! $isCreator) {
                $query->published();
            }
        }])->paginate(12);

        return view('classroom.index', compact('community', 'courses', 'isCreator', 'courseOrder'));
    }
}
