<?php

// Independent CLI request runner used only by guarded MySQL concurrency tests.
use App\Http\Controllers\LessonProgressController;
use App\Models\Community;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$connection = DB::connection();
if (! $app->environment('testing') || $connection->getConfig('driver') !== 'mysql' || $connection->getDatabaseName() !== 'scool_test') {
    fwrite(STDERR, "SAFETY GUARD: Writer requires testing and MySQL scool_test.\n");
    exit(2);
}
[$script, $userId, $communityId, $courseId, $lessonId, $directory, $tag] = $argv;
$user = User::findOrFail($userId);
$community = Community::findOrFail($communityId);
$course = Course::findOrFail($courseId);
$lesson = Lesson::with('section.course.community')->findOrFail($lessonId);
Auth::setUser($user);
$request = Request::create('/progress', 'POST', ['completed' => true]);
$request->headers->set('Accept', 'application/json');
$request->setUserResolver(fn () => $user);
$connection->beforeExecuting(function ($query) use ($directory, $tag): void {
    if (str_contains($query, '`communities`') && str_contains($query, 'for update')) {
        touch($directory.'/'.$tag.'.attempting');
    }
});
touch($directory.'/'.$tag.'.ready');
$deadline = microtime(true) + 20;
while (! is_file($directory.'/release')) {
    if (microtime(true) >= $deadline) {
        fwrite(STDERR, "Barrier timeout.\n");
        exit(3);
    }
    usleep(10000);
}
try {
    $response = app(LessonProgressController::class)->update($request, $community, $course, $lesson);
    echo json_encode(['status' => $response->getStatusCode(), 'payload' => $response->getData(true)]);
} catch (AuthorizationException $exception) {
    echo json_encode(['status' => $exception->status() ?? 403]);
} catch (HttpExceptionInterface $exception) {
    echo json_encode(['status' => $exception->getStatusCode()]);
} catch (Throwable $exception) {
    // No trace or connection credentials in subprocess diagnostics.
    fwrite(STDERR, get_class($exception)."\n");
    exit(1);
}
