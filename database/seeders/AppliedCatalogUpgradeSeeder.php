<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LogicException;

/** Versioned content-only upgrade. Unknown or edited outlines are conflicts, never overwritten. */
class AppliedCatalogUpgradeSeeder extends Seeder
{
    private const LEGACY_TITLES = [
        'Environment Setup & Virtual Environments' => 'Environment Setup & Isolated Virtual Environments',
        'Data Structures & List Comprehensions' => 'Idiomatic Data Structures & High-Performance Comprehensions',
        'Building Production APIs with FastAPI' => 'Building Asynchronous Web APIs with FastAPI & Pydantic V2',
        'Camera Movements & Lighting Prompts' => 'Camera Movements, Lighting & Focal Length Prompts',
        'Runway Gen-3 & Kling AI Motion Brush' => 'Runway Gen-3 & Kling AI Motion Brush Control',
        'Client Scoping & Workflow Blueprinting' => 'Client Scoping, Process Discovery & Data Flow Mapping',
        'Webhook Architecture & Error Handling' => 'Webhook Architecture, Security & Payload Normalization',
        'Planner-Worker Architecture & State Machines' => 'Planner-Worker Architecture & Deterministic State Machines',
        'LLM-as-a-Judge & Automated Regression Eval' => 'Automated Regression Testing with LLM-as-a-Judge',
        'Needle Tapers, Gauges & Voltage Calibration' => 'Needle Tapers, Gauges, Groupings & Machine Stroke',
        'Depth Control & Preventing Blowouts' => 'Depth Control, Hand Speed Synchronization & Blowout Prevention',
    ];

    public function run(): void
    {
        foreach ($this->upgrade(true) as $slug => $result) {
            $this->command?->line($slug.': '.$result);
        }
    }

