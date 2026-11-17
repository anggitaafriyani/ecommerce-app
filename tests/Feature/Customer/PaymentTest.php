<?php

namespace Tests\Feature\Customer;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\MidtransClient;
use App\Services\StockReservationService;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\UsesMemoryDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use UsesMemoryDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['midtrans.environment' => 'sandbox', 'midtrans.server_key' => 'SB-Mid-server-test-only', 'midtrans.client_key' => 'SB-Mid-client-test-only', 'midtrans.signature_secret_key' => 'sandbox-signing-test-only']);
        Http::preventStrayRequests();
    }

    private function order(): array
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => '25000.00', 'stock' => 5]);
        $this->actingAs($user)->post(route('cart.store', $product), ['quantity' => 2]);
        $cart = Cart::where('user_id', $user->id)->firstOrFail();
        $this->post('/checkout', ['checkout_token' => $cart->checkout_token, 'recipient_name' => 'Pelanggan', 'recipient_phone' => '081234567890', 'shipping_address' => 'Jalan Pengujian', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40111'])->assertRedirect();

        return [Order::where('user_id', $user->id)->firstOrFail(), $product, $user];
    }

    private function started(): array
    {
        $data = $this->order();
        Http::fake([MidtransClient::SNAP => Http::response(['token' => 'test-snap-token'])]);
        $this->postJson(route('payments.start', $data[0]))->assertOk()->assertJsonPath('token', 'test-snap-token');

        return $data;
    }

    private function gatewayStatus(Order $order, string $status = 'settlement', array $extra = []): array
    {
        return array_replace(['order_id' => $order->order_number, 'gross_amount' => $order->total_amount, 'status_code' => in_array($status, ['deny', 'expire', 'cancel', 'failure']) ? '202' : ($status === 'pending' ? '201' : '200'), 'transaction_status' => $status, 'transaction_id' => 'tx-'.$order->id, 'payment_type' => 'bank_transfer', 'currency' => 'IDR'], $extra);
    }

    private function fakeStatus(array $status): void
    {
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake([MidtransClient::API.'*' => Http::response($status)]);
    }

    private function notify(array $data): TestResponse
    {
        $data['signature_key'] = hash('sha512', $data['order_id'].$data['status_code'].$data['gross_amount'].config('midtrans.signature_secret_key'));

        return $this->postJson(route('midtrans.notification'), $data);
    }

    public function test_token_uses_snapshot_server_amount_expiry_and_is_reused(): void
    {
        [$order,$product] = $this->started();
        $this->postJson(route('payments.start', $order), ['gross_amount' => 1, 'payment_status' => 'paid'])->assertOk()->assertJsonPath('token', 'test-snap-token');
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['transaction_details']['gross_amount'] === 65000 && $request['transaction_details']['order_id'] === $order->order_number && $request['item_details'][0]['price'] === 25000 && $request['expiry']['duration'] === 60 && $request['expiry']['start_time'] === $order->created_at->copy()->timezone('Asia/Jakarta')->format('Y-m-d H:i:s O') && $request['callbacks']['finish'] === route('payments.show', $order));
        $this->assertDatabaseCount('payments', 1);
        $this->assertSame('65000.00', $order->payment->gross_amount);
        $this->assertSame(3, $product->fresh()->stock);
    }

    #[TestWith(['pending', 'pending', 3])] #[TestWith(['settlement', 'paid', 3])] #[TestWith(['deny', 'failed', 5])] #[TestWith(['failure', 'failed', 5])] #[TestWith(['expire', 'expired', 5])] #[TestWith(['cancel', 'cancelled', 5])]
    public function test_verified_status_updates_order_and_stock_once(string $gateway, string $expected, int $stock): void
    {
        [$order,$product] = $this->started();
        $data = $this->gatewayStatus($order, $gateway);
        $this->fakeStatus($data);
        $this->notify($data)->assertOk();
        $this->notify($data)->assertOk();
        $order->refresh();
        $this->assertSame($expected, $order->payment_status);
        $this->assertSame($stock, $product->fresh()->stock);
        $this->assertSame($expected === 'paid' ? 'processing' : ($expected === 'pending' ? 'awaiting_payment' : 'cancelled'), $order->order_status);
        $this->assertSame($expected === 'pending', $order->stock_reserved);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('payment_events', 2);
    }

    public function test_invalid_signature_and_amount_are_rejected_without_gateway_requests(): void
    {
        [$order,$product] = $this->started();
        Http::fake();
        $data = $this->gatewayStatus($order);
        $this->postJson(route('midtrans.notification'), $data + ['signature_key' => str_repeat('a', 128)])->assertForbidden();
        $data['gross_amount'] = '1.00';
        $this->notify($data)->assertUnprocessable();
        Http::assertNothingSent();
        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_webhook_fields_do_not_override_current_gateway_status(): void
    {
        [$order,$product] = $this->started();
        $this->fakeStatus($this->gatewayStatus($order, 'pending'));
        $this->notify($this->gatewayStatus($order, 'settlement'))->assertOk();
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_server_key_signature_is_rejected_when_separate_secret_is_required(): void
    {
        [$order, $product] = $this->started();
        $data = $this->gatewayStatus($order);
        $this->fakeStatus($data);
        $data['signature_key'] = hash('sha512', $data['order_id'].$data['status_code'].$data['gross_amount'].config('midtrans.server_key'));
        $this->postJson(route('midtrans.notification'), $data)->assertForbidden();
        Http::assertNothingSent();
        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertDatabaseCount('payment_events', 0);
    }

    #[TestWith([''])]
    #[TestWith([null])]
    #[TestWith(['   '])]
    public function test_missing_signature_secret_rejects_webhook_without_api_or_stock_changes(?string $secret): void
    {
        [$order, $product] = $this->started();
        $data = $this->gatewayStatus($order);
        $this->fakeStatus($data);
        config(['midtrans.signature_secret_key' => $secret]);
        $this->notify($data)->assertStatus(503);
        Http::assertNothingSent();
        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertDatabaseCount('payment_events', 0);
    }

    public function test_signature_secret_does_not_enable_production_webhooks(): void
    {
        [$order] = $this->started();
        $data = $this->gatewayStatus($order);
        $this->fakeStatus($data);
        config(['midtrans.environment' => 'production']);
        $this->notify($data)->assertStatus(503);
        Http::assertNothingSent();
        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->assertDatabaseCount('payment_events', 0);
    }

    #[TestWith(['capture', 'accept', 'paid'])] #[TestWith(['capture', 'challenge', 'pending'])] #[TestWith(['capture', 'deny', 'pending'])] #[TestWith(['capture', null, 'pending'])] #[TestWith(['settlement', 'deny', 'pending'])]
    public function test_fraud_status_is_verified(string $status, ?string $fraud, string $expected): void
    {
        [$order] = $this->started();
        $data = $this->gatewayStatus($order, $status, ['fraud_status' => $fraud]);
        $this->fakeStatus($data);
        $this->notify($data)->assertOk();
        $this->assertSame($expected, $order->fresh()->payment_status);
    }

    public function test_incorrect_status_code_cannot_mark_paid(): void
    {
        [$order] = $this->started();
        $data = $this->gatewayStatus($order, 'settlement', ['status_code' => '500']);
        $this->fakeStatus($data);
        $this->notify($data)->assertOk();
        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->assertNotNull($order->payment->review_reason);
    }

    public function test_owner_is_required_for_payment_page_start_refresh_and_history(): void
    {
        [$order,$product,$owner] = $this->order();
        $this->actingAs(User::factory()->create());
        $this->get(route('payments.show', $order))->assertNotFound();
        $this->postJson(route('payments.start', $order))->assertNotFound();
        $this->post(route('payments.refresh', $order))->assertNotFound();
        $this->get(route('orders.index'))->assertOk()->assertDontSee($order->order_number);
        $this->actingAs(User::factory()->admin()->create())->postJson(route('payments.start', $order))->assertForbidden();
        $this->patchJson('/orders/'.$order->id, ['payment_status' => 'paid'])->assertMethodNotAllowed();
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_payment_page_ignores_javascript_return_parameters_and_hides_server_key(): void
    {
        [$order] = $this->order();
        $this->get(route('payments.show', $order).'?transaction_status=settlement&status_code=200')->assertOk()->assertSee('Menunggu pembayaran')->assertSee('Bayar Sekarang')->assertDontSee(config('midtrans.server_key'))->assertSee('Kembali ke Riwayat Pesanan');
        $this->assertSame('pending', $order->fresh()->payment_status);
        Http::assertNothingSent();
        $this->post(route('payments.refresh', $order))->assertRedirect(route('payments.show', $order))->assertSessionHas('status');
    }

    public function test_missing_or_production_keys_disable_payment_without_creating_record(): void
    {
        [$order] = $this->order();
        config(['midtrans.server_key' => '']);
        $this->get(route('payments.show', $order))->assertOk()->assertSee('belum dikonfigurasi')->assertDontSee('id="pay-now"', false);
        $this->postJson(route('payments.start', $order))->assertUnprocessable();
        config(['midtrans.server_key' => 'Mid-server-production', 'midtrans.environment' => 'production']);
        $this->postJson(route('payments.start', $order))->assertUnprocessable();
        $this->assertDatabaseCount('payments', 0);
        Http::assertNothingSent();
    }

    public function test_timeout_does_not_resubmit_uncertain_creation_or_release_stock(): void
    {
        [$order,$product] = $this->order();
        Http::fake([MidtransClient::SNAP => Http::failedConnection()]);
        $this->postJson(route('payments.start', $order))->assertUnprocessable();
        $this->postJson(route('payments.start', $order))->assertUnprocessable();
        $this->assertDatabaseCount('payments', 1);
        $this->assertSame('creation_uncertain', $order->payment->review_reason);
        $this->travel(61)->minutes();
        Http::fake([MidtransClient::API.'*' => Http::response(['status_code' => '404'], 404)]);
        $this->artisan('orders:expire')->assertSuccessful();
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_scheduler_reconciles_paid_payment_before_releasing_stock(): void
    {
        [$order,$product] = $this->started();
        $this->travel(61)->minutes();
        $this->fakeStatus($this->gatewayStatus($order));
        $this->artisan('orders:expire')->assertSuccessful();
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_scheduler_holds_stock_during_gateway_outage(): void
    {
        [$order,$product] = $this->started();
        $this->travel(61)->minutes();
        Http::fake([MidtransClient::API.'*' => Http::response([], 503)]);
        $this->artisan('orders:expire')->assertSuccessful();
        $this->assertTrue($order->fresh()->stock_reserved);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_cancel_reconciles_gateway_and_releases_only_after_terminal_confirmation(): void
    {
        [$order,$product] = $this->started();
        Http::fake([MidtransClient::API.'*' => Http::sequence()->push($this->gatewayStatus($order, 'pending'))->push(['status_code' => '200'])->push($this->gatewayStatus($order, 'cancel'))]);
        $this->post(route('orders.cancel', $order))->assertSessionHasNoErrors();
        $this->assertSame('cancelled', $order->fresh()->payment_status);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_payment_winning_cancellation_race_is_not_released(): void
    {
        [$order,$product] = $this->started();
        Http::fake([MidtransClient::API.'*' => Http::sequence()->push($this->gatewayStatus($order, 'pending'))->push(['status_code' => '200'])->push($this->gatewayStatus($order, 'settlement'))]);
        $this->post(route('orders.cancel', $order))->assertSessionHasNoErrors();
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('payment_review', $order->fresh()->order_status);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_unselected_snap_session_is_closed_before_stock_release(): void
    {
        [$order,$product] = $this->started();
        Http::fake([MidtransClient::API.'*' => Http::response(['status_code' => '404'], 404), MidtransClient::SNAP.'/*/cancel' => Http::response(['canceled_at' => '2026-11-16T00:00:00Z'])]);
        $this->post(route('orders.cancel', $order))->assertSessionHasNoErrors();
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertSame('cancelled', $order->fresh()->payment_status);
        Http::assertSentCount(3);
    }

    public function test_unconfirmed_session_cancellation_holds_stock(): void
    {
        [$order,$product] = $this->started();
        Http::fake([MidtransClient::API.'*' => Http::response(['status_code' => '404'], 404), MidtransClient::SNAP.'/*/cancel' => Http::response(['error_messages' => ['Transaction is on progress']], 400)]);
        $this->post(route('orders.cancel', $order))->assertSessionHasErrors('payment');
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertTrue($order->fresh()->stock_reserved);
    }

    public function test_late_paid_after_release_requires_manual_handling_without_reserving_again(): void
    {
        [$order,$product] = $this->started();
        $expired = $this->gatewayStatus($order, 'expire');
        $this->fakeStatus($expired);
        $this->notify($expired)->assertOk();
        $this->assertSame(5, $product->fresh()->stock);
        $paid = $this->gatewayStatus($order);
        $this->fakeStatus($paid);
        $this->notify($paid)->assertOk();
        $this->notify($paid)->assertOk();
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('payment_review', $order->fresh()->order_status);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_paid_cannot_be_downgraded_by_pending_or_cancel(): void
    {
        [$order,$product] = $this->started();
        foreach (['settlement', 'pending', 'cancel'] as $status) {
            $data = $this->gatewayStatus($order, $status);
            $this->fakeStatus($data);
            $this->notify($data)->assertOk();
        }
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('payment_review', $order->fresh()->order_status);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_status_button_uses_gateway_and_bad_response_does_not_change_order(): void
    {
        [$order,$product] = $this->started();
        $this->fakeStatus($this->gatewayStatus($order, 'settlement', ['gross_amount' => '42.00']));
        $this->post(route('payments.refresh', $order))->assertSessionHasErrors('payment');
        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->fakeStatus($this->gatewayStatus($order));
        $this->post(route('payments.refresh', $order))->assertRedirect(route('payments.show', $order));
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_expired_order_and_fractional_rupiah_are_not_sent_to_midtrans(): void
    {
        [$order] = $this->order();
        $order->update(['total_amount' => '65000.25']);
        $this->postJson(route('payments.start', $order))->assertUnprocessable();
        $order->update(['total_amount' => '65000.00', 'expires_at' => now()->subMinute()]);
        $this->postJson(route('payments.start', $order))->assertUnprocessable();
        Http::assertNothingSent();
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_direct_stock_release_is_blocked_after_payment_attempt(): void
    {
        [$order,$product] = $this->started();
        $this->expectException(ValidationException::class);
        app(StockReservationService::class)->release($order);
    }

    public function test_unreachable_gateway_returns_retryable_webhook_response(): void
    {
        [$order] = $this->started();
        Http::fake([MidtransClient::API.'*' => Http::failedConnection()]);
        $this->notify($this->gatewayStatus($order))->assertStatus(503);
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_webhook_retries_when_gateway_does_not_yet_find_transaction(): void
    {
        [$order] = $this->started();
        Http::fake([MidtransClient::API.'*' => Http::response(['status_code' => '404'], 404)]);
        $this->notify($this->gatewayStatus($order))->assertStatus(503);
        $this->assertTrue($order->fresh()->stock_reserved);
    }

    public function test_scheduler_expires_pending_gateway_transaction_before_releasing_stock(): void
    {
        [$order,$product] = $this->started();
        $this->travel(61)->minutes();
        Http::fake([MidtransClient::API.'*' => Http::sequence()->push($this->gatewayStatus($order, 'pending'))->push(['status_code' => '200'])->push($this->gatewayStatus($order, 'expire'))]);
        $this->artisan('orders:expire')->assertSuccessful();
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/expire') && $request->method() === 'POST');
        $this->assertSame('expired', $order->fresh()->payment_status);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_unknown_order_and_changed_transaction_identity_are_rejected(): void
    {
        [$order] = $this->started();
        $unknown = $this->gatewayStatus($order, 'pending', ['order_id' => 'PWM-UNKNOWN']);
        $this->notify($unknown)->assertNotFound();
        $this->fakeStatus($this->gatewayStatus($order, 'pending'));
        $this->notify($this->gatewayStatus($order, 'pending'))->assertOk();
        $changed = $this->gatewayStatus($order, 'settlement', ['transaction_id' => 'unexpected-new-transaction']);
        $this->fakeStatus($changed);
        $this->notify($changed)->assertStatus(503);
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    #[TestWith([2])] #[TestWith([10081])]
    public function test_unsupported_reservation_duration_is_not_sent(int $minutes): void
    {
        [$order] = $this->order();
        $order->update(['expires_at' => $order->created_at->copy()->addMinutes($minutes)]);
        $this->postJson(route('payments.start', $order))->assertUnprocessable();
        Http::assertNothingSent();
        $this->assertDatabaseCount('payments', 0);
    }

    #[TestWith(['Mid-client-test-only'])]
    #[TestWith(['SB-Mid-client-test-only'])]
    public function test_dashboard_client_key_formats_use_sandbox_authentication(string $clientKey): void
    {
        config(['midtrans.client_key' => $clientKey]);
        [$order] = $this->order();
        $this->get(route('payments.show', $order))->assertOk()->assertSee('Bayar Sekarang')->assertDontSee('belum dikonfigurasi');
        Http::fake([MidtransClient::SNAP => Http::response(['token' => 'test-snap-token'])]);
        $this->postJson(route('payments.start', $order))->assertOk();
        Http::assertSent(fn ($request) => $request->url() === MidtransClient::SNAP && $request->hasHeader('Authorization', 'Basic '.base64_encode(config('midtrans.server_key').':')));
        $data = $this->gatewayStatus($order);
        $this->fakeStatus($data);
        $this->postJson(route('midtrans.notification'), $data + ['signature_key' => str_repeat('0', 128)])->assertForbidden();
        $this->notify($data)->assertOk();
        Http::assertSent(fn ($request) => str_starts_with($request->url(), MidtransClient::API) && $request->hasHeader('Authorization', 'Basic '.base64_encode(config('midtrans.server_key').':')));
    }

    #[TestWith(['client_key', ''])]
    #[TestWith(['client_key', '   '])]
    #[TestWith(['server_key', "test\nkey"])]
    #[TestWith(['server_key', null])]
    public function test_missing_or_malformed_credentials_are_not_configured(string $field, mixed $value): void
    {
        config(['midtrans.'.$field => $value]);
        $this->assertFalse(app(MidtransClient::class)->configured());
        Http::assertNothingSent();
    }

    public function test_key_presence_never_bypasses_midtrans_authentication(): void
    {
        config(['midtrans.client_key' => 'Mid-client-test-only', 'midtrans.server_key' => 'opaque-test-only']);
        [$order] = $this->order();
        Http::fake([MidtransClient::SNAP => Http::response(['status_code' => '401'], 401)]);
        $this->postJson(route('payments.start', $order))->assertUnprocessable();
        $this->assertNull($order->fresh()->snap_token);
        $this->assertSame('pending', $order->fresh()->payment_status);
        Http::assertSentCount(1);
    }
}
