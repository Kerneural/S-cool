<?php

namespace App\Jobs;

use App\Models\AppNotification;
use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCoursePublishedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $courseId,
        public int $publishedRevision,
    ) {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $course = Course::query()->find($this->courseId);
        if (! $course || $course->status !== 'PUBLISHED' || (int) $course->published_revision !== $this->publishedRevision) {
            return;
        }

        $community = $course->community;
        if (! $community || ! $community->isActive()) {
            return;
        }

        // Stream active members excluding creator in database chunks of 100
        $community->memberships()
            ->effective()
            ->where('user_id', '!=', $community->creator_id)
            ->whereHas('user', function ($q) {
                $q->where('notify_courses', true);
            })
            ->orderBy('id')
            ->chunkById(100, function ($memberships) use ($course, $community) {
                foreach ($memberships as $membership) {
                    $membership->setRelation('community', $community);
                    if (! $membership->hasEffectiveAccess()) {
                        continue;
                    }
                    $userId = (int) $membership->user_id;
                    $dedupeKey = "course_published:course_{$course->id}_rev_{$this->publishedRevision}:recipient_{$userId}";

                    AppNotification::createNotification(
                        userId: $userId,
                        communityId: $community->id,
                        type: 'course_published',
                        dedupeKey: $dedupeKey,
                        data: [
                            'course_id' => $course->id,
                            'published_revision' => $this->publishedRevision,
                        ],
                    );
                }
            });
    }
}
