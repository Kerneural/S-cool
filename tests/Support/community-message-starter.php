<?php

use App\Http\Controllers\CommunityMessageController;
use App\Models\Community;
use App\Models\CommunityConversation;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$db = DB::connection();
if (! $app->environment('testing') || $db->getConfig('driver') !== 'mysql' || $db->getDatabaseName() !== 'scool_test'
    || $db->selectOne('SELECT DATABASE() AS name')->name !== 'scool_test') {
    fwrite(STDERR, "SAFETY GUARD: Message starter requires testing/scool_test.\n");
    exit(2);
}
[$script, $communityId, $actorId, $recipientId, $directory, $tag] = $argv;
$mode = $argv[6] ?? 'start';
$conversation = $mode !== 'start' ? CommunityConversation::findOrFail($argv[7]) : null;
$actor = User::findOrFail($actorId);
$community = Community::findOrFail($communityId);
Auth::setUser($actor);
$request = Request::create('/messages', 'POST', ['recipient_id' => $recipientId,
    'body' => 'Concurrent retry body', 'client_send_id' => 'concurrent-retry-key']);
$request->setUserResolver(fn () => $actor);
$checks = 0;
DB::listen(function (QueryExecuted $query) use ($directory, $tag, $mode, &$checks): void {
    if (str_starts_with($query->sql, 'select exists') && str_contains($query->sql, '`community_memberships`') && ++$checks === 2) {
        touch($directory.'/'.$tag.'.attempting');
        if ($mode === 'show') {
            $deadline = microtime(true) + 15;
            while (! is_file($directory.'/continue')) {
                if (microtime(true) >= $deadline) {
                    exit(4);
                }
                usleep(10000);
            }
        }
    }
});
touch($directory.'/'.$tag.'.ready');
$deadline = microtime(true) + 20;
while (! is_file($directory.'/release')) {
    if (microtime(true) >= $deadline) {
        exit(3);
    }
    usleep(10000);
}
try {
    $controller = app(CommunityMessageController::class);
    if ($mode === 'show') {
        $view = $controller->show($request, $community, $conversation);
        echo json_encode(['status' => 200, 'message_count' => $view->getData()['messages']->count()]);
    } else {
        $response = $mode === 'send' ? $controller->store($request, $community, $conversation)
            : $controller->start($request, $community);
        echo json_encode(['status' => $response->getStatusCode()]);
    }
} catch (AuthorizationException $error) {
    echo json_encode(['status' => $error->status() ?? 403]);
} catch (HttpExceptionInterface $error) {
    echo json_encode(['status' => $error->getStatusCode()]);
} catch (Throwable $error) {
    fwrite(STDERR, get_class($error)."\n");
    exit(1);
}
