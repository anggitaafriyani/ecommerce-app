<?php

namespace Tests\Integration;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class MySqlCheckoutConcurrencyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() !== 'mysql' || DB::connection()->getDatabaseName() !== 'pawmart_phase5_testing') {
            throw new \RuntimeException('Run only on dedicated pawmart_phase5_testing MySQL database.');
        }
        $this->artisan('migrate', ['--no-interaction' => true])->assertSuccessful();
    }

    #[TestWith([false])] #[TestWith([true])]
    public function test_parallel_checkout_prevents_overselling_and_duplicate_orders(bool $sameCustomer): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create(['stock' => 1]);
        $users = User::factory()->count($sameCustomer ? 1 : 2)->create();
        $processes = [];
        $orderIds = [];
        try {
            foreach ($users as $user) {
                app(CartService::class)->change($user, $product->id, 1, true);
            }
            DB::beginTransaction();
            if ($sameCustomer) {
                Cart::where('user_id', $users[0]->id)->lockForUpdate()->firstOrFail();
            } else {
                Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
            }
            for ($i = 0; $i < 2; $i++) {
                $user = $users[$sameCustomer ? 0 : $i];
                $cart = Cart::where('user_id', $user->id)->firstOrFail();
                $data = ['checkout_token' => $cart->checkout_token, 'recipient_name' => 'Uji Paralel', 'recipient_phone' => '081234567890', 'shipping_address' => 'Alamat pengujian', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40111'];
                $process = new Process([PHP_BINARY, base_path('tests/Support/checkout-worker.php')], base_path(), ['APP_ENV' => 'testing']);
                $process->setInput(json_encode(['connection' => config('database.connections.mysql'), 'user' => $user->id, 'data' => $data, 'barrier' => $sameCustomer ? '`carts`' : '`products`'], JSON_THROW_ON_ERROR));
                $process->setTimeout(30);
                $process->start();
                $processes[] = $process;
            }
            foreach ($processes as $process) {
                $ready = $process->waitUntil(fn (string $type, string $output): bool => str_contains($output, 'LOCKING'));
                $this->assertTrue($ready, 'Worker must reach row lock while the parent holds it. '.$process->getErrorOutput());
            }
            DB::commit();
            $output = [];
            foreach ($processes as $process) {
                $this->assertSame(0, $process->wait(), $process->getErrorOutput());
                $output[] = $process->getOutput();
            }
            $orders = Order::whereIn('user_id', $users->pluck('id'))->get();
            $this->assertCount(1, $orders);
            $this->assertSame(0, $product->fresh()->stock);
            $this->assertSame(1, $orders[0]->items()->count());
            if ($sameCustomer) {
                foreach ($output as $result) {
                    $this->assertStringContainsString('ORDER:'.$orders[0]->id, $result);
                }
            } else {
                $this->assertSame(1, count(array_filter($output, fn ($result) => str_contains($result, 'REJECTED'))));
            }
        } finally {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop();
                }
            }
            Order::whereIn('user_id', $users->pluck('id'))->delete();
            User::whereIn('id', $users->pluck('id'))->delete();
            $product->forceDelete();
            $category->delete();
        }
    }
}
