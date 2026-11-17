<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\UsesMemoryDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use UsesMemoryDatabase;

    #[TestWith(['admin', '/admin/dashboard', 'Dashboard Admin'])]
    #[TestWith(['customer', '/dashboard', 'Akun Pelanggan'])]
    public function test_login_dashboard_and_logout_flow_for_each_role(string $role, string $dashboard, string $heading): void
    {
        $user = User::factory()->create(['role' => $role]);

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $response->assertRedirect($dashboard);
        $this->assertAuthenticatedAs($user);
        $this->get($dashboard)->assertOk()->assertSee($heading);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->get($dashboard)->assertRedirect(route('login'));
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
