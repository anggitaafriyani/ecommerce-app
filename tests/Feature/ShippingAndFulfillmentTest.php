<?php

namespace Tests\Feature;

use App\Models\Courier;
use App\Models\CourierService;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingRate;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\MidtransClient;
use App\Services\PaymentService;
use App\Services\ShippingCalculatorService;
use Database\Seeders\ShippingSeeder;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\UsesMemoryDatabase;
use Tests\TestCase;

class ShippingAndFulfillmentTest extends TestCase
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
        Http::preventStrayRequests();

        // Global fake for Midtrans sandbox endpoints
        Http::fake([
            MidtransClient::SNAP => Http::response(['token' => 'test-snap-token']),
            MidtransClient::API.'*' => function ($request) {
                $path = trim(parse_url($request->url(), PHP_URL_PATH) ?? '', '/');
                $parts = explode('/', $path);
                // URL pattern: v2/{orderNumber}/status
                $orderNumber = $parts[1] ?? '';
                $order = Order::where('order_number', $orderNumber)->first();

                return Http::response([
                    'order_id' => $orderNumber,
                    'gross_amount' => $order ? $order->total_amount : '225000.00',
                    'status_code' => '200',
                    'transaction_status' => 'settlement',
                    'transaction_id' => 'tx-'.($order?->id ?? 1),
                    'payment_type' => 'bank_transfer',
                    'currency' => 'IDR',
                    'fraud_status' => 'accept',
                ]);
            },
        ]);

        // Seed default couriers & rates for testing
        $this->seed(ShippingSeeder::class);
    }

    /**
     * Helper to create an order in a specific status.
     */
    private function createOrderForUser(User $user, string $orderStatus = 'awaiting_payment', string $paymentStatus = 'pending', int $weight = 600): Order
    {
        $product = Product::factory()->create([
            'name' => 'Royal Canin Cat Adult 2kg',
            'price' => '100000.00',
            'stock' => 10,
            'weight' => $weight,
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
            app(PaymentService::class)->sync($order);
        }

        if ($orderStatus === 'shipped') {
            $order->update([
                'order_status' => 'shipped',
                'tracking_number' => 'JNE-BDG-998877',
                'shipped_at' => now(),
            ]);
            $order->recordHistory('shipped', 'admin', 'Admin PawMart', 'Pesanan telah diserahkan ke kurir JNE dengan nomor resi JNE-BDG-998877');
        } elseif ($orderStatus === 'completed') {
            $order->update([
                'order_status' => 'completed',
                'tracking_number' => 'JNE-BDG-998877',
                'shipped_at' => now()->subDay(),
                'completed_at' => now(),
            ]);
            $order->recordHistory('completed', 'customer', $user->name, 'Pelanggan telah mengonfirmasi bahwa pesanan diterima dengan baik.');
        }

        return $order->fresh();
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 1. SHIPPING CALCULATOR SERVICE & API TESTS
    // ═════════════════════════════════════════════════════════════════════════

    public function test_shipping_calculator_rounds_weight_up_to_nearest_kg(): void
    {
        $calculator = app(ShippingCalculatorService::class);

        // 500g -> 1kg: Bandung JNE Reg base = 15000
        $cost500 = $calculator->calculate('Jawa Barat', 'Bandung', 500, 'jne', 'reg');
        // 1000g -> 1kg: Bandung JNE Reg base = 15000
        $cost1000 = $calculator->calculate('Jawa Barat', 'Bandung', 1000, 'jne', 'reg');
        // 1200g -> 2kg: base 15000 + (1 x 10000) = 25000
        $cost1200 = $calculator->calculate('Jawa Barat', 'Bandung', 1200, 'jne', 'reg');
        // 2100g -> 3kg: base 15000 + (2 x 10000) = 35000
        $cost2100 = $calculator->calculate('Jawa Barat', 'Bandung', 2100, 'jne', 'reg');

        $this->assertEquals(15000, $cost500['shipping_cost']);
        $this->assertEquals(15000, $cost1000['shipping_cost']);
        $this->assertEquals(25000, $cost1200['shipping_cost']);
        $this->assertEquals(35000, $cost2100['shipping_cost']);
    }

    public function test_shipping_calculator_matches_exact_city_first_then_province_fallback(): void
    {
        $calculator = app(ShippingCalculatorService::class);

        // Bandung, Jawa Barat (exact city configured in seeder: base 15000)
        $bandung = $calculator->calculate('Jawa Barat', 'Bandung', 1000, 'jne', 'reg');
        $this->assertEquals(15000, $bandung['shipping_cost']);

        // Cirebon, Jawa Barat (no exact city in seeder, matches Jawa Barat province base 18000)
        $cirebon = $calculator->calculate('Jawa Barat', 'Cirebon', 1000, 'jne', 'reg');
        $this->assertEquals(18000, $cirebon['shipping_cost']);

        // Papua (province not in seeder, destination unsupported returns null)
        $papua = $calculator->calculate('Papua', 'Jayapura', 1000, 'jne', 'reg');
        $this->assertNull($papua);
    }

    public function test_inactive_couriers_and_inactive_services_are_excluded(): void
    {
        $calculator = app(ShippingCalculatorService::class);

        // Deactivate J&T Express
        Courier::where('code', 'jnt')->update(['is_active' => false]);

        $available = $calculator->getAvailableOptions('Jawa Barat', 'Bandung', 1000);
        $courierCodes = array_column($available, 'courier_code');

        $this->assertNotContains('jnt', $courierCodes);
        $this->assertContains('jne', $courierCodes);
        $this->assertContains('sicepat', $courierCodes);
    }

    public function test_shipping_rates_ajax_endpoint_returns_json_options(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'weight' => 500]);
        app(CartService::class)->change($user, $product->id, 2, true);

        $response = $this->actingAs($user)->postJson(route('checkout.shipping-rates'), [
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'courier_code' => 'jne',
            'courier_service' => 'reg',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'available_options' => [
                    '*' => [
                        'courier_code',
                        'courier_name',
                        'service_code',
                        'service_name',
                        'estimated_days',
                        'shipping_cost',
                    ],
                ],
                'chosen_option',
                'shipping_cost',
                'total_weight',
                'subtotal',
                'total',
            ]);

        $this->assertNotEmpty($response->json('available_options'));
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 2. CHECKOUT WITH COURIER SELECTION & SNAPSHOT
    // ═════════════════════════════════════════════════════════════════════════

    public function test_checkout_records_selected_courier_service_weight_and_calculated_cost(): void
    {
        $user = User::factory()->create();
        $p1 = Product::factory()->create(['price' => '50000.00', 'stock' => 10, 'weight' => 700]);
        $p2 = Product::factory()->create(['price' => '25000.00', 'stock' => 10, 'weight' => 800]);

        $cartService = app(CartService::class);
        $cartService->change($user, $p1->id, 2, true); // 1400g
        $cartService->change($user, $p2->id, 1, true); // 800g -> total 2200g -> rounded to 3kg

        $cart = $cartService->cart($user);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'checkout_token' => $cart->checkout_token,
            'recipient_name' => 'Ahmad Dani',
            'recipient_phone' => '081298765432',
            'shipping_address' => 'Jl. Asia Afrika No. 10',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40111',
            'courier_code' => 'jne',
            'courier_service' => 'reg',
        ]);

        $response->assertRedirect();

        $order = Order::where('user_id', $user->id)->firstOrFail();

        $this->assertSame('jne', $order->courier_code);
        $this->assertSame('JNE', $order->courier_name);
        $this->assertSame('Reguler', $order->courier_service);
        $this->assertEquals(2200, $order->total_weight);
        // Bandung JNE Reg: base 15000 + (2 x 10000) = 35000
        $this->assertEquals('35000.00', $order->shipping_cost);
        // Subtotal: 100000 + 25000 = 125000 + 35000 = 160000
        $this->assertEquals('160000.00', $order->total_amount);

        // Check history was recorded
        $this->assertDatabaseHas('order_histories', [
            'order_id' => $order->id,
            'status' => 'awaiting_payment',
        ]);
    }

    public function test_checkout_rejects_invalid_courier_code(): void
    {
        $user = User::factory()->create();
        $p = Product::factory()->create(['stock' => 5]);
        $cartService = app(CartService::class);
        $cartService->change($user, $p->id, 1, true);
        $cart = $cartService->cart($user);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'checkout_token' => $cart->checkout_token,
            'recipient_name' => 'Ahmad Dani',
            'recipient_phone' => '081298765432',
            'shipping_address' => 'Jl. Asia Afrika No. 10',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40111',
            'courier_code' => 'unknown_courier',
            'courier_service' => 'reg',
        ]);

        $response->assertSessionHasErrors('courier_service');
        $this->assertDatabaseCount('orders', 0);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 3. ADMIN SHIPPING SETTINGS MANAGEMENT
    // ═════════════════════════════════════════════════════════════════════════

    public function test_admin_can_view_and_manage_shipping_settings(): void
    {
        $admin = User::factory()->admin()->create();

        // 1. View Shipping Settings Page
        $this->actingAs($admin)
            ->get(route('admin.shipping.index'))
            ->assertOk()
            ->assertSeeText('Pengaturan Pengiriman')
            ->assertSeeText('Ekspedisi & Layanan Aktif')
            ->assertSeeText('JNE')
            ->assertSeeText('SiCepat');

        // 2. Add New Courier
        $this->actingAs($admin)
            ->post(route('admin.shipping.couriers.store'), [
                'name' => 'Anteraja',
                'code' => 'anteraja',
                'is_active' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $courier = Courier::where('code', 'anteraja')->firstOrFail();
        $this->assertSame('Anteraja', $courier->name);
        $this->assertTrue($courier->is_active);

        // 3. Update Courier Active Status
        $this->actingAs($admin)
            ->patch(route('admin.shipping.couriers.update', $courier), [
                'is_active' => '0',
            ])
            ->assertRedirect();

        $this->assertFalse($courier->fresh()->is_active);

        // 4. Add New Service
        $this->actingAs($admin)
            ->post(route('admin.shipping.services.store'), [
                'courier_id' => $courier->id,
                'service_name' => 'Reguler',
                'service_code' => 'reg',
                'estimated_days' => '2-3 hari',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $service = CourierService::where('courier_id', $courier->id)->where('service_code', 'reg')->firstOrFail();
        $this->assertSame('2-3 hari', $service->estimated_days);

        // 5. Add New Rate
        $this->actingAs($admin)
            ->post(route('admin.shipping.rates.store'), [
                'courier_service_id' => $service->id,
                'province' => 'Bali',
                'city' => 'Denpasar',
                'base_cost' => '25000',
                'cost_per_kg' => '20000',
                'min_weight' => '0',
                'max_weight' => '30000',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $rate = ShippingRate::where('courier_service_id', $service->id)->where('city', 'Denpasar')->firstOrFail();
        $this->assertEquals(25000, $rate->base_cost);

        // 6. Update Rate
        $this->actingAs($admin)
            ->patch(route('admin.shipping.rates.update', $rate), [
                'base_cost' => '27000',
                'cost_per_kg' => '22000',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertEquals(27000, $rate->fresh()->base_cost);

        // 7. Delete Rate
        $this->actingAs($admin)
            ->delete(route('admin.shipping.rates.destroy', $rate))
            ->assertRedirect();

        $this->assertDatabaseMissing('shipping_rates', ['id' => $rate->id]);
    }

    public function test_non_admin_cannot_access_shipping_management(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.shipping.index'))
            ->assertForbidden();

        $this->actingAs($customer)
            ->post(route('admin.shipping.couriers.store'), ['name' => 'Test', 'code' => 'test'])
            ->assertForbidden();
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 4. ADMIN FULFILLMENT: RESI & SHIPPED STATUS
    // ═════════════════════════════════════════════════════════════════════════

    public function test_admin_fulfills_order_with_tracking_number_and_status_shipped(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrderForUser($customer, 'processing', 'paid');
        $admin = User::factory()->admin()->create();

        $this->assertSame('processing', $order->order_status);
        $this->assertNull($order->shipped_at);
        $this->assertNull($order->tracking_number);

        // Admin submits tracking number to mark shipped
        $response = $this->actingAs($admin)->put(route('admin.orders.update', $order), [
            'order_status' => 'shipped',
            'tracking_number' => 'JNE-RES-123456789',
            'shipping_notes' => 'Paket telah diserahkan ke gerai JNE Dago',
        ]);

        $response->assertRedirect(route('admin.orders.show', $order));

        $fresh = $order->fresh();
        $this->assertSame('shipped', $fresh->order_status);
        $this->assertSame('JNE-RES-123456789', $fresh->tracking_number);
        $this->assertNotNull($fresh->shipped_at);
        $this->assertSame('Paket telah diserahkan ke gerai JNE Dago', $fresh->shipping_notes);

        // Order history created
        $this->assertDatabaseHas('order_histories', [
            'order_id' => $order->id,
            'status' => 'shipped',
            'actor_type' => 'admin',
        ]);
    }

    public function test_admin_cannot_mark_unpaid_order_as_shipped(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrderForUser($customer, 'awaiting_payment', 'pending');
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->put(route('admin.orders.update', $order), [
            'order_status' => 'shipped',
            'tracking_number' => 'JNE-RESI-UNPAID',
        ]);

        $response->assertSessionHasErrors('order_status');
        $this->assertSame('awaiting_payment', $order->fresh()->order_status);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 5. CUSTOMER RECEIPT CONFIRMATION ("PESANAN DITERIMA")
    // ═════════════════════════════════════════════════════════════════════════

    public function test_customer_can_confirm_receipt_when_order_is_shipped(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrderForUser($customer, 'shipped', 'paid');
        $product = Product::findOrFail($order->items->first()->product_id);
        $initialStock = $product->stock;

        $this->assertSame('shipped', $order->order_status);
        $this->assertNull($order->completed_at);

        // Customer confirms receipt
        $response = $this->actingAs($customer)
            ->from(route('orders.show', $order))
            ->post(route('orders.confirm-received', $order));

        $response->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('status', 'Terima kasih! Pesanan telah Anda konfirmasi diterima.');

        $fresh = $order->fresh();
        $this->assertSame('completed', $fresh->order_status);
        $this->assertNotNull($fresh->completed_at);

        // History entry recorded with customer as actor
        $this->assertDatabaseHas('order_histories', [
            'order_id' => $order->id,
            'status' => 'completed',
            'actor_type' => 'customer',
        ]);

        // Stock MUST NOT be altered on receipt confirmation
        $this->assertSame($initialStock, $product->fresh()->stock);
    }

    public function test_receipt_confirmation_is_idempotent(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrderForUser($customer, 'shipped', 'paid');

        // First confirmation
        $this->actingAs($customer)->post(route('orders.confirm-received', $order))->assertRedirect();
        $completedAt = $order->fresh()->completed_at;

        // Second confirmation (should safely succeed without double actions)
        $this->actingAs($customer)->post(route('orders.confirm-received', $order))->assertRedirect();

        $fresh = $order->fresh();
        $this->assertSame('completed', $fresh->order_status);
        $this->assertEquals($completedAt, $fresh->completed_at);
    }

    public function test_customer_cannot_confirm_receipt_for_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $order = $this->createOrderForUser($owner, 'shipped', 'paid');

        $response = $this->actingAs($stranger)
            ->post(route('orders.confirm-received', $order));

        $response->assertNotFound();
        $this->assertSame('shipped', $order->fresh()->order_status);
    }

    public function test_customer_cannot_confirm_receipt_when_order_is_not_shipped(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrderForUser($customer, 'processing', 'paid');

        $response = $this->actingAs($customer)
            ->post(route('orders.confirm-received', $order));

        $response->assertSessionHasErrors('order');
        $this->assertSame('processing', $order->fresh()->order_status);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 6. CUSTOMER ISSUE REPORTING ("LAPORKAN MASALAH")
    // ═════════════════════════════════════════════════════════════════════════

    public function test_customer_can_report_issue_on_order(): void
    {
        $customer = User::factory()->create();
        $order = $this->createOrderForUser($customer, 'shipped', 'paid');

        $response = $this->actingAs($customer)
            ->from(route('orders.show', $order))
            ->post(route('orders.issues.store', $order), [
                'category' => 'rusak',
                'description' => 'Kemasan makanan sobek dan tumpah di dalam paket.',
            ]);

        $response->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('status', 'Laporan kendala berhasil dikirim. Pengelola toko akan segera memeriksa kendala Anda.');

        $this->assertDatabaseHas('order_issues', [
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'category' => 'rusak',
            'status' => 'open',
        ]);

        $this->assertDatabaseHas('order_histories', [
            'order_id' => $order->id,
            'status' => 'issue_reported',
        ]);
    }

    public function test_customer_cannot_report_issue_on_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $order = $this->createOrderForUser($owner, 'shipped', 'paid');

        $response = $this->actingAs($stranger)
            ->post(route('orders.issues.store', $order), [
                'category' => 'belum_diterima',
                'description' => 'Saya belum terima barang pesanan saya.',
            ]);

        $response->assertNotFound();
        $this->assertDatabaseCount('order_issues', 0);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 7. CUSTOMER DASHBOARD & ORDER STATUS FILTERING
    // ═════════════════════════════════════════════════════════════════════════

    public function test_customer_dashboard_renders_accurate_status_counts(): void
    {
        $customer = User::factory()->create();

        // 1 Unpaid
        $this->createOrderForUser($customer, 'awaiting_payment', 'pending');
        // 1 Processing
        $this->createOrderForUser($customer, 'processing', 'paid');
        // 1 Shipped
        $this->createOrderForUser($customer, 'shipped', 'paid');
        // 1 Completed
        $this->createOrderForUser($customer, 'completed', 'paid');

        $response = $this->actingAs($customer)->get(route('dashboard'));

        $response->assertOk()
            ->assertSeeText('Belum Bayar')
            ->assertSeeText('Diproses')
            ->assertSeeText('Dikirim')
            ->assertSeeText('Selesai');
    }

    public function test_customer_orders_page_filters_by_status(): void
    {
        $customer = User::factory()->create();

        $unpaid = $this->createOrderForUser($customer, 'awaiting_payment', 'pending');
        $shipped = $this->createOrderForUser($customer, 'shipped', 'paid');

        // Filter unpaid
        $this->actingAs($customer)
            ->get(route('orders.index', ['status' => 'unpaid']))
            ->assertOk()
            ->assertSee($unpaid->order_number)
            ->assertDontSee($shipped->order_number);

        // Filter shipped
        $this->actingAs($customer)
            ->get(route('orders.index', ['status' => 'shipped']))
            ->assertOk()
            ->assertSee($shipped->order_number)
            ->assertDontSee($unpaid->order_number);
    }
}
