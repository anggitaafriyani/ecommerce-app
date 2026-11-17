<?php

namespace Tests\Feature\Customer;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\UsesMemoryDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use UsesMemoryDatabase;

    private function address(string $token): array
    {
        return ['checkout_token' => $token, 'recipient_name' => 'Pelanggan PawMart', 'recipient_phone' => '081234567890', 'shipping_address' => 'Jalan Melati 12', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40111', 'notes' => 'Titip di resepsionis'];
    }

    private function prepareCart(int $stock = 5, int $quantity = 2): array
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => $stock, 'price' => '12345.67']);
        $this->actingAs($user)->post(route('cart.store', $product), ['quantity' => $quantity])->assertRedirect(route('cart.index'));

        return [$user, $product, Cart::where('user_id', $user->id)->firstOrFail()];
    }

    public function test_cart_add_merges_updates_removes_and_persists_per_account(): void
    {
        [$user,$product] = $this->prepareCart();
        $this->post(route('cart.store', $product), ['quantity' => 1])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 3]);
        $this->assertDatabaseCount('cart_items', 1);
        $this->get('/cart')->assertOk()->assertSee($product->name)->assertSee('Rp37.037,01');
        $this->patch(route('cart.update', $product), ['quantity' => 1])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cart_items', ['quantity' => 1]);
        $this->delete(route('cart.destroy', $product))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('cart_items', 0);
        $this->get('/cart')->assertSee('Keranjang masih kosong');
    }

    #[TestWith([0])] #[TestWith([-1])] #[TestWith([6])] #[TestWith(['abc'])] #[TestWith([1.5])]
    public function test_invalid_quantity_cannot_change_cart(mixed $quantity): void
    {
        [$user,$product] = $this->prepareCart();
        $this->post(route('cart.store', $product), ['quantity' => $quantity])->assertSessionHasErrors();
        $this->patch(route('cart.update', $product), ['quantity' => $quantity])->assertSessionHasErrors();
        $this->assertDatabaseHas('cart_items', ['quantity' => 2]);
    }

    #[TestWith(['inactive'])] #[TestWith(['archived'])] #[TestWith(['stock'])]
    public function test_unavailable_product_cannot_be_added_or_checked_out(string $reason): void
    {
        [$user,$product,$cart] = $this->prepareCart();
        match ($reason) {
            'inactive' => $product->update(['is_active' => false]),'archived' => $product->delete(),'stock' => $product->update(['stock' => 0])
        };
        $this->post(route('cart.store', $product), ['quantity' => 1])->assertSessionHasErrors('cart');
        $this->get('/checkout')->assertRedirect('/cart');
        $this->post('/checkout', $this->address($cart->checkout_token))->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_checkout_recomputes_totals_and_saves_snapshots_and_reserves_stock(): void
    {
        [$user,$product,$cart] = $this->prepareCart();
        $this->get('/checkout')->assertOk()->assertSee('Rp39.691,34');
        $product->update(['price' => '15000.25']);
        $this->post('/checkout', $this->address($cart->checkout_token) + ['total_amount' => 1, 'shipping_cost' => 0, 'payment_status' => 'success', 'user_id' => 999])->assertRedirect();
        $order = Order::firstOrFail();
        $this->assertSame('45000.50', $order->total_amount);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('awaiting_payment', $order->order_status);
        $this->assertSame($user->id, $order->user_id);
        $this->assertTrue($order->stock_reserved);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'product_name' => $product->name, 'product_price' => 15000.25, 'quantity' => 2, 'subtotal' => 30000.50]);
        $this->assertDatabaseCount('cart_items', 0);
        $this->get(route('orders.show', $order))->assertOk()->assertSee('Status pembayaran diperbarui melalui verifikasi server Midtrans');
        $product->update(['name' => 'Nama diubah', 'price' => '1.00']);
        $product->delete();
        $this->get(route('orders.show', $order))->assertOk()->assertDontSee('Nama diubah')->assertSee('Rp15.000,25');
    }

    public function test_duplicate_submission_returns_same_order_and_reserves_once(): void
    {
        [$user,$product,$cart] = $this->prepareCart();
        $data = $this->address($cart->checkout_token);
        $this->post('/checkout', $data)->assertRedirect();
        $order = Order::firstOrFail();
        $this->post('/checkout', $data)->assertRedirect(route('payments.show', $order));
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_checkout_requires_valid_address_and_token(): void
    {
        $this->prepareCart();
        $this->post('/checkout', [])->assertSessionHasErrors(['checkout_token', 'recipient_name', 'recipient_phone', 'shipping_address', 'city', 'province', 'postal_code']);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_stale_token_and_empty_cart_are_rejected(): void
    {
        [$user,$product,$cart] = $this->prepareCart();
        $this->patch(route('cart.update', $product), ['quantity' => 1]);
        $this->post('/checkout', $this->address($cart->checkout_token))->assertSessionHasErrors('cart');
        $this->delete(route('cart.destroy', $product));
        $this->post('/checkout', $this->address($cart->fresh()->checkout_token))->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_customers_cannot_read_or_mutate_other_customers_cart_or_order(): void
    {
        [$owner,$product,$cart] = $this->prepareCart();
        $other = User::factory()->create();
        $this->actingAs($other)->get('/cart')->assertOk()->assertDontSee($product->name);
        $this->patch(route('cart.update', $product), ['quantity' => 1])->assertNotFound();
        $this->delete(route('cart.destroy', $product))->assertNotFound();
        $this->post('/checkout', $this->address($cart->checkout_token))->assertSessionHasErrors();
        $this->actingAs($owner)->post('/checkout', $this->address($cart->checkout_token));
        $order = Order::firstOrFail();
        $this->actingAs($other)->get(route('orders.show', $order))->assertNotFound();
        $this->post(route('orders.cancel', $order))->assertNotFound();
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_guest_and_admin_cannot_use_transaction_routes(): void
    {
        $product = Product::factory()->create();
        foreach (['/cart', '/checkout', '/orders/1'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
        $this->post(route('cart.store', $product), ['quantity' => 1])->assertRedirect('/login');
        $this->actingAs(User::factory()->admin()->create());
        foreach (['/cart', '/checkout', '/orders/1'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->post(route('cart.store', $product), ['quantity' => 1])->assertForbidden();
        $this->post('/checkout', [])->assertForbidden();
    }

    public function test_guest_returns_to_product_after_login_without_automatic_purchase(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();
        $this->get(route('shop.login', $product->slug))->assertRedirect('/login');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('products.show', $product->slug));
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cancellation_returns_stock_exactly_once_even_for_archived_product(): void
    {
        [$user,$product,$cart] = $this->prepareCart();
        $this->post('/checkout', $this->address($cart->checkout_token));
        $order = Order::firstOrFail();
        $product->delete();
        $this->post(route('orders.cancel', $order))->assertSessionHasNoErrors();
        $this->post(route('orders.cancel', $order))->assertSessionHasNoErrors();
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertNotNull($order->fresh()->stock_released_at);
        $this->assertSame('cancelled', $order->fresh()->order_status);
    }

    public function test_expiry_command_releases_only_due_pending_reservations_once(): void
    {
        [$user,$product,$cart] = $this->prepareCart();
        $this->post('/checkout', $this->address($cart->checkout_token));
        $order = Order::firstOrFail();
        $this->artisan('orders:expire')->assertSuccessful();
        $this->assertSame(3, $product->fresh()->stock);
        $this->travel(61)->minutes();
        $this->artisan('orders:expire')->assertSuccessful();
        $this->artisan('orders:expire')->assertSuccessful();
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertSame('expired', $order->fresh()->payment_status);
    }

    public function test_paid_order_cannot_release_reserved_stock(): void
    {
        [$user,$product,$cart] = $this->prepareCart();
        $this->post('/checkout', $this->address($cart->checkout_token));
        $order = Order::firstOrFail();
        $order->update(['payment_status' => 'success']);
        $this->post(route('orders.cancel', $order))->assertSessionHasErrors('order');
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_second_customer_cannot_buy_stock_already_reserved(): void
    {
        [$user,$product,$cart] = $this->prepareCart(2, 2);
        $other = User::factory()->create();
        app(CartService::class)->change($other, $product->id, 2, true);
        $this->post('/checkout', $this->address($cart->checkout_token));
        $otherCart = Cart::where('user_id', $other->id)->firstOrFail();
        $this->actingAs($other)->post('/checkout', $this->address($otherCart->checkout_token))->assertSessionHasErrors('cart');
        $this->assertSame(0, $product->fresh()->stock);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_one_invalid_item_rolls_back_whole_order(): void
    {
        [$user,$product,$cart] = $this->prepareCart();
        $second = Product::factory()->create();
        app(CartService::class)->change($user, $second->id, 1, true);
        $second->update(['stock' => 0]);
        $this->post('/checkout', $this->address($cart->fresh()->checkout_token))->assertSessionHasErrors('cart');
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('cart_items', 2);
    }

    public function test_account_with_orders_cannot_delete_transaction_history(): void
    {
        [$user,$product,$cart] = $this->prepareCart();
        $this->post('/checkout', $this->address($cart->checkout_token));
        $this->delete('/profile', ['password' => 'password'])->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseCount('orders', 1);
    }
}
