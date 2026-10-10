<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class LessonProgressConcurrencyTest extends TestCase
{
    // Committed fixtures are visible to independent processes, unlike RefreshDatabase transactions.
    use DatabaseMigrations;

    public function test_competing_first_writes_are_serialized_without_duplicates_or_500(): void
    {
        $this->compete(null, 2, 200);
    }

    public function test_waiting_writer_rejects_unpublish_committed_after_request_preflight(): void
    {
        $this->compete('lesson', 1, 404);
    }

    public function test_waiting_writer_rejects_membership_revocation_committed_after_request_preflight(): void
    {
        $this->compete('membership', 1, 404);
    }

    public function test_waiting_writer_rejects_course_unpublish_committed_after_request_preflight(): void
    {
        $this->compete('course', 1, 404);
    }

    public function test_waiting_writer_rejects_community_archive_committed_after_request_preflight(): void
    {
        $this->compete('community', 1, 404);
    }

    private function compete(?string $revoke, int $writers, int $expectedStatus): void
    {
        $community = Community::factory()->create(['status' => 'ACTIVE']);
        $membership = CommunityMembership::factory()->active()->create(['community_id' => $community->id]);
        $course = Course::factory()->create(['community_id' => $community->id, 'status' => 'PUBLISHED']);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['course_section_id' => $section->id, 'status' => 'PUBLISHED']);
        $directory = sys_get_temp_dir().'/scool-progress-'.Str::uuid();
        mkdir($directory, 0700);
        $connection = DB::connection();
        $this->assertSame('mysql', $connection->getConfig('driver'));
        $this->assertSame('scool_test', $connection->getDatabaseName());
        $environment = [
            'APP_ENV' => 'testing', 'DB_URL' => '', 'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $connection->getConfig('host'), 'DB_PORT' => (string) $connection->getConfig('port'),
            'DB_DATABASE' => 'scool_test', 'DB_USERNAME' => $connection->getConfig('username'),
            'DB_PASSWORD' => $connection->getConfig('password'), 'CACHE_STORE' => 'array',
            'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array',
        ];
        $processes = [];
        try {
            for ($i = 0; $i < $writers; $i++) {
                $process = new Process([
                    PHP_BINARY, base_path('tests/Support/lesson-progress-writer.php'),
                    (string) $membership->user_id, (string) $community->id, (string) $course->id,
                    (string) $lesson->id, $directory, (string) $i,
                ], base_path(), $environment, null, 30);
                $process->start();
                $processes[] = $process;
            }
            $this->awaitSignals($directory, 'ready', $processes);
            $connection->beginTransaction();
            Community::query()->lockForUpdate()->findOrFail($community->id);
            touch($directory.'/release');
            $this->awaitSignals($directory, 'attempting', $processes);
            foreach ($processes as $process) {
                $this->assertTrue($process->isRunning(), 'Writer should be waiting on the held community lock.');
            }
            if ($revoke === 'lesson') {
                $lesson->update(['status' => 'DRAFT']);
            } elseif ($revoke === 'course') {
                $course->update(['status' => 'DRAFT']);
            } elseif ($revoke === 'community') {
                $community->forceFill(['status' => 'ARCHIVED'])->save();
            } elseif ($revoke === 'membership') {
                $membership->transitionTo('SUSPENDED');
            }
            $connection->commit();
            foreach ($processes as $process) {
                $process->wait();
                $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
                $result = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
                $this->assertSame($expectedStatus, $result['status']);
                if ($expectedStatus === 200) {
                    $this->assertTrue($result['payload']['completed']);
                    $this->assertSame(100, $result['payload']['course_progress_percentage']);
                }
            }
            $this->assertDatabaseCount('lesson_progresses', $expectedStatus === 200 ? 1 : 0);
            if ($expectedStatus === 200) {
                $this->assertTrue(LessonProgress::sole()->completed);
                $this->assertNotNull(LessonProgress::sole()->completed_at);
            }
        } finally {
            while ($connection->transactionLevel() > 0) {
                $connection->rollBack();
            }
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop();
                }
            }
            // Delete only this test's unique, explicitly created barrier files.
            foreach (['release', ...array_map(fn ($i) => $i.'.ready', range(0, $writers - 1)), ...array_map(fn ($i) => $i.'.attempting', range(0, $writers - 1))] as $file) {
                if (is_file($directory.'/'.$file)) {
                    unlink($directory.'/'.$file);
                }
            }
            rmdir($directory);
        }
    }

    private function awaitSignals(string $directory, string $signal, array $processes): void
    {
        $deadline = microtime(true) + 15;
        foreach ($processes as $i => $process) {
            while (! is_file($directory.'/'.$i.'.'.$signal)) {
                if (! $process->isRunning()) {
                    $this->fail('Writer exited before barrier: '.$process->getErrorOutput());
                }
                if (microtime(true) >= $deadline) {
                    $this->fail('Writer barrier timed out.');
                }
                usleep(10000);
            }
        }
    }
}
