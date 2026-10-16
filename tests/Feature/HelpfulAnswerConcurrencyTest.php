<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\HelpfulAnswerAction;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\User;
use App\Services\CommunityContributionService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class HelpfulAnswerConcurrencyTest extends TestCase
{
    // Independent PHP processes must see committed fixtures, not RefreshDatabase transactions.
    use DatabaseMigrations;

    public function test_duplicate_concurrent_acceptance_creates_one_action_and_one_signal(): void
    {
        $this->compete('duplicate');
    }

    public function test_competing_answers_leave_one_current_answer_and_an_atomic_replacement_audit(): void
    {
        $this->compete('replacement');
    }

    public function test_waiting_acceptance_rejects_author_membership_revoked_after_preflight(): void
    {
        $this->compete('revocation');
    }

    private function compete(string $mode): void
    {
        $creator = User::factory()->create();
        $community = Community::factory()->create(['creator_id' => $creator->id, 'status' => 'ACTIVE']);
        $authors = User::factory()->count(2)->create();
        foreach ($authors as $author) {
            CommunityMembership::factory()->active()->create(['community_id' => $community->id, 'user_id' => $author->id]);
        }
        $course = Course::factory()->included()->published()->create(['community_id' => $community->id]);
        $section = CourseSection::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->published()->create(['course_section_id' => $section->id]);
        $post = Post::factory()->create([
            'community_id' => $community->id, 'user_id' => $creator->id,
            'course_id' => $course->id, 'lesson_id' => $lesson->id,
        ]);
        $comments = $authors->map(fn (User $author) => Comment::factory()->create(['post_id' => $post->id, 'user_id' => $author->id]));
        $connection = DB::connection();
        $this->assertSame('mysql', $connection->getConfig('driver'));
        $this->assertSame('scool_test', $connection->getDatabaseName());
        $this->assertSame('scool_test', $connection->selectOne('SELECT DATABASE() AS name')->name);

        $directory = sys_get_temp_dir().'/scool-helpful-'.Str::uuid();
        mkdir($directory, 0700);
        $environment = [
            'APP_ENV' => 'testing', 'DB_URL' => '', 'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $connection->getConfig('host'), 'DB_PORT' => (string) $connection->getConfig('port'),
            'DB_DATABASE' => 'scool_test', 'DB_USERNAME' => $connection->getConfig('username'),
            'DB_PASSWORD' => $connection->getConfig('password'), 'CACHE_STORE' => 'array',
            'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array',
        ];
        $writers = $mode === 'revocation' ? 1 : 2;
        $processes = [];
        try {
            for ($i = 0; $i < $writers; $i++) {
                $comment = $comments[$mode === 'replacement' ? $i : 0];
                $process = new Process([
                    PHP_BINARY, base_path('tests/Support/helpful-answer-starter.php'),
                    (string) $community->id, (string) $creator->id, (string) $post->id,
                    (string) $comment->id, $directory, (string) $i,
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
                $this->assertTrue($process->isRunning(), 'Writer must still be waiting at the held parent lock.');
            }
            if ($mode === 'revocation') {
                CommunityMembership::query()->where('community_id', $community->id)->where('user_id', $authors[0]->id)
                    ->update(['status' => 'SUSPENDED']);
            }
            $connection->commit();

            foreach ($processes as $process) {
                $this->assertSame(0, $process->wait(), 'Helpful-answer runner failed: '.$process->getErrorOutput());
                $result = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
                $this->assertSame($mode === 'revocation' ? 404 : 200, $result['status']);
            }

            $post->refresh();
            $contributions = app(CommunityContributionService::class);
            $totalSignals = $authors->sum(fn (User $author) => $contributions->getHelpfulAnswersCount($community, $author));
            if ($mode === 'revocation') {
                $this->assertNull($post->accepted_comment_id);
                $this->assertSame(0, $totalSignals);
                $this->assertDatabaseCount('helpful_answer_actions', 0);
                $this->assertSame(0, $post->comments()->where('is_helpful', true)->count());

                return;
            }

            $this->assertContains($post->accepted_comment_id, $comments->modelKeys());
            $this->assertSame(1, $totalSignals);
            $this->assertSame(1, $post->comments()->where('is_helpful', true)->count());
            $this->assertTrue($post->comments()->findOrFail($post->accepted_comment_id)->is_helpful);
            $actions = HelpfulAnswerAction::query()->where('post_id', $post->id)->orderBy('id')->get();
            $this->assertSame($mode === 'duplicate'
                ? [HelpfulAnswerAction::ACTION_ACCEPTED]
                : [HelpfulAnswerAction::ACTION_ACCEPTED, HelpfulAnswerAction::ACTION_REPLACED, HelpfulAnswerAction::ACTION_ACCEPTED],
                $actions->pluck('action')->all());
            $this->assertSame($post->accepted_comment_id, $actions->last()->comment_id);
            if ($mode === 'replacement') {
                $this->assertSame($actions[0]->comment_id, $actions[1]->comment_id);
                $this->assertNotSame($actions[0]->comment_id, $actions[2]->comment_id);
                $this->assertFalse($post->comments()->findOrFail($actions[0]->comment_id)->is_helpful);
            }
        } finally {
            while ($connection->transactionLevel() > 0) {
                $connection->rollBack();
            }
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop(1);
                }
            }
            // Remove only known barrier files in this uniquely created directory.
            $barriers = ['release'];
            for ($i = 0; $i < $writers; $i++) {
                $barriers[] = $i.'.ready';
                $barriers[] = $i.'.attempting';
            }
            foreach ($barriers as $barrier) {
                if (is_file($directory.'/'.$barrier)) {
                    unlink($directory.'/'.$barrier);
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
                    $this->fail('Helpful-answer runner exited before barrier: '.$process->getErrorOutput());
                }
                if (microtime(true) >= $deadline) {
                    $this->fail('Helpful-answer barrier timed out: '.$signal);
                }
                usleep(10000);
            }
        }
    }
}
