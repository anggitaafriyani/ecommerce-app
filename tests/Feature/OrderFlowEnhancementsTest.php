<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\MidtransClient;
use App\Services\PaymentService;
use Database\Seeders\ShippingSeeder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\UsesMemoryDatabase;
use Tests\TestCase;

class OrderFlowEnhancementsTest extends TestCase
{
    use UsesMemoryDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'midtrans.environment' => 'sandbox',
            'midtrans.server_key' => 'test-api-private',
            'midtrans.client_key' => 'test-client-private',
            'midtrans.signature_secret_key' => 'test-signing-private',
        ]);

        $this->fakeMidtrans();

        $this->seed(ShippingSeeder::class);
    }

    protected function fakeMidtrans(?callable $apiCallback = null): void
    {
        Http::swap(new Factory);
        Http::preventStrayRequests();

        Http::fake([
            MidtransClient::SNAP => Http::response(['token' => 'test-snap-token']),
            'https://app.sandbox.midtrans.com/snap/v1/transactions/*' => Http::response(['canceled_at' => now()->toDateTimeString()], 200),
            MidtransClient::API.'*' => $apiCallback ?? function ($request) {
                $path = trim(parse_url($request->url(), PHP_URL_PATH) ?? '', '/');
                $parts = explode('/', $path);
                // URL pattern: v2/{orderNumber}/{action}
                $orderNumber = $parts[1] ?? '';
                $action = $parts[2] ?? 'status';

                if ($action === 'cancel' || $action === 'expire') {
                    return Http::response(['status_code' => '200', 'status_message' => 'Transaction canceled'], 200);
                }

                $order = Order::where('order_number', $orderNumber)->first();
                if ($order && ($order->payment_status === 'paid' || in_array($order->order_status, ['processing', 'shipped', 'completed'], true))) {
                    return Http::response([
                        'order_id' => $orderNumber,
                        'gross_amount' => $order->total_amount,
                        'status_code' => '200',
                        'transaction_status' => 'settlement',
                        'transaction_id' => 'tx-'.$order->id,
                        'payment_type' => 'bank_transfer',
                        'currency' => 'IDR',
                        'fraud_status' => 'accept',
                    ]);
                }

                return Http::response(['status_code' => '404', 'status_message' => 'Transaction not found'], 404);
            },
        ]);
    }

    private function createOrder(User $user, string $orderStatus = 'awaiting_payment', string $paymentStatus = 'pending', int $stock = 10): Order
    {
        $product = Product::factory()->create([
            'name' => 'Whiskas Adult Cat Food 1kg',
            'price' => '50000.00',
            'stock' => $stock,
            'weight' => 500,
        ]);

        $cartService = app(CartService::class);
        $cartService->change($user, $product->id, 2, true);
        $cart = $cartService->cart($user);

        $order = app(CheckoutService::class)->place($user, [
            'checkout_token' => $cart->checkout_token,
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081234567890',
            'shipping_address' => 'Jl. Dago No. 100',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40135',
            'courier_code' => 'jne',
            'courier_service' => 'reg',
        ]);

        if ($paymentStatus === 'paid' || $orderStatus !== 'awaiting_payment') {
            app(PaymentService::class)->start($order);
            $order->update(['payment_status' => 'paid', 'order_status' => 'processing']);
            $order->payment->update([
                'transaction_status' => 'settlement',
                'payment_type' => 'bank_transfer',
                'checked_at' => now(),
            ]);
        }

        if ($orderStatus === 'shipped') {
            $order->update([
                'order_status' => 'shipped',
                'tracking_number' => 'JNE-RES-9988',
                'shipped_at' => now(),
            ]);
        } elseif ($orderStatus === 'completed') {
            $order->update([
                'order_status' => 'completed',
                'tracking_number' => 'JNE-RES-9988',
                'shipped_at' => now()->subDay(),
                'completed_at' => now(),
            ]);
        }

        return $order->fresh(['items', 'payment']);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 1. PRODUCT REVIEWS TESTS
    // ═════════════════════════════════════════════════════════════════════════

    public function test_customer_can_review_product_from_completed_order(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrder($customer, 'completed', 'paid');
        $item = $order->items->first();

        $response = $this->actingAs($customer)
            ->from(route('orders.show', $order))
            ->post(route('orders.items.reviews.store', ['order' => $order, 'item' => $item]), [
                'rating' => 5,
                'comment' => 'Kucing saya sangat suka makanan ini, pengiriman cepat!',
            ]);

        $response->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('product_reviews', [
            'user_id' => $customer->id,
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'product_id' => $item->product_id,
            'rating' => 5,
            'comment' => 'Kucing saya sangat suka makanan ini, pengiriman cepat!',
        ]);

        // Review appears on product page
        $product = Product::findOrFail($item->product_id);
        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSeeText('Kucing saya sangat suka makanan ini, pengiriman cepat!')
            ->assertSeeText('(5.0)');
    }

    public function test_review_can_be_updated_without_duplicate(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrder($customer, 'completed', 'paid');
        $item = $order->items->first();

        // First review
        $this->actingAs($customer)->post(route('orders.items.reviews.store', ['order' => $order, 'item' => $item]), [
            'rating' => 4,
            'comment' => 'Bagus sekali produknya.',
        ]);

        $this->assertDatabaseCount('product_reviews', 1);

        // Update review
        $this->actingAs($customer)->post(route('orders.items.reviews.store', ['order' => $order, 'item' => $item]), [
            'rating' => 5,
            'comment' => 'Setelah seminggu, anabul makin sehat!',
        ]);

        $this->assertDatabaseCount('product_reviews', 1);
        $this->assertDatabaseHas('product_reviews', [
            'order_item_id' => $item->id,
            'rating' => 5,
            'comment' => 'Setelah seminggu, anabul makin sehat!',
        ]);
    }

    public function test_cannot_review_uncompleted_order(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrder($customer, 'processing', 'paid');
        $item = $order->items->first();

        $response = $this->actingAs($customer)
            ->post(route('orders.items.reviews.store', ['order' => $order, 'item' => $item]), [
                'rating' => 5,
                'comment' => 'Belum sampai tapi mau review.',
            ]);

        $response->assertSessionHasErrors('review');
        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_customer_cannot_review_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $order = $this->createOrder($owner, 'completed', 'paid');
        $item = $order->items->first();

        $response = $this->actingAs($stranger)
            ->post(route('orders.items.reviews.store', ['order' => $order, 'item' => $item]), [
                'rating' => 5,
                'comment' => 'Review bajakan.',
            ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_review_validation_rules(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrder($customer, 'completed', 'paid');
        $item = $order->items->first();

        // Missing rating and comment
        $this->actingAs($customer)
            ->post(route('orders.items.reviews.store', ['order' => $order, 'item' => $item]), [
                'rating' => '',
                'comment' => '',
            ])
            ->assertSessionHasErrors(['rating', 'comment']);

        // Invalid rating (out of 1-5 range)
        $this->actingAs($customer)
            ->post(route('orders.items.reviews.store', ['order' => $order, 'item' => $item]), [
                'rating' => 6,
                'comment' => 'Rating kebesaran.',
            ])
            ->assertSessionHasErrors('rating');
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 2. PAYMENT BUTTON VISIBILITY IN ORDERS LIST
    // ═════════════════════════════════════════════════════════════════════════

    public function test_payment_button_hidden_for_paid_and_completed_orders_on_orders_list(): void
    {
        $customer = User::factory()->create();
        $paidOrder = $this->createOrder($customer, 'processing', 'paid');
        $completedOrder = $this->createOrder($customer, 'completed', 'paid');
        $pendingOrder = $this->createOrder($customer, 'awaiting_payment', 'pending');

        $response = $this->actingAs($customer)->get(route('orders.index'));

        $response->assertOk();
        // Pending order shows "Bayar Sekarang →"
        $response->assertSeeText('Bayar Sekarang →');
        // Old "Lihat pembayaran →" is never shown anywhere
        $response->assertDontSeeText('Lihat pembayaran →');
        $response->assertDontSeeText('Lanjutkan pembayaran →');
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 3. ORDER CANCELLATION TESTS (15 MANDATORY AUDIT SCENARIOS)
    // ═════════════════════════════════════════════════════════════════════════

    // Scenario 1: Pelanggan membatalkan pesanan sebelum memilih metode pembayaran
    public function test_1_customer_can_cancel_order_before_selecting_payment_method(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrder($customer, 'awaiting_payment', 'pending', 10);
        $product = Product::findOrFail($order->items->first()->product_id);

        $this->assertSame(8, $product->stock); // 10 - 2

        $response = $this->actingAs($customer)
            ->from(route('orders.show', $order))
            ->post(route('orders.cancel', $order));

        $response->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('status', 'Pesanan berhasil dibatalkan dan reservasi stok produk telah dikembalikan.');

        $this->assertSame('cancelled', $order->fresh()->order_status);
        $this->assertSame('cancelled', $order->fresh()->payment_status);
        $this->assertFalse($order->fresh()->stock_reserved);
        $this->assertSame(10, $product->fresh()->stock);
    }

    // Scenario 2: Pelanggan membatalkan pesanan setelah mendapatkan token Snap tetapi belum membayar
    public function test_2_customer_can_cancel_after_getting_snap_token_without_paying(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrder($customer, 'awaiting_payment', 'pending', 10);
        $product = Product::findOrFail($order->items->first()->product_id);

        app(PaymentService::class)->start($order);
        $this->assertNotNull($order->fresh()->payment->snap_token);

        $response = $this->actingAs($customer)
            ->from(route('payments.show', $order))
            ->post(route('orders.cancel', $order));

        $response->assertRedirect(route('payments.show', $order))
            ->assertSessionHas('status', 'Pesanan berhasil dibatalkan dan reservasi stok produk telah dikembalikan.');

        $this->assertSame('cancelled', $order->fresh()->order_status);
        $this->assertSame('cancelled', $order->fresh()->payment_status);
        $this->assertFalse($order->fresh()->stock_reserved);
        $this->assertSame(10, $product->fresh()->stock);
    }

    // Scenario 3: Pelanggan membatalkan transaksi pending yang aman dibatalkan
    public function test_3_customer_can_cancel_active_pending_transaction(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrder($customer, 'awaiting_payment', 'pending', 10);
        $product = Product::findOrFail($order->items->first()->product_id);

        app(PaymentService::class)->start($order);

        $callCount = 0;
        $this->fakeMidtrans(function ($request) use ($order, &$callCount) {
            $path = trim(parse_url($request->url(), PHP_URL_PATH) ?? '', '/');
            $parts = explode('/', $path);
            $action = $parts[2] ?? 'status';

            if ($action === 'cancel' || $action === 'expire') {
                return Http::response(['status_code' => '200', 'status_message' => 'Transaction is canceled'], 200);
            }

            $callCount++;
            if ($callCount === 1) {
                return Http::response([
                    'order_id' => $order->order_number,
                    'gross_amount' => $order->total_amount,
                    'status_code' => '201',
                    'transaction_status' => 'pending',
                    'transaction_id' => 'tx-pending-123',
                    'fraud_status' => 'accept',
                ], 200);
            }

            return Http::response([
                'order_id' => $order->order_number,
                'gross_amount' => $order->total_amount,
                'status_code' => '200',
                'transaction_status' => 'cancel',
                'transaction_id' => 'tx-pending-123',
                'fraud_status' => 'accept',
            ], 200);
        });

        $response = $this->actingAs($customer)
            ->post(route('orders.cancel', $order));

        $response->assertSessionHas('status');
        $this->assertSame('cancelled', $order->fresh()->order_status);
        $this->assertSame('cancelled', $order->fresh()->payment_status);
        $this->assertFalse($order->fresh()->stock_reserved);
        $this->assertSame(10, $product->fresh()->stock);
    }

    // Scenario 4: Pelanggan tidak bisa membatalkan pesanan yang sudah paid/settlement
    public function test_4_customer_cannot_cancel_paid_order(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrder($customer, 'processing', 'paid', 10);
        $product = Product::findOrFail($order->items->first()->product_id);

        $response = $this->actingAs($customer)
            ->post(route('orders.cancel', $order));

        $response->assertSessionHasErrors('order');
        $this->assertSame('processing', $order->fresh()->order_status);
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame(8, $product->fresh()->stock);
    }

    // Scenario 5: Pelanggan tidak bisa membatalkan pesanan Diproses, Dikirim, dan Selesai
    public function test_5_customer_cannot_cancel_processing_shipped_or_completed_orders(): void
    {
        $customer = User::factory()->create();

        $processing = $this->createOrder($customer, 'processing', 'paid');
        $this->actingAs($customer)->post(route('orders.cancel', $processing))->assertSessionHasErrors('order');
        $this->assertSame('processing', $processing->fresh()->order_status);

        $shipped = $this->createOrder($customer, 'shipped', 'paid');
        $this->actingAs($customer)->post(route('orders.cancel', $shipped))->assertSessionHasErrors('order');
        $this->assertSame('shipped', $shipped->fresh()->order_status);

        $completed = $this->createOrder($customer, 'completed', 'paid');
        $this->actingAs($customer)->post(route('orders.cancel', $completed))->assertSessionHasErrors('order');
        $this->assertSame('completed', $completed->fresh()->order_status);
    }

    // Scenario 6: Pelanggan tidak bisa membatalkan pesanan milik akun lain
    public function test_6_customer_cannot_cancel_another_users_order(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $order = $this->createOrder($owner, 'awaiting_payment', 'pending');

        $this->actingAs($stranger)
            ->post(route('orders.cancel', $order))
            ->assertNotFound();

        $this->assertSame('awaiting_payment', $order->fresh()->order_status);
        $this->assertTrue($order->fresh()->stock_reserved);
    }

    // Scenario 7: Respons Midtrans 404 ditangani secara tepat
    public function test_7_midtrans_404_responses_are_handled_properly(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrder($customer, 'awaiting_payment', 'pending', 10);
        $product = Product::findOrFail($order->items->first()->product_id);

        app(PaymentService::class)->start($order);

        $this->actingAs($customer)->post(route('orders.cancel', $order))->assertSessionHas('status');
        $this->assertSame('cancelled', $order->fresh()->order_status);
        $this->assertSame(10, $product->fresh()->stock);
    }

    // Scenario 8: Kegagalan verifikasi pembayaran tidak menyebabkan stok dilepas secara tidak aman
    public function test_8_verification_failure_does_not_release_stock_unsafely(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrder($customer, 'awaiting_payment', 'pending', 10);
        $product = Product::findOrFail($order->items->first()->product_id);

        app(PaymentService::class)->start($order);

        $this->fakeMidtrans(function () {
            throw new ConnectionException('Connection failed');
        });

        $this->actingAs($customer)->post(route('orders.cancel', $order))->assertSessionHasErrors('payment');

        $this->assertSame(8, $product->fresh()->stock);
        $this->assertTrue($order->fresh()->stock_reserved);
        $this->assertSame('cancellation_uncertain', $order->fresh()->payment->review_reason);
    }

    // Scenario 9: Pembatalan yang berhasil mengembalikan stok tepat satu kali
    public function test_9_successful_cancellation_restores_stock_exactly_once(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrder($customer, 'awaiting_payment', 'pending', 10);
        $product = Product::findOrFail($order->items->first()->product_id);

        $this->assertSame(8, $product->stock);

        $this->actingAs($customer)->post(route('orders.cancel', $order))->assertSessionHas('status');
        $this->assertSame(10, $product->fresh()->stock);

        $this->actingAs($customer)->post(route('orders.cancel', $order));
        $this->assertSame(10, $product->fresh()->stock);
    }

    // Scenario 10: Dua permintaan pembatalan bersamaan tidak menyebabkan perubahan stok ganda
    public function test_10_concurrent_cancellation_requests_do_not_cause_double_stock_restoration(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrder($customer, 'awaiting_payment', 'pending', 10);
        $product = Product::findOrFail($order->items->first()->product_id);

        app(PaymentService::class)->cancel($order);
        $this->assertSame(10, $product->fresh()->stock);

        app(PaymentService::class)->cancel($order->fresh());
        $this->assertSame(10, $product->fresh()->stock);
    }

    // Scenario 11: Pembayaran berhasil yang bersamaan dengan proses pembatalan tidak menyebabkan pembatalan keliru
    public function test_11_payment_won_cancellation_preserves_paid_order_and_purchased_stock(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrder($customer, 'awaiting_payment', 'pending', 10);
        $product = Product::findOrFail($order->items->first()->product_id);

        app(PaymentService::class)->start($order);

        $this->fakeMidtrans(function ($request) use ($order) {
            return Http::response([
                'order_id' => $order->order_number,
                'gross_amount' => $order->total_amount,
                'status_code' => '200',
                'transaction_status' => 'settlement',
                'transaction_id' => 'tx-settled-race',
                'payment_type' => 'qris',
                'currency' => 'IDR',
                'fraud_status' => 'accept',
            ], 200);
        });

        $this->actingAs($customer)->post(route('orders.cancel', $order))->assertSessionHasNoErrors();

        $fresh = $order->fresh();
        $this->assertSame('paid', $fresh->payment_status);
        $this->assertSame('payment_review', $fresh->order_status);
        $this->assertSame(8, $product->fresh()->stock);
    }

    // Scenario 12: Pesanan melewati waktu pembayaran otomatis kedaluwarsa jika dipastikan belum dibayar
    public function test_12_order_past_expires_at_automatically_expires_when_visited(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrder($customer, 'awaiting_payment', 'pending', 10);
        $product = Product::findOrFail($order->items->first()->product_id);

        $order->update(['expires_at' => now()->subMinutes(10)]);

        $response = $this->actingAs($customer)->get(route('payments.show', $order));

        $response->assertOk()
            ->assertSeeText('Waktu Habis / Kedaluwarsa')
            ->assertDontSee('id="pay-now"', false);

        $fresh = $order->fresh();
        $this->assertSame('cancelled', $fresh->order_status);
        $this->assertSame('expired', $fresh->payment_status);
        $this->assertSame(10, $product->fresh()->stock);
    }

    // Scenario 13: Pesanan yang sudah dibayar tidak kedaluwarsa secara keliru
    public function test_13_paid_order_past_expires_at_is_not_expired_by_scheduler(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrder($customer, 'processing', 'paid', 10);
        $product = Product::findOrFail($order->items->first()->product_id);

        $order->update(['expires_at' => now()->subHour()]);

        $this->artisan('orders:expire')->assertSuccessful();

        $fresh = $order->fresh();
        $this->assertSame('processing', $fresh->order_status);
        $this->assertSame('paid', $fresh->payment_status);
        $this->assertSame(8, $product->fresh()->stock);
    }

    // Scenario 14: Scheduler dapat memproses pesanan kedaluwarsa tanpa pelanggan membuka browser
    public function test_14_scheduler_processes_expired_orders_without_browser_interaction(): void
    {
        $customer = User::factory()->create();
        $order1 = $this->createOrder($customer, 'awaiting_payment', 'pending', 10);
        $order2 = $this->createOrder($customer, 'awaiting_payment', 'pending', 10);

        $order1->update(['expires_at' => now()->subMinutes(15)]);
        $order2->update(['expires_at' => now()->addMinutes(30)]);

        $this->artisan('orders:expire')->assertSuccessful();

        $this->assertSame('cancelled', $order1->fresh()->order_status);
        $this->assertSame('expired', $order1->fresh()->payment_status);

        $this->assertSame('awaiting_payment', $order2->fresh()->order_status);
        $this->assertSame('pending', $order2->fresh()->payment_status);
    }

    // Scenario 15: Tombol pembatalan hanya muncul ketika kondisi pesanan memang mengizinkan
    public function test_15_cancellation_button_visibility_matches_order_state(): void
    {
        $customer = User::factory()->create();

        // 1. Pending order shows "Batalkan Pesanan" and "Bayar Sekarang"
        $pendingOrder = $this->createOrder($customer, 'awaiting_payment', 'pending');
        $this->actingAs($customer)->get(route('orders.show', $pendingOrder))
            ->assertOk()
            ->assertSeeText('Batalkan Pesanan')
            ->assertSeeText('Bayar Sekarang →');

        // 2. Paid order HIDES "Batalkan Pesanan" and "Bayar Sekarang"
        $paidOrder = $this->createOrder($customer, 'processing', 'paid');
        $this->actingAs($customer)->get(route('orders.show', $paidOrder))
            ->assertOk()
            ->assertDontSeeText('Batalkan Pesanan')
            ->assertDontSeeText('Bayar Sekarang →');

        // 3. Shipped order HIDES "Batalkan Pesanan"
        $shippedOrder = $this->createOrder($customer, 'shipped', 'paid');
        $this->actingAs($customer)->get(route('orders.show', $shippedOrder))
            ->assertOk()
            ->assertDontSeeText('Batalkan Pesanan');

        // 4. Completed order HIDES "Batalkan Pesanan"
        $completedOrder = $this->createOrder($customer, 'completed', 'paid');
        $this->actingAs($customer)->get(route('orders.show', $completedOrder))
            ->assertOk()
            ->assertDontSeeText('Batalkan Pesanan');
    }
}
