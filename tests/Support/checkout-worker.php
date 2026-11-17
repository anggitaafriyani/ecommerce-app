<?php

use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$input = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
if (($input['connection']['database'] ?? '') !== 'pawmart_phase5_testing') {
    throw new RuntimeException('Dedicated test database required.');
}
config(['database.default' => 'mysql', 'database.connections.mysql' => $input['connection']]);
DB::purge('mysql');
DB::connection()->beforeExecuting(function (string $query) use ($input): void {
    if (str_contains($query, $input['barrier']) && str_contains($query, 'for update')) {
        echo "LOCKING\n";
        flush();
    }
});
try {
    $order = app(CheckoutService::class)->place(User::findOrFail($input['user']), $input['data']);
    echo 'ORDER:'.$order->id;
} catch (ValidationException $exception) {
    echo 'REJECTED';
}
