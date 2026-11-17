<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\UsesMemoryDatabase;
use Tests\TestCase;

class FinalAuditTest extends TestCase
{
    use UsesMemoryDatabase;

    public function test_profile_phone_can_be_saved_preserved_and_cleared_without_role_changes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('profile.edit'))->assertOk()->assertSee('Nomor telepon');
        $base = ['name' => $user->name, 'email' => $user->email];
        $this->patch(route('profile.update'), $base + ['phone' => '+628123456789', 'role' => 'admin'])->assertSessionHasNoErrors();
        $this->assertSame('+628123456789', $user->fresh()->phone);
        $this->assertSame('customer', $user->fresh()->role);
        $this->patch(route('profile.update'), $base)->assertSessionHasNoErrors();
        $this->assertSame('+628123456789', $user->fresh()->phone);
        $this->patch(route('profile.update'), $base + ['phone' => ''])->assertSessionHasNoErrors();
        $this->assertNull($user->fresh()->phone);
    }

    #[TestWith(['abc'])]
    #[TestWith(['123'])]
    #[TestWith(['0812345678901234567890123'])]
    #[TestWith([['081234567890']])]
    public function test_invalid_profile_phone_cannot_overwrite_saved_number(mixed $phone): void
    {
        $user = User::factory()->create(['phone' => '081234567890']);
        $this->actingAs($user)->patch(route('profile.update'), ['name' => $user->name, 'email' => $user->email, 'phone' => $phone])->assertSessionHasErrors('phone');
        $this->assertSame('081234567890', $user->fresh()->phone);
    }

    public function test_zero_search_is_not_treated_as_an_empty_filter(): void
    {
        $matching = Product::factory()->create(['name' => 'Pakan 0 grain']);
        $other = Product::factory()->create(['name' => 'Kalung merah']);
        $this->get(route('products.index', ['q' => '0']))->assertOk()->assertSee($matching->name)->assertDontSee($other->name);
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.products.index', ['q' => '0']))->assertOk()->assertSee($matching->name)->assertDontSee($other->name);
        $customer = User::factory()->create(['name' => 'Pelanggan Alpha']);
        $matchOrder = Order::create(['user_id' => $customer->id, 'order_number' => 'PWM-0-TEST', 'total_price' => '100.00', 'total_amount' => '100.00']);
        $otherOrder = Order::create(['user_id' => $customer->id, 'order_number' => 'PWM-OTHER-TEST', 'total_price' => '100.00', 'total_amount' => '100.00']);
        $this->get(route('admin.orders.index', ['q' => '0']))->assertOk()->assertSee($matchOrder->order_number)->assertDontSee($otherOrder->order_number);
    }

    public function test_browser_mutations_require_csrf_while_webhook_still_requires_valid_payload(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->app['env'] = 'local';
        try {
            $this->patch(route('profile.update'), ['name' => 'Changed', 'email' => $user->email])->assertStatus(419);
            $this->post(route('checkout.store'), [])->assertStatus(419);
            $this->actingAs(User::factory()->admin()->create())->post(route('admin.categories.store'), ['name' => 'CSRF attempt'])->assertStatus(419);
            $this->postJson(route('midtrans.notification'), [])->assertUnprocessable();
            $this->assertSame($user->name, $user->fresh()->name);
            $this->assertDatabaseCount('orders', 0);
            $this->assertDatabaseCount('categories', 0);
        } finally {
            $this->app['env'] = 'testing';
        }
    }
}
