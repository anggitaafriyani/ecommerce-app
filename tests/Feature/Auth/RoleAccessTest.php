<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\UsesMemoryDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use UsesMemoryDatabase;

    #[TestWith(['/admin/dashboard'])]
    #[TestWith(['/dashboard'])]
    public function test_guests_are_redirected_to_login(string $path): void
    {
        $this->get($path)->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/dashboard')->assertForbidden();
    }

    public function test_admin_cannot_access_customer_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/dashboard')->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard_and_profile(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/admin/dashboard')
            ->assertOk()->assertSee('Dashboard Admin');
        $this->get('/profile')->assertOk();
        $this->get('/')->assertOk()->assertSee(route('admin.dashboard'));
    }

    public function test_customer_dashboard_escapes_the_name(): void
    {
        $user = User::factory()->create(['name' => '<script>alert(1)</script>']);
        $this->actingAs($user)->get('/dashboard')->assertOk()
            ->assertSee($user->name)->assertDontSee($user->name, false);
    }

    public function test_admin_login_redirects_to_admin_even_with_customer_intended_url(): void
    {
        $user = User::factory()->admin()->create();
        $this->withSession(['url.intended' => '/dashboard'])->post('/login', [
            'email' => $user->email, 'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_customer_login_does_not_follow_admin_intended_url(): void
    {
        $user = User::factory()->create();
        $this->withSession(['url.intended' => '/admin/dashboard'])->post('/login', [
            'email' => $user->email, 'password' => 'password',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_admin_is_redirected_away_from_guest_pages(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/login')->assertRedirect(route('admin.dashboard'));
        $this->get('/register')->assertRedirect(route('admin.dashboard'));
    }

    public function test_profile_cannot_change_role(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name, 'email' => $user->email, 'role' => 'admin',
        ])->assertSessionHasNoErrors();
        $this->assertSame('customer', $user->fresh()->role);
    }

    public function test_login_is_rate_limited_after_five_failures(): void
    {
        $user = User::factory()->create();
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logout_removes_access_to_protected_pages(): void
    {
        $this->actingAs(User::factory()->admin()->create())->post('/logout')->assertRedirect('/');
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
    }
}
