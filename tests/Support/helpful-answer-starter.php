<?php

// Independent request runner; production/development databases are never allowed.
use App\Http\Controllers\HelpfulAnswerController;
use App\Models\Comment;
use App\Models\Community;
use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$connection = DB::connection();
if (! $app->environment('testing') || $connection->getConfig('driver') !== 'mysql'
    || $connection->getDatabaseName() !== 'scool_test'
    || $connection->selectOne('SELECT DATABASE() AS name')->name !== 'scool_test') {
    fwrite(STDERR, "SAFETY GUARD: Helpful-answer runner requires testing/MySQL scool_test.\n");
    exit(2);
}
[$script, $communityId, $actorId, $postId, $commentId, $directory, $tag] = $argv;
$actor = User::findOrFail($actorId);
$community = Community::findOrFail($communityId);
$post = Post::with('community', 'course', 'lesson.section')->findOrFail($postId);
$comment = Comment::with('author')->findOrFail($commentId);
Auth::setUser($actor);
$request = Request::create('/helpful-answer', 'POST');
$request->headers->set('Accept', 'application/json');
$request->setUserResolver(fn () => $actor);
$connection->beforeExecuting(function (string $query) use ($directory, $tag): void {
    if (str_contains($query, '`communities`') && str_contains($query, 'for update')) {
        touch($directory.'/'.$tag.'.attempting');
    }
});
touch($directory.'/'.$tag.'.ready');
$deadline = microtime(true) + 20;
while (! is_file($directory.'/release')) {
    if (microtime(true) >= $deadline) {
        fwrite(STDERR, "Helpful-answer release barrier timed out.\n");
        exit(3);
    }
    usleep(10000);
}
try {
    $response = app(HelpfulAnswerController::class)->store($request, $community, $post, $comment);
    echo json_encode(['status' => $response->getStatusCode()]);
} catch (HttpExceptionInterface $exception) {
    echo json_encode(['status' => $exception->getStatusCode()]);
} catch (Throwable $exception) {
    // Do not print a trace, connection settings or exception messages.
    fwrite(STDERR, get_class($exception)."\n");
    exit(1);
}
