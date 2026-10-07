<?php

// Test-only child process. Input remains in memory, never in CLI arguments/files.
use App\Actions\Communities\AcceptInvitation;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpKernel\Exception\HttpException;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$connection = $app->make('db')->connection();
if (! $app->environment('testing') || $connection->getConfig('driver') !== 'mysql'
    || $connection->getDatabaseName() !== 'scool_test') {
    fwrite(STDERR, "Unsafe test process configuration.\n");
    exit(2);
}
$input = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
try {
    if ($input['mode'] === 'queue') {
        $exit = Artisan::call('queue:work', ['connection' => 'database', '--queue' => $input['queue'], '--once' => true, '--tries' => 1]);
        echo $exit === 0 ? 'WORKED' : 'FAILED';
        exit($exit);
    }
    echo "READY\n";
    @ob_flush();
    flush();
    $app->make(AcceptInvitation::class)->handle($input['invitation_id'], User::findOrFail($input['user_id']), $input['token']);
    echo 'ACCEPTED';
} catch (HttpException $exception) {
    echo $exception->getStatusCode() === 404 ? 'REJECTED' : 'FAILED';
} catch (Throwable) {
    fwrite(STDERR, "Test process failed; sensitive exception details suppressed.\n");
    exit(1);
}
