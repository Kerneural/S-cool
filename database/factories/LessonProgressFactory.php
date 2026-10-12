<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LessonProgress>
 */
class LessonProgressFactory extends Factory
{
    protected $model = LessonProgress::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'lesson_id' => Lesson::factory(),
            'completed' => true,
            'completed_at' => now(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => true,
            'completed_at' => now(),
        ]);
    }

    public function incomplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => false,
            'completed_at' => null,
        ]);
    }
}
