<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LessonImageController extends Controller
{
    public function store(Request $request, Community $community, Course $course): JsonResponse
    {
        abort_unless((int) $course->community_id === (int) $community->id, 404);
        Gate::authorize('update', $course);
        $request->validate(['image' => [
            'required',
            File::image()->dimensions(Rule::dimensions()->maxWidth(4096)->maxHeight(4096)),
            File::types(['jpg', 'jpeg', 'png', 'webp'])->extensions(['jpg', 'jpeg', 'png', 'webp'])->max(2048),
        ]]);
        $path = Storage::disk('community_media')->putFile($community->id.'/lessons/'.$course->id, $request->file('image'));
        abort_unless(is_string($path), 503);

        return response()->json([
            'url' => route('communities.courses.lesson-images.show', [$community, $course, basename($path)], false),
        ], 201);
    }

    public function show(Request $request, Community $community, Course $course, string $filename): StreamedResponse
    {
        abort_unless((int) $course->community_id === (int) $community->id, 404);
        Gate::authorize('view', $course);
        abort_unless(preg_match('/\A[a-zA-Z0-9]{40}\.(?:jpg|jpeg|png|webp)\z/D', $filename) === 1, 404);
        $url = route('communities.courses.lesson-images.show', [$community, $course, $filename], false);

        if (! $community->isCreator($request->user())) {
            abort_unless($course->hasMemberAccess($request->user()), 404);
            // Unpublished/abandoned uploads never become a member-visible media library.
            abort_unless($course->publishedLessons()->where('content', 'like', '%'.$url.'%')->exists(), 404);
        }

        $path = $community->id.'/lessons/'.$course->id.'/'.$filename;
        $disk = Storage::disk('community_media');
        abort_unless($disk->exists($path), 404);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return $disk->response($path, 'lesson-image.'.$extension, [
            'Content-Type' => in_array($extension, ['jpg', 'jpeg'], true) ? 'image/jpeg' : 'image/'.$extension,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }
}
