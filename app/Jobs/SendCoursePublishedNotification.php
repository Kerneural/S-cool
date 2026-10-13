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

        // Active members excluding the creator
        $recipientIds = $community->memberships()
            ->where('status', 'ACTIVE')
            ->where('user_id', '!=', $community->creator_id)
            ->pluck('user_id');

        // Bounded chunks through the database queue (Delivery Plan F-08 contract)
        foreach ($recipientIds->chunk(100) as $chunk) {
            foreach ($chunk as $userId) {
                $dedupeKey = "course_published:course_{$course->id}_rev_{$this->publishedRevision}:recipient_{$userId}";

                AppNotification::createNotification(
                    userId: (int) $userId,
                    communityId: $community->id,
                    type: 'course_published',
                    dedupeKey: $dedupeKey,
                    data: [
                        'course_id' => $course->id,
                        'published_revision' => $this->publishedRevision,
                    ],
                );
            }
        }
    }
}
