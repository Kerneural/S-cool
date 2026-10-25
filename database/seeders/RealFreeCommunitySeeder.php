<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;

class RealFreeCommunitySeeder extends Seeder
{
    /**
     * Run the database seeds for real crawled communities from Skool.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Real community sample content may only be seeded in local or testing environments.');
        }

        $creator = User::query()->where('email', 'creator@scool.local')->firstOrFail();
        $member = User::query()->where('email', 'member@scool.local')->firstOrFail();

        $jsonPath = file_exists(database_path('seeders/data/skool-real-communities.json'))
            ? database_path('seeders/data/skool-real-communities.json')
            : storage_path('app/skool-real-communities.json');
        if (! file_exists($jsonPath)) {
            return;
        }

        $rawJson = json_decode(file_get_contents($jsonPath), true);
        $communitiesData = $rawJson['communities'] ?? [];

        foreach ($communitiesData as $data) {
            DB::transaction(function () use ($creator, $member, $data): void {
                $community = Community::query()
                    ->where('slug', $data['slug'])
                    ->first();

                // Re-running an opt-in fixture must not overwrite local edits,
                // reopen a suspended community, or restore revoked member access.
                if ($community !== null) {
                    return;
                }

                $community = new Community;
                $community->forceFill([
                    'creator_id' => $creator->id,
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'description' => $data['description'],
                    'rules' => $data['rules'],
                    'intro_video_url' => $data['intro_video_url'] ?? null,
                    'onboarding_enabled' => true,
                    'visibility' => 'PRIVATE',
                    'access_mode' => $data['access_mode'] ?? 'FREE',
                    'price_vnd' => $data['price_vnd'] ?? null,
                    'billing_period' => $data['billing_period'] ?? 'ONE_TIME',
                    'referral_commission_rate' => 0,
                    'status' => 'ACTIVE',
                ])->save();

                // Initialize standard post categories
                PostCategory::initializeDefaultsFor($community);

                // Ensure demo member has active membership
                $membership = CommunityMembership::query()->firstOrNew([
                    'community_id' => $community->id,
                    'user_id' => $member->id,
                ]);
                $membership->forceFill([
                    'status' => 'ACTIVE',
                    'access_kind' => CommunityMembership::ACCESS_LIFETIME,
                    'expires_at' => null,
                ])->save();

                // Setup Courses, Sections & Lessons
                $courseOrder = 1;
                foreach ($data['courses'] as $courseSpec) {
                    $course = $community->courses()->where('title', $courseSpec['title'])->first();
                    $course ??= new Course;
                    $course->community_id = $community->id;
                    $course->forceFill([
                        'title' => $courseSpec['title'],
                        'description' => $courseSpec['description'] ?? null,
                        'access_mode' => $courseSpec['access_mode'] ?? Course::ACCESS_MODE_INCLUDED,
                        'price' => $courseSpec['price'] ?? null,
                        'status' => 'PUBLISHED',
                        'order' => $courseOrder++,
                        'published_revision' => max(1, (int) $course->published_revision),
                    ])->save();

                    $sectionOrder = 1;
                    $sections = $courseSpec['sections'] ?? [];
                    foreach ($sections as $secKey => $secVal) {
                        $secTitle = is_array($secVal) && isset($secVal['title']) ? $secVal['title'] : $secKey;
                        $lessonsSpec = is_array($secVal) && isset($secVal['lessons']) ? $secVal['lessons'] : $secVal;

                        $section = $course->sections()->where('title', $secTitle)->first();
                        $section ??= new CourseSection;
                        $section->course_id = $course->id;
                        $section->forceFill([
                            'title' => $secTitle,
                            'order' => $sectionOrder++,
                        ])->save();

                        if (is_array($lessonsSpec)) {
                            foreach ($lessonsSpec as $lIdx => $lessonSpec) {
                                $lesson = $section->lessons()->where('title', $lessonSpec['title'])->first();
                                $lesson ??= new Lesson;
                                $lesson->course_section_id = $section->id;
                                $lesson->forceFill([
                                    'title' => $lessonSpec['title'],
                                    'content' => $lessonSpec['content'] ?? ($lessonSpec['title']."\n\nLesson content and instructions."),
                                    'video_url' => $lessonSpec['video_url'] ?? null,
                                    'is_practical' => $lessonSpec['is_practical'] ?? false,
                                    'skill_label' => $lessonSpec['skill_label'] ?? null,
                                    'status' => 'PUBLISHED',
                                    'order' => $lIdx + 1,
                                ])->save();
                            }
                        }
                    }
                }

                // Setup Announcement Post
                $announcementsCat = $community->categories()->where('slug', 'announcements')->first();
                $postTitle = $data['post_title'] ?? ('Welcome to '.$data['name'].'! Start here and introduce yourself');
                $postBody = $data['post_body'] ?? ('Welcome to '.$data['name'].'! Please drop a quick introduction below and start learning with the community.');
                $post = Post::query()->firstOrCreate(
                    [
                        'community_id' => $community->id,
                        'title' => $postTitle,
                    ],
                    [
                        'user_id' => $creator->id,
                        'category_id' => $announcementsCat?->id,
                        'body' => $postBody,
                        'pinned_at' => CarbonImmutable::now('UTC'),
                    ]
                );

                // Add welcome comment from member
                Comment::query()->firstOrCreate(
                    [
                        'post_id' => $post->id,
                        'user_id' => $member->id,
                    ],
                    [
                        'body' => 'Excited to be here! Looking forward to learning and collaborating with everyone.',
                    ]
                );
            }, 3);
        }
    }
}
