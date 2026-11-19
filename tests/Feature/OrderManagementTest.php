<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\MidtransClient;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\UsesMemoryDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use UsesMemoryDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['midtrans.environment' => 'sandbox', 'midtrans.server_key' => 'test-api-private', 'midtrans.client_key' => 'test-client-private', 'midtrans.signature_secret_key' => 'test-signing-private']);
        Http::preventStrayRequests();
    }

    /** @return array{Order, Product, User} */
    private function order(bool $paid = false, ?User $customer = null): array
    {
        $customer ??= User::factory()->create();
        $product = Product::factory()->create(['name' => 'Makanan Kucing Historis', 'price' => '25000.00', 'stock' => 5]);
        $carts = app(CartService::class);
        $carts->change($customer, $product->id, 2, true);
        $order = app(CheckoutService::class)->place($customer, ['checkout_token' => $carts->cart($customer)->checkout_token, 'recipient_name' => 'Penerima PawMart', 'recipient_phone' => '081234567890', 'shipping_address' => 'Jalan Melati 12', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40111']);
        if ($paid) {
            Http::fake([MidtransClient::SNAP => Http::response(['token' => 'test-order-token']), MidtransClient::API.'*' => Http::response($this->gatewayStatus($order))]);
            app(PaymentService::class)->start($order);
            app(PaymentService::class)->sync($order);
        }

        return [$order->fresh(), $product->fresh(), $customer];
    }

    private function gatewayStatus(Order $order): array
    {
        return ['order_id' => $order->order_number, 'gross_amount' => $order->total_amount, 'status_code' => '200', 'transaction_status' => 'settlement', 'transaction_id' => 'test-tx-'.$order->id, 'payment_type' => 'qris', 'currency' => 'IDR', 'fraud_status' => 'accept'];
    }

    public function test_admin_sees_all_orders_and_details_with_safe_payment_information(): void
    {
        [$paid] = $this->order(true);
        [$pending] = $this->order();
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('admin.orders.index'))->assertOk()->assertSee($paid->order_number)->assertSee($pending->order_number)->assertSee('65.000,00');
        $this->get(route('admin.orders.show', $paid))->assertOk()->assertSee('Makanan Kucing Historis')->assertSee('Jalan Melati 12')->assertSee('QRIS')->assertSee('Tandai Dikirim')->assertDontSee('test-order-token')->assertDontSee('test-api-private')->assertDontSee('test-client-private')->assertDontSee('test-signing-private');
        $this->get(route('admin.orders.show', $pending))->assertOk()->assertDontSee('Tandai Dikirim');
    }

    public function test_fulfillment_changes_only_order_status_and_preserves_paid_on_repeated_webhooks(): void
    {
        [$order, $product] = $this->order(true);
        $paymentBefore = $order->payment->getAttributes();
        $before = $order->getAttributes();
        unset($before['order_status'], $before['updated_at'], $before['shipped_at'], $before['completed_at']);
        $this->actingAs(User::factory()->admin()->create());
        foreach (['shipped', 'completed'] as $status) {
            $this->patch(route('admin.orders.update', $order), ['order_status' => $status])->assertRedirect(route('admin.orders.show', $order))->assertSessionHas('status');
            $this->assertSame($status, $order->fresh()->order_status);
            $this->assertSame($paymentBefore, $order->payment()->first()->getAttributes());
            $after = $order->fresh()->getAttributes();
            unset($after['order_status'], $after['updated_at'], $after['shipped_at'], $after['completed_at']);
            $this->assertSame($before, $after);
            $data = $this->gatewayStatus($order);
            $data['signature_key'] = hash('sha512', $data['order_id'].$data['status_code'].$data['gross_amount'].config('midtrans.signature_secret_key'));
            $this->postJson(route('midtrans.notification'), $data)->assertOk();
            $this->assertSame($status, $order->fresh()->order_status);
            $this->assertSame('paid', $order->fresh()->payment_status);
            $this->assertSame(3, $product->fresh()->stock);
            $paymentBefore = $order->payment()->first()->getAttributes();
        }
        $this->patchJson(route('admin.orders.update', $order), ['order_status' => 'completed'])->assertUnprocessable();
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('payments', 1);
    }

    #[TestWith(['awaiting_payment', 'pending', 'shipped'])]
    #[TestWith(['cancelled', 'cancelled', 'shipped'])]
    #[TestWith(['cancelled', 'expired', 'shipped'])]
    #[TestWith(['cancelled', 'failed', 'shipped'])]
    #[TestWith(['processing', 'paid', 'completed'])]
    #[TestWith(['shipped', 'paid', 'processing'])]
    #[TestWith(['completed', 'paid', 'shipped'])]
    #[TestWith(['payment_review', 'paid', 'shipped'])]
    public function test_illegal_transitions_cannot_change_order_or_stock(string $current, string $paymentStatus, string $next): void
    {
        [$order, $product] = $this->order($paymentStatus === 'paid');
        $order->update(['order_status' => $current, 'payment_status' => $paymentStatus]);
        $this->actingAs(User::factory()->admin()->create())->patchJson(route('admin.orders.update', $order), ['order_status' => $next])->assertUnprocessable()->assertJsonValidationErrors('order_status');
        $this->assertSame($current, $order->fresh()->order_status);
        $this->assertSame($paymentStatus, $order->fresh()->payment_status);
        $this->assertSame(3, $product->fresh()->stock);
    }

    #[TestWith(['missing_payment'])]
    #[TestWith(['review'])]
    #[TestWith(['amount'])]
    #[TestWith(['released_stock'])]
    #[TestWith(['not_verified'])]
    public function test_paid_flag_alone_is_not_enough_to_ship(string $reason): void
    {
        [$order, $product] = $this->order($reason !== 'missing_payment');
        $order->update(['payment_status' => 'paid', 'order_status' => 'processing', 'stock_reserved' => false]);
        match ($reason) {
            'review' => $order->payment()->update(['review_reason' => 'paid_status_conflict']),
            'amount' => $order->payment()->update(['gross_amount' => '1.00']),
            'released_stock' => $order->update(['stock_released_at' => now()]),
            'not_verified' => $order->payment()->update(['paid_at' => null]),
            default => null,
        };
        $this->actingAs(User::factory()->admin()->create())->patchJson(route('admin.orders.update', $order), ['order_status' => 'shipped'])->assertUnprocessable();
        $this->assertSame('processing', $order->fresh()->order_status);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_admin_cannot_manipulate_payment_or_totals(): void
    {
        [$order, $product] = $this->order();
        $this->actingAs(User::factory()->admin()->create())->patchJson(route('admin.orders.update', $order), ['order_status' => 'shipped', 'payment_status' => 'paid', 'transaction_status' => 'settlement', 'total_amount' => '1.00', 'stock_reserved' => false])->assertUnprocessable()->assertJsonValidationErrors(['payment_status', 'transaction_status', 'total_amount', 'stock_reserved']);
        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->assertSame('65000.00', $order->fresh()->total_amount);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_admin_search_and_combined_filters_are_scoped_correctly(): void
    {
        $customer = User::factory()->create(['name' => 'Ayu PawMart']);
        [$paid] = $this->order(true, $customer);
        [$pending] = $this->order(false, $customer);
        [$other] = $this->order();
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('admin.orders.index', ['q' => $paid->order_number]))->assertOk()->assertSee($paid->order_number)->assertDontSee($pending->order_number)->assertDontSee($other->order_number);
        $this->get(route('admin.orders.index', ['q' => 'Ayu', 'payment_status' => 'paid', 'order_status' => 'processing']))->assertOk()->assertSee($paid->order_number)->assertDontSee($pending->order_number)->assertDontSee($other->order_number);
        $this->get(route('admin.orders.index', ['q' => 'Ayu', 'payment_status' => 'pending']))->assertOk()->assertSee($pending->order_number)->assertDontSee($paid->order_number)->assertDontSee($other->order_number);
        $this->get(route('admin.orders.index', ['q' => 'TidakAdaHasil']))->assertOk()->assertSee('Belum ada pesanan yang sesuai');
        $this->getJson(route('admin.orders.index', ['order_status' => 'invalid', 'q' => ['invalid']]))->assertUnprocessable();
    }

    public function test_history_and_invoice_are_owner_only_and_admin_routes_are_protected(): void
    {
        [$order, , $customer] = $this->order();
        [$other] = $this->order();
        foreach (['admin.orders.index', 'admin.orders.show', 'orders.invoice'] as $route) {
            $this->get(route($route, $route === 'admin.orders.index' ? [] : $order))->assertRedirect(route('login'));
        }
        $this->patch(route('admin.orders.update', $order), ['order_status' => 'shipped'])->assertRedirect(route('login'));
        $this->actingAs($customer);
        $this->get(route('orders.index'))->assertOk()->assertSee($order->order_number)->assertDontSee($other->order_number)->assertSee('Lihat Detail')->assertSee('Lihat Invoice')->assertSee('Bayar Sekarang');
        $this->get(route('orders.show', $other))->assertNotFound();
        $this->get(route('orders.invoice', $other))->assertNotFound();
        $this->get(route('admin.orders.index'))->assertForbidden();
        $this->get(route('admin.orders.show', $order))->assertForbidden();
        $this->patch(route('admin.orders.update', $order), ['order_status' => 'shipped'])->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->get(route('orders.invoice', $order))->assertForbidden();
    }

    public function test_invoice_keeps_snapshot_after_product_changes_and_archival(): void
    {
        [$order, $product, $customer] = $this->order(true);
        $product->update(['name' => 'Nama Baru Produk', 'price' => '999999.00']);
        $product->delete();
        $this->actingAs($customer);
        $response = $this->get(route('orders.invoice', $order));
        $response->assertOk()->assertSee('PawMart Pet Shop')->assertSee($order->order_number)->assertSee($customer->name)->assertSee('Penerima PawMart')->assertSee('Makanan Kucing Historis')->assertSee('Rp25.000,00')->assertSee('Rp50.000,00')->assertSee('Rp15.000,00')->assertSee('Rp65.000,00')->assertSee('Berhasil')->assertSee('Cetak / Simpan PDF')->assertDontSee('Nama Baru Produk')->assertDontSee('999.999')->assertDontSee('test-order-token')->assertDontSee('test-api-private')->assertDontSee('test-signing-private')->assertDontSee('test-client-private');
        $this->get(route('orders.show', $order))->assertOk()->assertSee('Makanan Kucing Historis')->assertSee('Perkembangan pesanan')->assertSee('QRIS')->assertDontSee('test-client-private');
    }

    public function test_customer_empty_history_and_expired_payment_action(): void
    {
        $this->actingAs(User::factory()->create())->get(route('orders.index'))->assertOk()->assertSee('Belum ada pesanan.');
        [$order, , $customer] = $this->order();
        $order->update(['expires_at' => now()->subMinute()]);
        $this->actingAs($customer)->get(route('orders.index'))->assertOk()->assertDontSee('Lanjutkan pembayaran')->assertDontSee('Bayar Sekarang')->assertSee('Lihat Detail');
        $this->get(route('orders.invoice', $order))->assertOk()->assertSee('Menunggu pembayaran');
    }

    public function test_pagination_preserves_admin_filters(): void
    {
        $customer = User::factory()->create(['name' => 'Pagination Customer']);
        for ($i = 0; $i < 16; $i++) {
            $this->order(false, $customer);
        }
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.orders.index', ['q' => 'Pagination', 'payment_status' => 'pending']))->assertOk()->assertViewHas('orders', fn ($orders) => $orders->total() === 16 && $orders->count() === 15 && str_contains($orders->nextPageUrl(), 'payment_status=pending') && str_contains($orders->nextPageUrl(), 'q=Pagination'));
        $this->actingAs($customer)->get(route('orders.index'))->assertOk()->assertViewHas('orders', fn ($orders) => $orders->total() === 16 && $orders->count() === 10);
    }
}
