<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\UsesMemoryDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use UsesMemoryDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_public_registration_ignores_admin_role_and_hashes_password(): void
    {
        $this->post('/register', [
            'name' => 'Pelanggan', 'email' => 'customer@example.com',
            'password' => 'Secret123!', 'password_confirmation' => 'Secret123!',
            'role' => 'admin', 'email_verified_at' => now(),
        ])->assertRedirect(route('dashboard'));

        $user = User::query()->where('email', 'customer@example.com')->firstOrFail();
        $this->assertSame('customer', $user->role);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue(Hash::check('Secret123!', $user->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_duplicate_email_is_rejected_without_creating_an_account(): void
    {
        $user = User::factory()->create();
        $this->post('/register', [
            'name' => 'Duplicate', 'email' => $user->email,
            'password' => 'Secret123!', 'password_confirmation' => 'Secret123!',
        ])->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    #[TestWith(['short', 'short'])]
    #[TestWith(['Secret123!', 'different'])]
    public function test_invalid_password_is_rejected(string $password, string $confirmation): void
    {
        $this->post('/register', [
            'name' => 'Pelanggan', 'email' => 'customer@example.com',
            'password' => $password, 'password_confirmation' => $confirmation,
        ])->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_required_registration_fields_are_validated(): void
    {
        $this->post('/register', [])->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }
}
