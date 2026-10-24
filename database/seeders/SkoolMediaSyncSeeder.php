<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use LogicException;

class SkoolMediaSyncSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Sample media may only be seeded in local or testing environments.');
        }

        $creator = User::query()->where('email', 'creator@scool.local')->firstOrFail();
        // Assets directory
        $commAssetsDir = database_path('seeders/assets/community-covers');
        $courseAssetsDir = database_path('seeders/assets/course-covers');
        // Read bundled assets only. Source URLs below document provenance;
        // seeding must not fetch remote content or modify repository assets.

        $items = [
            [
                'slug' => 'python-developers',
                'comm_url' => 'https://assets.skool.com/f/bc0ed33173ce43e8b01ed1177f824ee9/dd34c72a727f4764a09543ad70c6be85c6f7df8261494dac8c78d41b8cf9105d-md.jpg',
                'course_title' => 'Python Zero to Production',
                'course_url' => 'https://assets.skool.com/f/bc0ed33173ce43e8b01ed1177f824ee9/d47926a44ecb4aea84f1ead852ceb6dbe8d72f50a7014416a9c24da77c949a2c',
            ],
            [
                'slug' => 'ai-automation-society',
                'comm_url' => 'https://assets.skool.com/f/b51c57150c70495899bf4bc8aaee8166/ef969dbbebb3454a95f009eecf68564e39d455ef8d644ae1a1be7f88bb295297.png',
                'course_title' => 'Start Here',
                'course_url' => 'https://assets.skool.com/f/b51c57150c70495899bf4bc8aaee8166/cb1da39e8fae4851afabe1a50c0fda3c1cfa43c79b7041778874c96634651ad1.jpg',
            ],
            [
                'slug' => 'ai-automation-society',
                'comm_url' => null,
                'course_title' => '7 Day AIS Challenge',
                'course_url' => 'https://assets.skool.com/f/b51c57150c70495899bf4bc8aaee8166/1fc00d46830d472bbdffc95db0d07e340f3fbf3de2c24286a1293bd83dec3cf7.jpg',
            ],
            [
                'slug' => 'learn-ai',
                'comm_url' => 'https://assets.skool.com/f/83f66d7eb37844e2adbdab53dd48b3ce/c70ea5a870fc469ba0c0f42434397da604abe53a2f674dfcbee7a0fb4c7ace86.png',
                'course_title' => 'Start Here',
                'course_url' => 'https://assets.skool.com/f/83f66d7eb37844e2adbdab53dd48b3ce/84d3ff0d13d44c6ab136e23568b8a59f9be09a1adc8247e98b99baf3e87870df.jpg',
            ],
            [
                'slug' => 'learn-ai',
                'comm_url' => null,
                'course_title' => 'AI Consultant Pathway',
                'course_url' => 'https://assets.skool.com/f/83f66d7eb37844e2adbdab53dd48b3ce/1c82af0c33aa41108c9a344eb5f7ae448b1a9a46ab53475f84009a68111ffcaa.jpg',
            ],
            [
                'slug' => 'max-business-school',
                'comm_url' => 'https://assets.skool.com/f/9fed30752196495cbc84dcf7d93febd2/efb59c37e16f4625927b0de00eef866f7b7aaa5631024d7fb3a5ca4502736b05',
                'course_title' => 'FREE Master in Digital Marketing & Business',
                'course_url' => 'https://assets.skool.com/f/9fed30752196495cbc84dcf7d93febd2/efb59c37e16f4625927b0de00eef866f7b7aaa5631024d7fb3a5ca4502736b05',
            ],
            [
                'slug' => 'the-ai-advantage',
                'comm_url' => 'https://assets.skool.com/f/f09a730f782d4507a02c22164695a366/413f33448216462e9aa2326df42b6235905382bb4f5c4592a1f2fdc50c357334.jpg',
                'course_title' => 'Start Here',
                'course_url' => 'https://assets.skool.com/f/f09a730f782d4507a02c22164695a366/6d1faedee6ce4e02984cd117a95a69e7038ec46cbc0f46c9854d4c0303e9255b.jpg',
            ],
            [
                'slug' => 'the-ai-advantage',
                'comm_url' => null,
                'course_title' => "Get Started with Claude: Even If You've Tried It",
                'course_url' => 'https://assets.skool.com/f/f09a730f782d4507a02c22164695a366/f8eaa46a17ac4e47a6ca7b701d31f7a636ea2f94042a44fca7a919650bc2fc10.jpg',
            ],
            [
                'slug' => 'cliefnotes',
                'comm_url' => 'https://assets.skool.com/f/9a42d1d697754c499c3effeee8135e03/f1ad7e97ae994efdb0ef35af54331e1f3025ba2af59d4962a0699273b3a763c3.png',
                'course_title' => 'The Foundation',
                'course_url' => 'https://assets.skool.com/f/9a42d1d697754c499c3effeee8135e03/2a4034245dd24171bd768b5ccd85ec20ecbb1a2c1b7f4e35ac716031e73d2ded.jpg',
            ],
            [
                'slug' => 'docker-engineering',
                'comm_url' => null, // keep existing community cover
                'course_title' => 'Docker Engineering: Containers to Production',
                'course_url' => 'https://assets.skool.com/f/52fb4ccdcf3b47bf9bd07e37acca88c7/4d546573050547d49e29c4289e8d71205497ff85703b43e4be9887b2b61c3465.jpg',
            ],
            [
                'slug' => 'cloud-master',
                'comm_url' => null, // keep existing community cover
                'course_title' => 'AWS Cloud Foundations and Well-Architected Thinking',
                'course_url' => 'https://assets.skool.com/f/02f0e829891d4426a1ca05739fe50345/f106461719554461b4b42e2d8510348d8dd815df9972465bad9a22a6813f2e0a.jpg',
            ],
            [
                'slug' => 'networking-learning',
                'comm_url' => null, // keep existing community cover
                'course_title' => 'Networking Foundations: From Packet to Production',
                'course_url' => 'https://assets.skool.com/f/bc61fee8bdd148cea07ba0ee5153042c/f51be11b88bc408e94ec4453b69749f8e296066aed414dd7ba0372ebaeedc3b3.jpg',
            ],
            [
                'slug' => 'ai-engineer',
                'comm_url' => null, // keep existing community cover
                'course_title' => 'Applied AI Engineering: Data to Reliable Service',
                'course_url' => 'https://assets.skool.com/f/bc8f27f07047490f8c1d29f381fa252d/ce34b5cbe8204cf6aef23b62315baea72f6dd2947d5f4c26ae5a526e80d453f1.jpg',
            ],
        ];

        foreach ($items as $item) {
            $community = Community::query()->where('slug', $item['slug'])
                ->where('creator_id', $creator->id)->first();
            if (! $community) {
                continue;
            }

            // 1. Sync Community Cover if specified
            if ($item['comm_url'] !== null) {
                $commLocalPath = $commAssetsDir.'/'.$item['slug'].'.jpg';
                $storagePath = $community->id.'/'.sha1('scool-comm-cover:'.$community->slug).'.jpg';
                $this->seedCover($community, $commLocalPath, $storagePath, 2 * 1024 * 1024);
            }

            // 2. Sync Course Cover
            if ($item['course_url'] !== null) {
                $course = $community->courses()->where('title', $item['course_title'])->first();
                if ($course) {
                    $courseLocalPath = $courseAssetsDir.'/'.sha1($item['course_title']).'.jpg';
                    $courseStoragePath = $community->id.'/courses/'.sha1('scool-course-cover:'.$course->id).'.jpg';
                    $this->seedCover($course, $courseLocalPath, $courseStoragePath, 5 * 1024 * 1024);
                }
            }
        }

        // 3. Sync all courses from crawled real communities
        $jsonPath = database_path('seeders/data/skool-real-communities.json');
        if (file_exists($jsonPath)) {
            $rawJson = json_decode(file_get_contents($jsonPath), true);
            foreach ($rawJson['communities'] ?? [] as $cData) {
                $community = Community::query()->where('slug', $cData['slug'])
                    ->where('creator_id', $creator->id)->first();
                if (! $community) {
                    continue;
                }

                foreach ($cData['courses'] ?? [] as $courseData) {
                    $courseTitle = $courseData['title'] ?? null;
                    if (! $courseTitle) {
                        continue;
                    }

                    $course = $community->courses()->where('title', $courseTitle)->first();
                    if (! $course) {
                        continue;
                    }

                    $scopedLocalPath = $courseAssetsDir.'/'.sha1($community->slug.':'.$courseTitle).'.jpg';
                    $legacyLocalPath = $courseAssetsDir.'/'.sha1($courseTitle).'.jpg';
                    $courseLocalPath = file_exists($scopedLocalPath) ? $scopedLocalPath : $legacyLocalPath;

                    if (file_exists($courseLocalPath)) {
                        $courseStoragePath = $community->id.'/courses/'.sha1('scool-course-cover:'.$course->id).'.jpg';
                        $this->seedCover($course, $courseLocalPath, $courseStoragePath, 5 * 1024 * 1024);
                    }
                }
            }
        }
    }

    private function seedCover(Community|Course $target, string $source, string $path, int $maxBytes): void
    {
        if ($target->cover_path !== null && $target->cover_path !== $path) {
            return; // Preserve manually uploaded covers, including demo-owner edits.
        }

        $image = is_file($source) ? @getimagesize($source) : false;
        if ($image === false || ($image['mime'] ?? null) !== 'image/jpeg'
            || $image[0] > 4096 || $image[1] > 4096 || filesize($source) > $maxBytes) {
            throw new LogicException('A valid bundled JPEG sample cover is required.');
        }

        $disk = Storage::disk('community_media');
        $existed = $disk->exists($path);
        if (! $existed && ! $disk->put($path, file_get_contents($source))) {
            throw new \RuntimeException('Sample cover storage failed.');
        }

        try {
            $target->forceFill(['cover_path' => $path])->saveOrFail();
        } catch (\Throwable $exception) {
            if (! $existed) {
                $disk->delete($path);
            }
            throw $exception;
        }
    }
}
