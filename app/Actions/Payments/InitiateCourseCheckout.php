<?php

namespace App\Actions\Payments;

use App\Models\Community;
use App\Models\Course;
use App\Models\CourseEntitlement;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InitiateCourseCheckout
{
    /**
     * Initiate paid checkout for a one-time course purchase by an active community member.
     */
    public function handle(Community $community, Course $course, User $user): Payment
    {
        return DB::transaction(function () use ($community, $course, $user): Payment {
            $actor = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $lockedCommunity = Community::query()->whereKey($community->id)->lockForUpdate()->firstOrFail();
            $lockedCourse = Course::query()->whereKey($course->id)->where('community_id', $lockedCommunity->id)->lockForUpdate()->firstOrFail();

            abort_unless(
                $actor->hasVerifiedEmail()
                && $lockedCommunity->isActive()
                && ! $lockedCommunity->isCreator($actor),
                404
            );

            // Active community membership is strictly required
            $membership = $lockedCommunity->memberships()
                ->where('user_id', $actor->id)
                ->lockForUpdate()
                ->first();

            if (! $membership || ! $membership->hasEffectiveAccess()) {
                abort(404);
            }

            if (! $lockedCourse->isPublished()) {
                abort(404);
            }

            if (! $lockedCourse->isOneTime()) {
                throw ValidationException::withMessages([
                    'course' => 'This course is included with community membership and does not require purchase.',
                ]);
            }

            $price = (int) $lockedCourse->price;
            if ($price <= 0) {
                throw ValidationException::withMessages([
                    'course' => 'Course price is not configured.',
                ]);
            }

            // Already entitled?
            $hasEntitlement = CourseEntitlement::where('community_id', $lockedCommunity->id)
                ->where('course_id', $lockedCourse->id)
                ->where('user_id', $actor->id)
                ->exists();

            if ($hasEntitlement) {
                throw ValidationException::withMessages([
                    'course' => 'You already have access to this course.',
                ]);
            }

            // Reuse existing pending payment or create new one
            $payment = Payment::where('community_id', $lockedCommunity->id)
                ->where('user_id', $actor->id)
                ->where('course_id', $lockedCourse->id)
                ->where('purpose', Payment::PURPOSE_COURSE_ACCESS)
                ->where('status', Payment::STATUS_PENDING)
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                $reference = 'CRS-'.strtoupper(Str::random(10));

                $payment = Payment::create([
                    'external_reference' => $reference,
                    'community_id' => $lockedCommunity->id,
                    'user_id' => $actor->id,
                    'course_id' => $lockedCourse->id,
                    'purpose' => Payment::PURPOSE_COURSE_ACCESS,
                    'amount' => $price,
                    'currency' => 'VND',
                    'status' => Payment::STATUS_PENDING,
                    'gateway_provider' => Payment::PROVIDER_SEPAY_SANDBOX,
                ]);
            }

            return $payment;
        });
    }
}
