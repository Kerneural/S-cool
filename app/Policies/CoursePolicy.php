<?php

namespace App\Policies;

use App\Models\Community;
use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    /**
     * Determine whether the user can view the course.
     */
    public function view(User $user, Course $course): Response
    {
        $community = $course->community;

        if (! $community || ! $community->isActive()) {
            return Response::denyAsNotFound();
        }

        // Owning creator can preview drafts
        if ($community->isCreator($user)) {
            return Response::allow();
        }

        // Active members can only view PUBLISHED courses
        if ($course->isPublished() && $community->memberships()->where('user_id', $user->id)->where('status', 'ACTIVE')->exists()) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can manage/create courses.
     */
    public function create(User $user, Community $community): Response
    {
        return $community->isActive() && $community->isCreator($user)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can update the course.
     */
    public function update(User $user, Course $course): Response
    {
        $community = $course->community;

        return $community && $community->isActive() && $community->isCreator($user)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can delete the course.
     */
    public function delete(User $user, Course $course): Response
    {
        $community = $course->community;

        return $community && $community->isActive() && $community->isCreator($user)
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
