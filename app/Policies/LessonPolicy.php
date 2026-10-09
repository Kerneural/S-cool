<?php

namespace App\Policies;

use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LessonPolicy
{
    /**
     * Determine whether the user can view the lesson.
     */
    public function view(User $user, Lesson $lesson): Response
    {
        $section = $lesson->section;
        $course = $section?->course;
        $community = $course?->community;

        if (! $community || ! $community->isActive()) {
            return Response::denyAsNotFound();
        }

        // Creator can preview drafts
        if ($community->isCreator($user)) {
            return Response::allow();
        }

        // Active members require BOTH course and lesson to be PUBLISHED
        if ($course->isPublished() && $lesson->isPublished()
            && $community->memberships()->where('user_id', $user->id)->where('status', 'ACTIVE')->exists()) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can create lessons.
     */
    public function create(User $user, CourseSection $section): Response
    {
        $community = $section->course?->community;

        return $community && $community->isActive() && $community->isCreator($user)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can update the lesson.
     */
    public function update(User $user, Lesson $lesson): Response
    {
        $community = $lesson->section?->course?->community;

        return $community && $community->isActive() && $community->isCreator($user)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can delete the lesson.
     */
    public function delete(User $user, Lesson $lesson): Response
    {
        return Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can track progress on the lesson.
     */
    public function updateProgress(User $user, Lesson $lesson): Response
    {
        $section = $lesson->section;
        $course = $section?->course;
        $community = $course?->community;

        if (! $community || ! $community->isActive()) {
            return Response::denyAsNotFound();
        }

        if (! $course->isPublished() || ! $lesson->isPublished()) {
            return Response::denyAsNotFound();
        }

        $isActiveMember = $community->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->exists();

        return $isActiveMember
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
