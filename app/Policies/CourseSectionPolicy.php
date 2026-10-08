<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CourseSectionPolicy
{
    public function create(User $user, Course $course): Response
    {
        $community = $course->community;

        return $community && $community->isActive() && $community->isCreator($user)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function update(User $user, CourseSection $section): Response
    {
        $community = $section->course?->community;

        return $community && $community->isActive() && $community->isCreator($user)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function delete(User $user, CourseSection $section): Response
    {
        $community = $section->course?->community;

        return $community && $community->isActive() && $community->isCreator($user)
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
