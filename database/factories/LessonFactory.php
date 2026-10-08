<?php

namespace Database\Factories;

use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    public function definition(): array
    {
        return [
            'course_section_id' => CourseSectionFactory::new(),
            'title' => fake()->sentence(4),
            'content' => fake()->paragraphs(2, true),
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'status' => 'PUBLISHED',
            'order' => fake()->numberBetween(1, 100),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'DRAFT',
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'PUBLISHED',
        ]);
    }

    public function textOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'video_url' => null,
        ]);
    }
}
