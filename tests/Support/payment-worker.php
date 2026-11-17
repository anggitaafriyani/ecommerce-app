<?php

use App\Models\Order;
use App\Services\MidtransClient;
use App\Services\PaymentService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$input = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
if (($input['connection']['database'] ?? '') !== 'pawmart_phase5_testing') {
    throw new RuntimeException('Dedicated test database required.');
}
config(['database.default' => 'mysql', 'database.connections.mysql' => $input['connection'], 'midtrans.environment' => 'sandbox', 'midtrans.server_key' => 'SB-Mid-server-test-only', 'midtrans.client_key' => 'SB-Mid-client-test-only']);
DB::purge('mysql');
DB::connection()->beforeExecuting(function (string $query): void {
    if (str_contains($query, '`orders`') && str_contains($query, 'for update')) {
        echo "LOCKING\n";
        flush();
    }
});
Http::preventStrayRequests();
Http::fake([MidtransClient::SNAP => Http::response(['token' => 'parallel-test-token']), MidtransClient::API.'*' => Http::response($input['status'])]);
$order = Order::findOrFail($input['order']);
try {
    $service = app(PaymentService::class);
    match ($input['action']) {
        'start' => $service->start($order),'expire' => $service->cancel($order, true),'sync' => $service->sync($order, 'webhook')
    };
} catch (ValidationException $e) {
    echo "REJECTED\n";
}
echo 'REQUESTS:'.count(Http::recorded())."\n";
