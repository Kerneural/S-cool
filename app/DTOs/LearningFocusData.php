<?php

namespace App\DTOs;

use App\Models\Course;
use App\Models\Lesson;

class LearningFocusData
{
    public const STATE_CONTINUE = 'CONTINUE';

    public const STATE_COMPLETE = 'COMPLETE';

    public const STATE_LOCKED = 'LOCKED';

    public const STATE_EMPTY = 'EMPTY';

    /**
     * @param  array{
     *     id: int,
     *     title: string,
     *     starts_at: string,
     *     formatted_time: string,
     *     timezone: string,
     *     meeting_url: ?string
     * }|null  $upcomingEvent
     */
    public function __construct(
        public readonly string $state,
        public readonly ?Course $nextCourse = null,
        public readonly ?Lesson $nextLesson = null,
        public readonly int $totalLessons = 0,
        public readonly int $completedLessons = 0,
        public readonly int $percentage = 0,
        public readonly bool $hasStarted = false,
        public readonly ?array $upcomingEvent = null,
    ) {}

    public function isContinue(): bool
    {
        return $this->state === self::STATE_CONTINUE;
    }

    public function isComplete(): bool
    {
        return $this->state === self::STATE_COMPLETE;
    }

    public function isLocked(): bool
    {
        return $this->state === self::STATE_LOCKED;
    }

    public function isEmpty(): bool
    {
        return $this->state === self::STATE_EMPTY;
    }
}
