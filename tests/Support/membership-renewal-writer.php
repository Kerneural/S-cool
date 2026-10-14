<?php

use App\Http\Controllers\Payment\SePayWebhookController;
use App\Models\Payment;
use App\Services\Payment\SePayGateway;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$connection = DB::connection();
if (! $app->environment('testing') || $connection->getConfig('driver') !== 'mysql' || $connection->getDatabaseName() !== 'scool_test') {
    fwrite(STDERR, "SAFETY GUARD: Renewal writer requires testing and MySQL scool_test.\n");
    exit(2);
}
[$script, $paymentId, $eventId, $directory, $tag] = $argv;
config(['services.sepay.test_mode' => true, 'services.sepay.webhook_token' => 'concurrency-test-token', 'services.sepay.account_number' => 'CONCURRENCY-TEST']);
$payment = Payment::findOrFail($paymentId);
$request = Request::create('/webhooks/sepay', 'POST', [
    'id' => (int) $eventId, 'transferType' => 'in', 'accountNumber' => 'CONCURRENCY-TEST',
    'referenceCode' => 'CONCURRENT-'.$eventId, 'content' => $payment->external_reference,
    'transferAmount' => $payment->amount, 'currency' => 'VND',
]);
$request->headers->set('Authorization', 'Apikey concurrency-test-token');
$connection->beforeExecuting(function (string $query) use ($directory, $tag): void {
    if (str_contains($query, '`users`') && str_contains($query, 'for update')) {
        touch($directory.'/'.$tag.'.attempting');
    }
});
touch($directory.'/'.$tag.'.ready');
$deadline = microtime(true) + 20;
while (! is_file($directory.'/release')) {
    if (microtime(true) >= $deadline) {
        fwrite(STDERR, "Renewal barrier timeout.\n");
        exit(3);
    }
    usleep(10000);
}
try {
    $response = app(SePayWebhookController::class)->handle($request, new SePayGateway);
    echo json_encode(['status' => $response->getStatusCode()]);
} catch (Throwable $error) {
    fwrite(STDERR, get_class($error)."\n");
    exit(1);
}
