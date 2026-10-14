<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Event;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Demo content may only be seeded in local or testing environments.');
        }

        DB::transaction(function (): void {
            $creator = User::query()->where('email', 'creator@scool.local')->firstOrFail();
            $member = User::query()->where('email', 'member@scool.local')->firstOrFail();

            $freeCommunity = $this->community(
                creator: $creator,
                slug: 'demo-free-learning',
                name: 'Demo Free Learning',
                description: 'Invite-only free community for the local golden-flow demo.',
                accessMode: 'FREE',
            );
            $paidCommunity = $this->community(
                creator: $creator,
                slug: 'demo-paid-learning',
                name: 'Demo Paid Learning',
                description: 'Invite-only paid community for SePay Test Mode validation.',
                accessMode: 'PAID',
                priceVnd: 100000,
            );

            $this->activeMembership($freeCommunity, $member);
            $freeLesson = $this->content($freeCommunity, $creator, 'Free');
            $this->content($paidCommunity, $creator, 'Paid');

            LessonProgress::query()->firstOrCreate(
                ['user_id' => $member->id, 'lesson_id' => $freeLesson->id],
                ['completed' => true, 'completed_at' => CarbonImmutable::parse('2026-10-01 00:00:00', 'UTC')],
            );

            $post = Post::query()->firstOrCreate(
                [
                    'community_id' => $freeCommunity->id,
                    'user_id' => $creator->id,
                    'title' => 'Welcome to the free demo community',
                ],
                ['body' => 'Use this thread to validate the Feed and member discussion flow.'],
            );

            Comment::query()->firstOrCreate(
                ['post_id' => $post->id, 'user_id' => $member->id],
                ['body' => 'The demo member can participate without receiving creator permissions.'],
            );
        }, 3);
    }

    private function community(
        User $creator,
        string $slug,
        string $name,
        string $description,
        string $accessMode,
        ?int $priceVnd = null,
    ): Community {
        $community = Community::query()->where('slug', $slug)->first();

        if ($community !== null) {
            if ((int) $community->creator_id !== (int) $creator->id) {
                throw new LogicException("Demo community slug '{$slug}' is already owned by another user.");
            }

            return $community;
        }

        $community = new Community;
        $community->forceFill([
            'creator_id' => $creator->id,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'visibility' => 'PRIVATE',
            'access_mode' => $accessMode,
            'price_vnd' => $priceVnd,
            'status' => 'ACTIVE',
        ])->save();

        PostCategory::initializeDefaultsFor($community);

        return $community;
    }

    private function activeMembership(Community $community, User $member): void
    {
        if (CommunityMembership::query()
            ->where('community_id', $community->id)
            ->where('user_id', $member->id)
            ->exists()) {
            return;
        }

        $membership = new CommunityMembership;
        $membership->forceFill([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'ACTIVE',
        ])->save();
    }

    private function content(Community $community, User $creator, string $label): Lesson
    {
        Post::query()->firstOrCreate(
            [
                'community_id' => $community->id,
                'user_id' => $creator->id,
                'title' => "{$label} community announcement",
            ],
            ['body' => "This deterministic {$label} post is safe to reuse in the local demo."],
        );

        $course = $community->courses()->firstOrCreate(
            ['title' => "{$label} Demo Course"],
            [
                'description' => "Published course for the {$label} community demo.",
                'status' => 'PUBLISHED',
                'order' => 1,
            ],
        );
        $section = $course->sections()->firstOrCreate(
            ['title' => 'Getting Started'],
            ['order' => 1],
        );
        $firstLesson = $section->lessons()->firstOrCreate(
            ['title' => "{$label} Orientation"],
            [
                'content' => "Start here to validate the {$label} classroom flow.",
                'status' => 'PUBLISHED',
                'order' => 1,
            ],
        );
        $section->lessons()->firstOrCreate(
            ['title' => "{$label} Practice"],
            [
                'content' => "Complete this lesson during the {$label} progress demo.",
                'status' => 'PUBLISHED',
                'order' => 2,
            ],
        );

        Event::query()->firstOrCreate(
            [
                'community_id' => $community->id,
                'creator_id' => $creator->id,
                'title' => "{$label} Demo Workshop",
            ],
            [
                'description' => "Scheduled event for the {$label} community demo.",
                'starts_at' => CarbonImmutable::parse('2030-01-15 02:00:00', 'UTC'),
                'ends_at' => CarbonImmutable::parse('2030-01-15 03:00:00', 'UTC'),
                'timezone' => 'Asia/Ho_Chi_Minh',
                'meeting_url' => 'https://meet.example.test/scool-demo',
                'status' => 'SCHEDULED',
            ],
        );

        return $firstLesson;
    }
}
