<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\Course;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SkoolMediaSyncSeeder extends Seeder
{
    public function run(): void
    {
        $disk = Storage::disk('community_media');

        // Assets directory
        $commAssetsDir = database_path('seeders/assets/community-covers');
        $courseAssetsDir = database_path('seeders/assets/course-covers');
        if (! is_dir($commAssetsDir)) {
            mkdir($commAssetsDir, 0755, true);
        }
        if (! is_dir($courseAssetsDir)) {
            mkdir($courseAssetsDir, 0755, true);
        }

        $items = [
            [
                'slug' => 'python-developers',
                'comm_url' => 'https://assets.skool.com/f/bc0ed33173ce43e8b01ed1177f824ee9/dd34c72a727f4764a09543ad70c6be85c6f7df8261494dac8c78d41b8cf9105d-md.jpg',
                'course_title' => 'Python Zero to Production',
                'course_url' => 'https://assets.skool.com/f/bc0ed33173ce43e8b01ed1177f824ee9/d47926a44ecb4aea84f1ead852ceb6dbe8d72f50a7014416a9c24da77c949a2c',
            ],
            [
                'slug' => 'ai-video-bootcamp',
                'comm_url' => 'https://assets.skool.com/f/1a18982c15714b0c97031894dbdb3900/b8b1878300654c3da413bd6a7dc17372bbb442197c2d49dc93504dd94d258fa4-md.jpg',
                'course_title' => 'AI Cinematic Filmmaking Masterclass',
                'course_url' => 'https://assets.skool.com/f/1a18982c15714b0c97031894dbdb3900/8c7712a783ff4d40bb8659aac5b03a529f406294777b4502afa85a0a7a6fa00c.jpg',
            ],
            [
                'slug' => 'maker-school-ai-automation',
                'comm_url' => 'https://assets.skool.com/f/e256cd9ef1ac4dbe9197634db46e9e3b/56cb44fa92ce4a2a950a286352392ff96e45d3d056e740e4a30c12dbb52faad4-md.jpg',
                'course_title' => 'Enterprise AI Automations Blueprint',
                'course_url' => 'https://assets.skool.com/f/e256cd9ef1ac4dbe9197634db46e9e3b/7a0881d755cd45d69679b78d418b803a475d563a4bc4476ea3dfc1ad682b0551.jpg',
            ],
            [
                'slug' => 'agentic-ai-founders',
                'comm_url' => 'https://assets.skool.com/f/be9ac25c58f14ae1a4a93519ccf67319/739ecf918ff445499d85d265e754961fc7c073c31dae4d13acb9f05a9e4516dd-md.jpg',
                'course_title' => 'Autonomous Agent Systems at Scale',
                'course_url' => 'https://assets.skool.com/f/be9ac25c58f14ae1a4a93519ccf67319/b112225121dc41a2bae9e12bf0bae52fb51244b84aae4678b3276268e2bdbe61.jpg',
            ],
            [
                'slug' => 'tattoo-masterclass',
                'comm_url' => 'https://assets.skool.com/f/51bfeba6acd545fcab2e789b178a1e26/4eb1ff5661a74adc83c414706b37afcc634cef6e51f54483a2074d72e44a45e8-md.jpg',
                'course_title' => 'Precision Tattooing: Needle Theory & Skin Dynamics',
                'course_url' => 'https://assets.skool.com/f/51bfeba6acd545fcab2e789b178a1e26/cb00ce3805ac41729ed4fa23f13f7c8a464e81ac03da42a0a1af2f5a27d7e6fb.jpg',
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
            $community = Community::query()->where('slug', $item['slug'])->first();
            if (! $community) {
                continue;
            }

            // 1. Sync Community Cover if specified
            if ($item['comm_url'] !== null) {
                $commLocalPath = $commAssetsDir.'/'.$item['slug'].'.jpg';
                if (! file_exists($commLocalPath)) {
                    $content = @file_get_contents($item['comm_url']);
                    if ($content !== false) {
                        file_put_contents($commLocalPath, $content);
                    }
                }

                if (file_exists($commLocalPath)) {
                    $raw = file_get_contents($commLocalPath);
                    $storagePath = $community->id.'/'.sha1('scool-comm-cover:'.$community->slug).'.jpg';
                    $disk->put($storagePath, $raw);
                    $absPath = $disk->path($storagePath);
                    @chmod(dirname($absPath), 0755);
                    @chmod($absPath, 0644);
                    $community->forceFill(['cover_path' => $storagePath])->save();
                    echo "Updated cover for community: {$community->name} ({$storagePath})\n";
                }
            }

            // 2. Sync Course Cover
            if ($item['course_url'] !== null) {
                $course = $community->courses()->where('title', $item['course_title'])->first();
                if ($course) {
                    $courseLocalPath = $courseAssetsDir.'/'.sha1($item['course_title']).'.jpg';
                    if (! file_exists($courseLocalPath)) {
                        $content = @file_get_contents($item['course_url']);
                        if ($content !== false) {
                            file_put_contents($courseLocalPath, $content);
                        }
                    }

                    if (file_exists($courseLocalPath)) {
                        $raw = file_get_contents($courseLocalPath);
                        $courseStoragePath = $community->id.'/courses/'.sha1('scool-course-cover:'.$course->id).'.jpg';
                        $disk->put($courseStoragePath, $raw);
                        $absPath = $disk->path($courseStoragePath);
                        @chmod(dirname($absPath), 0755);
                        @chmod($absPath, 0644);
                        $course->forceFill(['cover_path' => $courseStoragePath])->save();
                        echo "Updated cover for course: {$course->title} ({$courseStoragePath})\n";
                    }
                }
            }
        }
    }
}
