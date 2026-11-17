<?php

namespace Tests\Integration;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class MySqlPaymentConcurrencyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() !== 'mysql' || DB::connection()->getDatabaseName() !== 'pawmart_phase5_testing') {
            throw new \RuntimeException('Dedicated test database required.');
        }
        $this->artisan('migrate', ['--no-interaction' => true])->assertSuccessful();
    }

    #[TestWith(['start'])] #[TestWith(['paid'])] #[TestWith(['late_paid'])]
    public function test_concurrent_payment_operations_preserve_one_transaction_and_stock(string $mode): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create(['stock' => 5, 'price' => '25000.00']);
        $processes = [];
        $order = null;
        try {
            app(CartService::class)->change($user, $product->id, 2, true);
            $cart = Cart::where('user_id', $user->id)->firstOrFail();
            $order = app(CheckoutService::class)->place($user, ['checkout_token' => $cart->checkout_token, 'recipient_name' => 'Test', 'recipient_phone' => '081234567890', 'shipping_address' => 'Alamat pengujian', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40111']);
            if ($mode !== 'start') {
                $order->update(['expires_at' => now()->subMinute()]);
                Payment::create(['order_id' => $order->id, 'midtrans_order_id' => $order->order_number, 'gross_amount' => $order->total_amount, 'snap_token' => 'parallel-test-token', 'attempted_at' => now()]);
            }
            DB::beginTransaction();
            Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            for ($i = 0; $i < 2; $i++) {
                $status = ['order_id' => $order->order_number, 'gross_amount' => $order->total_amount, 'transaction_id' => 'tx-'.$order->id, 'transaction_status' => ($mode === 'late_paid' && $i === 0) ? 'expire' : 'settlement', 'status_code' => ($mode === 'late_paid' && $i === 0) ? '202' : '200', 'currency' => 'IDR'];
                $process = new Process([PHP_BINARY, base_path('tests/Support/payment-worker.php')], base_path(), ['APP_ENV' => 'testing']);
                $process->setTimeout(40);
                $process->setInput(json_encode(['connection' => config('database.connections.mysql'), 'order' => $order->id, 'action' => $mode === 'start' ? 'start' : ($i === 0 ? 'expire' : 'sync'), 'status' => $status], JSON_THROW_ON_ERROR));
                $process->start();
                $processes[] = $process;
            }
            foreach ($processes as $process) {
                $this->assertTrue($process->waitUntil(fn ($type, $output) => str_contains($output, 'LOCKING')), $process->getErrorOutput());
            }
            DB::commit();
            $requests = 0;
            foreach ($processes as $process) {
                $this->assertSame(0, $process->wait(), $process->getErrorOutput());
                preg_match('/REQUESTS:(\d+)/', $process->getOutput(), $matches);
                $requests += (int) $matches[1];
            }
            $this->assertSame(1, Payment::where('order_id', $order->id)->count());
            $order->refresh();
            if ($mode === 'start') {
                $this->assertSame(1, $requests);
                $this->assertSame(3, $product->fresh()->stock);
                $this->assertSame('parallel-test-token', $order->payment->snap_token);
            } else {
                $this->assertSame('paid', $order->payment_status);
                $this->assertFalse($order->stock_reserved);
                $stock = $product->fresh()->stock;
                $this->assertContains($stock, [3, 5]);
                $this->assertSame($stock === 5 ? 'payment_review' : 'processing', $order->order_status);
            }
        } finally {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop();
                }
            }
            if ($order) {
                Payment::where('order_id', $order->id)->delete();
                $order->delete();
            }$user->delete();
            $product->forceDelete();
            $category->delete();
        }
    }
}
