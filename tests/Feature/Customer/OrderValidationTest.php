<?php

namespace Tests\Feature\Customer;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\UsesMemoryDatabase;
use Tests\TestCase;

class OrderValidationTest extends TestCase
{
    use UsesMemoryDatabase;

    #[TestWith(['recipient_name', 101])] #[TestWith(['shipping_address', 1001])] #[TestWith(['city', 101])] #[TestWith(['province', 101])] #[TestWith(['notes', 1001])]
    public function test_address_length_limits_are_enforced(string $field, int $length): void
    {
        $this->actingAs(User::factory()->create())->post('/checkout', [$field => str_repeat('a', $length)])->assertSessionHasErrors($field);
        $this->assertDatabaseCount('orders', 0);
    }

    #[TestWith(['recipient_phone', 'abc'])] #[TestWith(['postal_code', '1234'])] #[TestWith(['postal_code', '123456'])] #[TestWith(['postal_code', 'abcde'])] #[TestWith(['checkout_token', 'invalid'])]
    public function test_invalid_phone_postcode_or_token_is_rejected(string $field, string $value): void
    {
        $this->actingAs(User::factory()->create())->post('/checkout', [$field => $value])->assertSessionHasErrors($field);
    }

    public function test_checkout_rejects_total_exceeding_database_precision_without_changing_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => '9999999999.99', 'stock' => 2147483647]);
        $this->actingAs($user)->post(route('cart.store', $product), ['quantity' => 2147483647]);
        $cart = Cart::where('user_id', $user->id)->firstOrFail();
        $this->post('/checkout', ['checkout_token' => $cart->checkout_token, 'recipient_name' => 'Uji batas', 'recipient_phone' => '081234567890', 'shipping_address' => 'Alamat', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40111'])->assertSessionHasErrors('cart');
        $this->assertSame(2147483647, $product->fresh()->stock);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_external_login_redirect_is_ignored(): void
    {
        $user = User::factory()->create();
        $this->withSession(['url.intended' => 'https://example.com/cart'])->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/dashboard');
    }

    public function test_guest_can_resume_checkout_after_login(): void
    {
        $user = User::factory()->create();
        $this->get('/checkout')->assertRedirect('/login');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/checkout');
    }

    public function test_non_numeric_record_identifiers_return_not_found(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post('/cart/not-an-id', ['quantity' => 1])->assertNotFound();
        $this->get('/orders/not-an-id')->assertNotFound();
    }
}