    /** @return array<string, string> */
    public function upgrade(bool $apply = false): array
    {
        $environment = app()->environment();
        $database = match ($environment) {
            'local' => 'scool',
            'testing' => 'scool_test',
            default => throw new LogicException('Applied catalog upgrades are local/testing only.'),
        };
        $connection = DB::connection();
        if ($connection->getDriverName() !== 'mysql'
            || $connection->getDatabaseName() !== $database
            || ($environment === 'local' && config('app.url') !== 'http://127.0.0.1:8080')
            || $connection->selectOne('SELECT DATABASE() AS name')->name !== $database) {
            throw new LogicException('Applied catalog upgrade requires canonical scool or guarded scool_test.');
        }

        $creator = User::where('email', 'creator@scool.local')->firstOrFail();
        $baselines = json_decode(file_get_contents(database_path('data/applied-catalog-baselines.json')), true, 512, JSON_THROW_ON_ERROR);
        $seeder = new RealCommunitySampleSeeder;
        $report = [];
        foreach ($seeder->catalog() as $spec) {
            $report[$spec['slug']] = DB::transaction(function () use ($creator, $baselines, $seeder, $spec, $apply): string {
                $community = Community::where('slug', $spec['slug'])->lockForUpdate()->first();
                if (! $community || (int) $community->creator_id !== (int) $creator->id) {
                    return 'CONFLICT: missing or foreign-owned community';
                }
                $course = $community->courses()->where('title', $spec['course']['title'])->lockForUpdate()->first();
                if (! $course) {
                    return 'CONFLICT: missing or renamed course';
                }
                $sections = $course->sections()->lockForUpdate()->get();
                foreach ($sections as $section) {
                    $section->setRelation('lessons', $section->lessons()->lockForUpdate()->get());
                }
                $actual = $this->snapshot($course, $sections);
                $desired = $this->desired($spec, $seeder);
                $matching = null;
                foreach ($baselines as $version => $catalog) {
                    if (($catalog[$spec['slug']]['course'] ?? null) === $actual) {
                        $matching = $version;
                        break;
                    }
                }
                if ($actual !== $desired && $matching === null) {
                    return 'CONFLICT: edited or unknown course outline preserved';
                }
                $communityChanges = [];
                foreach (['description', 'rules', 'intro_video_url'] as $field) {
                    if ($community->{$field} === $spec[$field]) {
                        continue;
                    }
                    foreach ($baselines as $catalog) {
                        if (($catalog[$spec['slug']]['community'][$field] ?? null) === $community->{$field}) {
                            $communityChanges[$field] = $spec[$field];
                            break;
                        }
                    }
                }
                if ($actual === $desired && $communityChanges === []) {
                    return 'CURRENT';
                }
                if (! $apply) {
                    return 'WOULD_UPGRADE: '.($matching ?? 'orientation only');
                }
                $lessonsByTitle = $sections->flatMap(fn ($section) => $section->lessons)
                    ->keyBy(fn ($lesson): string => self::LEGACY_TITLES[$lesson->title] ?? $lesson->title);
                $desiredTitles = collect($desired['sections'])->flatMap(fn ($section) => $section['lessons'])->pluck('title');
                if ($lessonsByTitle->keys()->diff($desiredTitles)->isNotEmpty()) {
                    return 'CONFLICT: no semantic lesson mapping; preserved';
                }
                if ($communityChanges !== []) {
                    $community->forceFill($communityChanges)->save();
                }
                if ($actual === $desired) {
                    return 'UPGRADED: orientation only';
                }
                // Reuse IDs so progress/evidence/history retain their original ancestry.
                // Never assign publication, access, prices, covers or account/membership state.
                $course->forceFill(['description' => $desired['description']])->save();
                foreach ($desired['sections'] as $i => $sectionSpec) {
                    $section = $sections[$i] ?? new CourseSection(['course_id' => $course->id]);
                    $section->forceFill(['title' => $sectionSpec['title'], 'order' => $sectionSpec['order']])->save();
                    foreach ($sectionSpec['lessons'] as $j => $lessonSpec) {
                        $lesson = $lessonsByTitle->get($lessonSpec['title']) ?? new Lesson;
                        if (! $lesson->exists) {
                            $lesson->forceFill(['status' => $course->status]);
                        }
                        $lesson->forceFill(['course_section_id' => $section->id]);
                        $lesson->forceFill($lessonSpec)->save();
                    }
                }

                return 'UPGRADED: '.$matching;
            }, 3);
        }

        return $report;
    }

    /** @return array<string, mixed> */
    private function snapshot(Course $course, Collection $sections): array
    {
        return ['title' => $course->title, 'description' => $course->description, 'sections' => $sections->map(fn ($section): array => [
            'title' => $section->title, 'order' => (int) $section->order,
            'lessons' => $section->lessons->map(fn ($lesson): array => [
                'title' => $lesson->title, 'content' => $lesson->content, 'video_url' => $lesson->video_url,
                'order' => (int) $lesson->order, 'is_practical' => (bool) $lesson->is_practical, 'skill_label' => $lesson->skill_label,
            ])->all(),
        ])->all()];
    }

    /** @param array<string, mixed> $spec
     * @return array<string, mixed>
     */
    private function desired(array $spec, RealCommunitySampleSeeder $seeder): array
    {
        $sections = [];
        foreach ($spec['course']['sections'] as $i => $section) {
            $lessons = [];
            foreach ($section['lessons'] as $j => $lesson) {
                $lessons[] = ['title' => $lesson['title'], 'content' => $seeder->buildLessonContent($lesson),
                    'video_url' => $lesson['video_url'] ?? null, 'order' => $j + 1,
                    'is_practical' => $lesson['is_practical'] ?? false, 'skill_label' => $lesson['skill_label'] ?? null];
            }
            $sections[] = ['title' => $section['title'], 'order' => $i + 1, 'lessons' => $lessons];
        }

        return ['title' => $spec['course']['title'], 'description' => $spec['course']['description'], 'sections' => $sections];
    }
}
