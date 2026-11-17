<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\Concerns\UsesMemoryDatabase;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use UsesMemoryDatabase;

    public function test_seeder_creates_admin_and_preserves_credentials_on_rerun(): void
    {
        config(['pawmart.admin.email' => 'admin@example.com', 'pawmart.admin.password' => 'StrongPassword123!']);
        $this->seed(AdminSeeder::class);
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check('StrongPassword123!', $admin->password));
        config(['pawmart.admin.password' => 'DifferentPassword123!']);
        $this->seed(AdminSeeder::class);
        $this->assertDatabaseCount('users', 1);
        $this->assertSame($admin->password, $admin->fresh()->password);
    }

    public function test_seeder_refuses_to_promote_an_existing_customer(): void
    {
        $user = User::factory()->create();
        config(['pawmart.admin.email' => $user->email]);
        try {
            $this->seed(AdminSeeder::class);
            $this->fail('Seeder harus menolak email Pelanggan.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Pelanggan', $exception->getMessage());
        }
        $this->assertSame('customer', $user->fresh()->role);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_seeder_requires_a_secure_password_for_a_new_admin(): void
    {
        config(['pawmart.admin.password' => null]);
        try {
            $this->seed(AdminSeeder::class);
            $this->fail('Seeder harus menolak password kosong.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('password', $exception->errors());
        }
        $this->assertDatabaseCount('users', 0);
    }
}
