<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Event;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use LogicException;

class LearningCommunitySeeder extends Seeder
{
    public function run(bool $preserveExisting = false): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new LogicException('Learning community content may only be seeded in local or testing environments.');
        }

        $creator = User::query()->where('email', 'creator@scool.local')->firstOrFail();
        $member = User::query()->where('email', 'member@scool.local')->firstOrFail();

        foreach ($this->catalog() as $spec) {
            DB::transaction(function () use ($creator, $member, $spec, $preserveExisting): void {
                if ($preserveExisting) {
                    $existing = Community::query()->where('slug', $spec['slug'])->lockForUpdate()->first();
                    if ($existing !== null) {
                        if ((int) $existing->creator_id !== (int) $creator->id) {
                            throw new LogicException("Learning community slug '{$spec['slug']}' is already owned by another user.");
                        }

                        return; // Sync must retain edited content and access states.
                    }
                }

                $community = $this->community($creator, $spec);

                PostCategory::initializeDefaultsFor($community);
                $this->activeMembership($community, $member);
                $this->course($community, $spec);
                $this->posts($community, $creator, $spec);
                $this->event($community, $creator, $spec);
                $this->cover($community, $spec['cover']);

                if ($community->slug === 'networking-learning') {
                    $this->removeEmptyNetworkingPlaceholders($community);
                }
            }, 3);
        }
    }

    /** @param array<string, mixed> $spec */
    private function community(User $creator, array $spec): Community
    {
        $community = Community::query()->where('slug', $spec['slug'])->first();

        if ($community !== null && (int) $community->creator_id !== (int) $creator->id) {
            throw new LogicException("Learning community slug '{$spec['slug']}' is already owned by another user.");
        }

        $community ??= new Community;
        $community->forceFill([
            'creator_id' => $creator->id,
            'name' => $spec['name'],
            'slug' => $spec['slug'],
            'description' => $spec['description'],
            'rules' => $spec['rules'],
            'onboarding_enabled' => true,
            'visibility' => 'PRIVATE',
            'access_mode' => 'FREE',
            'price_vnd' => null,
            'billing_period' => 'ONE_TIME',
            'referral_commission_rate' => 0,
            'status' => 'ACTIVE',
        ])->save();

        return $community;
    }

    private function activeMembership(Community $community, User $member): void
    {
        $membership = CommunityMembership::query()->firstOrNew([
            'community_id' => $community->id,
            'user_id' => $member->id,
        ]);
        $membership->forceFill([
            'status' => 'ACTIVE',
            'access_kind' => CommunityMembership::ACCESS_LIFETIME,
            'expires_at' => null,
        ])->save();
    }

    /** @param array<string, mixed> $spec */
    private function course(Community $community, array $spec): void
    {
        $courseSpec = $spec['course'];
        $course = $community->courses()->where('title', $courseSpec['title'])->first();

        if ($course === null && isset($courseSpec['legacy_title'])) {
            $course = $community->courses()->where('title', $courseSpec['legacy_title'])->first();
        }

        $course ??= new Course;
        $course->community_id = $community->id;
        $course->forceFill([
            'title' => $courseSpec['title'],
            'description' => $courseSpec['description'],
            'access_mode' => Course::ACCESS_MODE_INCLUDED,
            'price' => null,
            'status' => 'PUBLISHED',
            'order' => 1,
            'published_revision' => max(1, (int) $course->published_revision),
        ])->save();

        foreach ($courseSpec['sections'] as $sectionOrder => $sectionSpec) {
            $section = $course->sections()->where('title', $sectionSpec['title'])->first();

            if ($section === null && $sectionOrder === 0 && isset($courseSpec['legacy_title'])) {
                $section = $course->sections()->orderBy('order')->orderBy('id')->first();
            }

            $section ??= new CourseSection;
            $section->course_id = $course->id;
            $section->forceFill([
                'title' => $sectionSpec['title'],
                'order' => $sectionOrder + 1,
            ])->save();

            foreach ($sectionSpec['lessons'] as $lessonOrder => $lessonSpec) {
                $lesson = $section->lessons()->where('title', $lessonSpec['title'])->first();

                if ($lesson === null && $sectionOrder === 0 && $lessonOrder === 0 && isset($courseSpec['legacy_title'])) {
                    $lesson = $section->lessons()->orderBy('order')->orderBy('id')->first();
                }

                $lesson ??= new Lesson;
                $lesson->course_section_id = $section->id;
                $lesson->forceFill([
                    'title' => $lessonSpec['title'],
                    'content' => $this->lessonContent(...$lessonSpec['content']),
                    'video_url' => null,
                    'status' => 'PUBLISHED',
                    'order' => $lessonOrder + 1,
                ])->save();
            }
        }
    }

    /** @param array<string, mixed> $spec */
    private function posts(Community $community, User $creator, array $spec): void
    {
        $categories = $community->categories()->get()->keyBy('slug');
        $posts = [
            [
                'title' => "Start here: {$spec['name']} learning roadmap",
                'body' => "Welcome to {$spec['name']}. Follow the published course in order, complete each practice task, and share evidence instead of only reporting that a lesson was read.\n\n{$spec['roadmap_summary']}\n\nUse the Questions category when blocked and include what you expected, what happened, and the evidence you collected.",
                'category' => 'announcements',
                'pinned_at' => CarbonImmutable::parse('2026-10-15 00:00:00', 'UTC'),
                'legacy_title' => $spec['legacy_post_title'] ?? null,
            ],
            [
                'title' => "Weekly lab: {$spec['weekly_lab_title']}",
                'body' => "This week's practice is {$spec['weekly_lab_body']}\n\nPost your diagram, commands or evaluation notes, then explain one failure you found and how you verified the fix. Ask for a review if your evidence does not match your prediction.",
                'category' => 'questions',
                'pinned_at' => null,
            ],
            [
                'title' => "Introduce yourself to {$spec['name']}",
                'body' => 'Tell the community what you can build today, what outcome you want from this roadmap, and the environment you will use for labs. Add one topic where you can help another learner.',
                'category' => 'introductions',
                'pinned_at' => null,
            ],
        ];

        foreach ($posts as $postSpec) {
            $post = Post::query()
                ->where('community_id', $community->id)
                ->where('user_id', $creator->id)
                ->where('title', $postSpec['title'])
                ->first();

            if ($post === null && ($postSpec['legacy_title'] ?? null) !== null) {
                $post = Post::query()
                    ->where('community_id', $community->id)
                    ->where('user_id', $creator->id)
                    ->where('title', $postSpec['legacy_title'])
                    ->first();
            }

            $post ??= new Post;
            $post->forceFill([
                'community_id' => $community->id,
                'category_id' => $categories[$postSpec['category']]->id,
                'user_id' => $creator->id,
                'title' => $postSpec['title'],
                'body' => $postSpec['body'],
                'pinned_at' => $postSpec['pinned_at'],
            ])->save();
        }
    }

    /** @param array<string, mixed> $spec */
    private function event(Community $community, User $creator, array $spec): void
    {
        $eventSpec = $spec['event'];
        $event = Event::query()
            ->where('community_id', $community->id)
            ->where('creator_id', $creator->id)
            ->where('title', $eventSpec['title'])
            ->first();

        if ($event === null && isset($eventSpec['legacy_title'])) {
            $event = Event::query()
                ->where('community_id', $community->id)
                ->where('creator_id', $creator->id)
                ->where('title', $eventSpec['legacy_title'])
                ->first();
        }

        $event ??= new Event;
        $event->forceFill([
            'community_id' => $community->id,
            'creator_id' => $creator->id,
            'title' => $eventSpec['title'],
            'description' => $eventSpec['description'],
            'starts_at' => CarbonImmutable::parse($eventSpec['starts_at'], 'UTC'),
            'ends_at' => CarbonImmutable::parse($eventSpec['ends_at'], 'UTC'),
            'timezone' => 'Asia/Ho_Chi_Minh',
            'meeting_url' => "https://meet.example.test/scool/{$community->slug}",
            'status' => 'SCHEDULED',
        ])->save();
    }

    private function cover(Community $community, string $filename): void
    {
        $source = database_path("seeders/assets/community-covers/{$filename}");

        if (! is_file($source)) {
            throw new LogicException("Missing learning community cover asset: {$source}");
        }

        $disk = Storage::disk('community_media');

        // General feature tests may invoke DatabaseSeeder without faking this private disk.
        // Never let an isolated scool_test run write fixtures into the local development media root.
        if (app()->environment('testing')
            && rtrim($disk->path(''), DIRECTORY_SEPARATOR) === rtrim(storage_path('app/community-media'), DIRECTORY_SEPARATOR)) {
            $community->forceFill(['cover_path' => null])->save();

            return;
        }

        $path = $community->id.'/'.sha1("scool-learning-cover:{$community->slug}").'.png';
        $disk->put($path, file_get_contents($source));

        // Seed commands often run as root in Docker while HTTP runs as www-data.
        // The file remains outside the public web root and is served only by the authorized cover route.
        $absolutePath = $disk->path($path);
        if (! @chmod(dirname($absolutePath), 0755) || ! @chmod($absolutePath, 0644)) {
            throw new LogicException("Unable to make the learning community cover readable: {$path}");
        }

        $community->forceFill(['cover_path' => $path])->save();
    }

    private function removeEmptyNetworkingPlaceholders(Community $community): void
    {
        $community->courses()
            ->where('title', 'test course')
            ->where('status', 'DRAFT')
            ->doesntHave('sections')
            ->doesntHave('payments')
            ->doesntHave('entitlements')
            ->get()
            ->each->delete();
    }

    private function lessonContent(string $objective, string $mentalModel, string $practice, string $evidence): string
    {
        return "LEARNING OBJECTIVE\n{$objective}\n\nCORE MENTAL MODEL\n{$mentalModel}\n\nHANDS-ON PRACTICE\n{$practice}\n\nEVIDENCE OF COMPLETION\n{$evidence}\n\nREFLECTION\nWrite down your prediction before the lab. After the lab, compare it with the observed result and explain one change you would make in a production environment.";
    }

    /** @return array<int, array<string, mixed>> */
    private function catalog(): array
    {
        return [
            $this->networkingCatalog(),
            $this->cloudCatalog(),
            $this->dockerCatalog(),
            $this->aiCatalog(),
        ];
    }

    /** @return array<string, mixed> */
    private function networkingCatalog(): array
    {
        return [
            'slug' => 'networking-learning',
            'name' => 'Networking Learning',
            'description' => 'A practical community for building strong networking fundamentals through diagrams, packet-level observation, small labs, and a repeatable troubleshooting method.',
            'rules' => "1. Share sanitized diagrams and command output; never post real credentials or private addresses from work.\n2. State your prediction before asking for help.\n3. Explain the layer, symptom, and evidence when troubleshooting.\n4. Give constructive feedback and credit the source of reused material.",
            'cover' => 'networking-learning.png',
            'roadmap_summary' => 'Roadmap: understand packet flow, build a safe lab, master switching and subnetting, then operate routing, DNS, DHCP, and layered troubleshooting.',
            'weekly_lab_title' => 'trace one request from client to service',
            'weekly_lab_body' => 'to draw every hop for a DNS lookup followed by an HTTPS request, then confirm the route with packet capture or system networking tools.',
            'course' => [
                'legacy_title' => 'PR17 Review',
                'title' => 'Networking Foundations: From Packet to Production',
                'description' => 'A six-lesson path from network mental models to a repeatable troubleshooting workflow, with evidence from diagrams, subnet calculations, and packet observations.',
                'sections' => [
                    ['title' => 'Mental Models and Lab', 'lessons' => [
                        ['title' => 'How Data Moves Through a Network', 'content' => ['Explain encapsulation and the roles of links, switches, routers, and applications.', 'Data moves as application data wrapped into transport segments, network packets, and link frames. Each device makes a decision at its own layer.', 'Draw the path of a browser request across two networks. Label MAC addresses, IP addresses, ports, and where each value changes.', 'Submit the annotated path and identify the first device that can route the packet.']],
                        ['title' => 'Build a Safe Practice Lab', 'content' => ['Create an isolated lab where network failures can be reproduced without affecting real users.', 'A useful lab has known endpoints, an explicit topology, observable traffic, and a reset path. Isolation matters more than size.', 'Build two client nodes and one service using containers or virtual machines. Record addresses and prove baseline reachability.', 'Provide the topology, address plan, and successful connectivity checks before introducing a fault.']],
                    ]],
                    ['title' => 'Local Networks', 'lessons' => [
                        ['title' => 'Ethernet, Switching and VLANs', 'content' => ['Predict how a switch learns and forwards frames inside and across VLAN boundaries.', 'Switches learn source MAC addresses per VLAN and forward by destination MAC. VLANs create separate broadcast domains.', 'Observe ARP and MAC learning, then separate two hosts into different VLANs and explain why routing becomes necessary.', 'Capture the ARP exchange and a MAC table snapshot with a short explanation of the forwarding decision.']],
                        ['title' => 'IPv4 Addressing and Subnetting', 'content' => ['Turn a host requirement into a correct subnet and usable address range.', 'A prefix divides network bits from host bits. Subnet design balances address capacity, broadcast boundaries, and future growth.', 'Calculate /24, /26, and /28 ranges, then create an address plan for three teams without overlap.', 'Provide network, broadcast, first and last usable addresses plus a check using a subnet tool.']],
                    ]],
                    ['title' => 'Services and Troubleshooting', 'lessons' => [
                        ['title' => 'Routing, DNS and DHCP', 'content' => ['Explain how addressing, name resolution, and routing cooperate before an application can connect.', 'DHCP supplies configuration, DNS maps names to addresses, and routers select a next hop. A failure in any stage can look like an application outage.', 'Trace a new client from DHCP lease through DNS resolution to a routed service request and deliberately break one stage.', 'Submit the lease, resolver result, route decision, and the observed symptom of the injected failure.']],
                        ['title' => 'Troubleshoot with a Layered Method', 'content' => ['Diagnose network faults from evidence instead of changing multiple settings at once.', 'Start at scope and symptoms, form one hypothesis, test the lowest relevant layer, and move upward only when evidence supports it.', 'Repair three faults: link unavailable, wrong gateway, and failed DNS. Use one command or capture to prove each root cause.', 'Provide a timeline of hypothesis, test, result, fix, and independent verification for every fault.']],
                    ]],
                ],
            ],
            'event' => [
                'legacy_title' => 'Buổi kiểm tra',
                'title' => 'Networking Troubleshooting Lab',
                'description' => 'A guided incident lab: isolate a broken client-to-service path, explain the failing layer, apply one fix, and verify recovery.',
                'starts_at' => '2030-02-09 02:00:00',
                'ends_at' => '2030-02-09 03:30:00',
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function cloudCatalog(): array
    {
        return [
            'slug' => 'cloud-master',
            'name' => 'Cloud Master',
            'description' => 'A hands-on cloud architecture community focused on AWS foundations, security, reliability, observability, and cost-aware design decisions.',
            'rules' => "1. Remove account IDs, credentials, and customer data from every screenshot.\n2. Prefer architecture decisions with stated constraints and trade-offs.\n3. Include cleanup steps and estimated cost for each lab.\n4. Review designs against security, reliability, performance, cost, and operations.",
            'cover' => 'cloud-master.png',
            'roadmap_summary' => 'Roadmap: establish the cloud mental model, build a secure account and network foundation, design reliable workloads, then add observability, cost controls, and Well-Architected review.',
            'weekly_lab_title' => 'review a resilient web workload',
            'weekly_lab_body' => 'to diagram a two-AZ web service, define failure boundaries, add health signals, and estimate the main cost drivers.',
            'legacy_post_title' => 'test post creator',
            'course' => [
                'title' => 'AWS Cloud Foundations and Well-Architected Thinking',
                'description' => 'An eight-lesson architecture path covering shared responsibility, regional design, IAM, VPCs, compute, data, observability, cost, and structured review.',
                'sections' => [
                    ['title' => 'Cloud Mental Model', 'lessons' => [
                        ['title' => 'Shared Responsibility and Service Models', 'content' => ['Separate provider responsibilities from the controls a workload owner must implement.', 'Responsibility changes across infrastructure, managed platforms, and software services, but ownership of data and access never disappears.', 'Classify ten operational tasks across EC2, RDS, and a managed object store, then mark the accountable party.', 'Submit the responsibility matrix and explain two controls that remain yours in every service model.']],
                        ['title' => 'Regions, Availability Zones and Failure Boundaries', 'content' => ['Choose a deployment boundary that matches recovery and latency needs.', 'Regions isolate geography; availability zones isolate infrastructure failure. Resilience comes from independent copies and tested recovery paths.', 'Design a service across two availability zones and document what happens when one zone is unavailable.', 'Provide the diagram, recovery objective, dependency list, and a probe that proves the surviving path works.']],
                    ]],
                    ['title' => 'Secure Foundation', 'lessons' => [
                        ['title' => 'IAM and Least Privilege', 'content' => ['Design identities and permissions that grant only the actions a role needs.', 'Authentication proves identity; authorization limits actions. Short-lived role credentials reduce the impact of leaked secrets.', 'Replace an administrator-style policy with task-specific permissions for an application and an operator.', 'Submit the policy rationale plus one allowed and one denied action from a policy simulation or sandbox test.']],
                        ['title' => 'VPC Design: Public and Private Paths', 'content' => ['Design subnets, routes, and controls for internet-facing and internal components.', 'A public subnet has a route to an internet gateway; workload exposure also depends on addresses and security controls. Private workloads use explicit egress paths.', 'Create a two-AZ address plan with public ingress and private application and data tiers.', 'Provide CIDR calculations, route intent, and a flow explanation from user to database and back.']],
                    ]],
                    ['title' => 'Reliable Workloads', 'lessons' => [
                        ['title' => 'Compute, Load Balancing and Health', 'content' => ['Connect scaling and load balancing decisions to measurable health signals.', 'A load balancer distributes only to healthy targets; scaling changes capacity from observed demand while deployment strategy limits blast radius.', 'Define health, scaling, and deployment rules for a stateless API with two failure scenarios.', 'Submit thresholds, expected routing behavior, and the probe used to confirm healthy service.']],
                        ['title' => 'Storage and Database Trade-offs', 'content' => ['Choose storage and database services from access pattern, consistency, durability, and recovery needs.', 'Object, block, file, relational, and key-value systems optimize different constraints. Recovery design includes backup, restore, and verification.', 'Compare two data designs for user uploads and transactional metadata, then rehearse a restore in a sandbox.', 'Provide the decision table, recovery objective, restore result, and integrity check.']],
                    ]],
                    ['title' => 'Operate and Improve', 'lessons' => [
                        ['title' => 'Observability and Cost Controls', 'content' => ['Define signals that reveal user impact while keeping resource cost visible.', 'Metrics show trends, logs explain events, and traces connect distributed work. Cost is another operational signal tied to usage and ownership.', 'Create a small dashboard and budget plan for latency, errors, saturation, and daily spend.', 'Submit alert thresholds, owner, runbook link, and one test alert with its cost assumption.']],
                        ['title' => 'Run a Well-Architected Review', 'content' => ['Turn architecture risks into prioritized, testable improvements.', 'A review is a conversation about evidence and trade-offs across operational excellence, security, reliability, performance, cost, and sustainability.', 'Review the weekly workload, rank three risks by likelihood and impact, and define one measurable remediation for each.', 'Provide the risk register, chosen priorities, owners, and acceptance evidence for the highest-risk improvement.']],
                    ]],
                ],
            ],
            'event' => [
                'title' => 'Cloud Architecture Review Clinic',
                'description' => 'Bring one workload diagram and receive a structured review of failure boundaries, identity, observability, recovery, and cost.',
                'starts_at' => '2030-02-16 02:00:00',
                'ends_at' => '2030-02-16 03:30:00',
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function dockerCatalog(): array
    {
        return [
            'slug' => 'docker-engineering',
            'name' => 'Docker Engineering',
            'description' => 'A project-based container community covering images, Dockerfiles, Compose, networking, persistence, security, delivery, and production troubleshooting.',
            'rules' => "1. Share minimal reproducible Dockerfiles and Compose files.\n2. Never publish secrets in images, logs, or environment examples.\n3. Pin meaningful versions and explain image provenance.\n4. Include build, run, health, and cleanup commands with every lab.",
            'cover' => 'docker-engineering.png',
            'roadmap_summary' => 'Roadmap: understand images and containers, build efficient Dockerfiles, compose local systems, then add health, security, registries, delivery, and troubleshooting.',
            'weekly_lab_title' => 'containerize a small web service',
            'weekly_lab_body' => 'to build a non-root image, connect it to a database with Compose, preserve data in a volume, and prove readiness with a health check.',
            'course' => [
                'title' => 'Docker Engineering: Containers to Production',
                'description' => 'An eight-lesson path from container fundamentals to secure images, multi-service development, delivery, and evidence-led troubleshooting.',
                'sections' => [
                    ['title' => 'Container Foundations', 'lessons' => [
                        ['title' => 'Images, Containers and Layers', 'content' => ['Explain the difference between an immutable image and a running container.', 'An image is a layered filesystem plus metadata; a container adds a writable layer and an isolated process. Data that must survive belongs outside that writable layer.', 'Inspect an image history, run two containers from it, change one container, and compare their state.', 'Submit image digest, layer summary, process list, and proof that the second container remains unchanged.']],
                        ['title' => 'Write an Efficient Dockerfile', 'content' => ['Build a reproducible image with a small attack surface and effective cache use.', 'Dockerfile order shapes build cache reuse. Multi-stage builds separate compilation tools from the runtime image.', 'Create a multi-stage build, add a precise ignore file, pin the base version, and compare image size and rebuild time.', 'Provide the Dockerfile, image digest, size comparison, and cached build output.']],
                    ]],
                    ['title' => 'Local Application Stacks', 'lessons' => [
                        ['title' => 'Volumes and Container Networking', 'content' => ['Choose the correct persistence and communication mechanism for a multi-container app.', 'Named volumes outlive containers; bind mounts expose host files. User-defined networks provide scoped DNS-based service discovery.', 'Connect an application to a database by service name, persist its data, remove the containers, and recreate them.', 'Submit network inspection, volume inspection, and proof that data survives container recreation.']],
                        ['title' => 'Compose a Reproducible Environment', 'content' => ['Describe a complete local stack as versioned configuration.', 'Compose defines services, networks, volumes, configuration, dependencies, and health, but startup order alone does not guarantee readiness.', 'Build an app, database, and worker stack with health checks and environment defaults that contain no secrets.', 'Provide the resolved Compose config, healthy service list, and one-command setup and teardown instructions.']],
                    ]],
                    ['title' => 'Reliability and Security', 'lessons' => [
                        ['title' => 'Health Checks, Signals and Graceful Shutdown', 'content' => ['Make container lifecycle behavior observable and safe during replacement.', 'PID 1 must receive signals and stop work cleanly. Readiness describes ability to serve; liveness describes whether recovery is required.', 'Add a health check and graceful termination handler, then stop the container during an active request.', 'Submit timestamps showing signal receipt, request completion or safe cancellation, and exit code.']],
                        ['title' => 'Run as Non-root and Scan the Supply Chain', 'content' => ['Reduce privileges and identify known risk before publishing an image.', 'A secure container combines trusted inputs, minimal contents, non-root runtime, controlled secrets, and continuous vulnerability review.', 'Switch the runtime user, remove build tools, scan the image, and document one accepted or remediated finding.', 'Provide user identity inside the container, scan summary, base image source, and remediation decision.']],
                    ]],
                    ['title' => 'Delivery and Operations', 'lessons' => [
                        ['title' => 'Registries, Tags and Immutable Releases', 'content' => ['Publish and identify an exact image without relying on a mutable tag.', 'Tags are convenient pointers; digests identify immutable content. Promotion should move a verified artifact between environments without rebuilding it.', 'Tag a release, record its digest, and verify the pulled artifact matches what passed tests.', 'Submit registry reference, digest comparison, and the rule used to prevent accidental latest-tag deployment.']],
                        ['title' => 'CI Delivery and Container Troubleshooting', 'content' => ['Design a delivery gate and diagnose common runtime failures from evidence.', 'A reliable pipeline builds once, tests the artifact, scans it, publishes by digest, and deploys with health verification and rollback criteria.', 'Draft a pipeline and debug one crash loop, one port mismatch, and one permission failure.', 'Provide the pipeline stages and, for each fault, the log or inspection evidence, fix, and independent health check.']],
                    ]],
                ],
            ],
            'event' => [
                'title' => 'Container Build and Debug Workshop',
                'description' => 'Containerize a service, reduce its image size, run it as non-root, and debug an injected startup failure using inspection evidence.',
                'starts_at' => '2030-02-23 02:00:00',
                'ends_at' => '2030-02-23 03:30:00',
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function aiCatalog(): array
    {
        return [
            'slug' => 'ai-engineer',
            'name' => 'AI Engineer',
            'description' => 'An applied AI engineering community for turning a well-defined problem and trustworthy data into an evaluated, deployed, monitored, and responsibly operated service.',
            'rules' => "1. Never share private datasets, credentials, or identifiable user records.\n2. Report baselines, dataset version, metrics, and known limitations.\n3. Separate measured results from assumptions and demos.\n4. Include human review, rollback, and monitoring for high-impact use cases.",
            'cover' => 'ai-engineer.png',
            'roadmap_summary' => 'Roadmap: frame the problem and data contract, establish a baseline, evaluate errors, expose a tested service, then monitor drift, privacy, bias, and human oversight.',
            'weekly_lab_title' => 'build an evidence-backed classification service',
            'weekly_lab_body' => 'to define an acceptance metric, train a baseline on versioned data, inspect failure cases, expose one inference endpoint, and monitor input and output quality.',
            'course' => [
                'title' => 'Applied AI Engineering: Data to Reliable Service',
                'description' => 'An eight-lesson path from problem framing and data quality to evaluation, service delivery, monitoring, privacy, bias, and human review.',
                'sections' => [
                    ['title' => 'Problem and Data', 'lessons' => [
                        ['title' => 'Define the Task, User and Success Metric', 'content' => ['Translate a product need into a measurable machine-learning task and explicit decision boundary.', 'A useful model starts with who acts on the output, what decision changes, the cost of errors, and a baseline that can be beaten.', 'Write a one-page task brief with user, input, output, latency, false-positive and false-negative costs, and acceptance metric.', 'Submit the task brief and explain why the chosen metric represents user value better than raw accuracy.']],
                        ['title' => 'Build a Data Contract and Prevent Leakage', 'content' => ['Define trustworthy inputs and create train, validation, and test splits that reflect deployment.', 'A data contract states schema, provenance, consent, quality, and freshness. Leakage occurs when training uses information unavailable at prediction time.', 'Audit a sample dataset, define validation rules, and split it by the unit and time boundary used in production.', 'Provide schema checks, split logic, class distribution, and one leakage risk you removed.']],
                    ]],
                    ['title' => 'Model and Evaluation', 'lessons' => [
                        ['title' => 'Establish a Reproducible Baseline', 'content' => ['Create the simplest credible model and record enough context to reproduce it.', 'A baseline tests whether the problem and data contain useful signal. Reproducibility requires versioned code, data reference, configuration, and random seed.', 'Train a simple baseline, compare it with a rule-based guess, and record the experiment inputs and result.', 'Submit the run configuration, dataset version, metric table, and exact command that reproduces the result.']],
                        ['title' => 'Use Error Analysis to Improve the System', 'content' => ['Find meaningful failure groups instead of tuning only an aggregate score.', 'Aggregate metrics hide uneven performance. Error slices connect model behavior to data quality, product rules, and affected users.', 'Review false positives and false negatives, group them by cause, and choose one change based on impact and frequency.', 'Provide a confusion matrix, three error slices, representative sanitized examples, and the next experiment hypothesis.']],
                    ]],
                    ['title' => 'Serve and Verify', 'lessons' => [
                        ['title' => 'Design a Stable Inference API', 'content' => ['Expose model inference through a bounded, versioned contract with predictable failure behavior.', 'The service contract owns input validation, preprocessing parity, timeouts, output schema, and traceable model version.', 'Create an endpoint specification with valid, invalid, and timeout cases plus a health endpoint separate from inference.', 'Submit request and response examples, error contract, latency target, and model-version field.']],
                        ['title' => 'Test and Version the Whole Pipeline', 'content' => ['Catch divergence across data preparation, model artifact, API behavior, and deployment.', 'Unit tests protect transforms, contract tests protect interfaces, and golden examples protect expected behavior. Versioning ties an output to its inputs.', 'Add transform tests, API contract tests, and five golden examples, then replay them against a packaged artifact.', 'Provide test results, model and data versions, artifact checksum, and the golden-example comparison.']],
                    ]],
                    ['title' => 'Responsible Operations', 'lessons' => [
                        ['title' => 'Monitor Quality, Drift and Service Health', 'content' => ['Detect when an AI service becomes unavailable, slow, or less trustworthy.', 'Operational signals cover latency and errors; data signals cover schema and drift; quality signals require delayed labels or reviewed samples.', 'Define a dashboard and response plan for latency, error rate, input drift, prediction distribution, and sampled quality.', 'Submit thresholds, owner, investigation query, rollback rule, and a simulated alert result.']],
                        ['title' => 'Privacy, Bias and Human Review', 'content' => ['Design safeguards proportional to data sensitivity and the impact of automated decisions.', 'Responsible operation combines data minimization, access controls, subgroup evaluation, transparent limitations, and a clear human escalation path.', 'Create a risk register, compare performance across relevant slices, and design a review queue for uncertain or high-impact predictions.', 'Provide the risk register, slice metrics, retention rule, review criteria, and one example where the human can override safely.']],
                    ]],
                ],
            ],
            'event' => [
                'title' => 'AI Evaluation and Reliability Clinic',
                'description' => 'Review a model card, inspect failure slices, define service-level signals, and design a human fallback for uncertain predictions.',
                'starts_at' => '2030-03-02 02:00:00',
                'ends_at' => '2030-03-02 03:30:00',
            ],
        ];
    }
}
