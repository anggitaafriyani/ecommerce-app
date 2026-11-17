<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\UsesMemoryDatabase;
use Tests\TestCase;

class PasswordSafetyTest extends TestCase
{
    use UsesMemoryDatabase;

    public static function unsupportedPasswords(): array
    {
        return [
            'multibyte exceeds bcrypt bytes' => [str_repeat('é', 37)],
            'null byte' => ["Secret123!\0tail"],
        ];
    }

    public function test_registration_accepts_multibyte_password_at_the_72_byte_boundary(): void
    {
        $password = str_repeat('é', 36);
        $this->post('/register', [
            'name' => 'Pelanggan', 'email' => 'unicode@example.com',
            'password' => $password, 'password_confirmation' => $password,
        ])->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));

        $user = User::query()->where('email', 'unicode@example.com')->firstOrFail();
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertSame('customer', $user->role);
    }

    #[DataProvider('unsupportedPasswords')]
    public function test_admin_seeder_rejects_unsupported_password_without_creating_admin(string $password): void
    {
        config(['pawmart.admin.email' => 'new-admin@example.com', 'pawmart.admin.password' => 'Aa1!'.$password]);

        try {
            $this->seed(AdminSeeder::class);
            $this->fail('Seeder harus menolak password yang tidak didukung bcrypt.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('password', $exception->errors());
        }

        $this->assertDatabaseCount('users', 0);
    }

    #[DataProvider('unsupportedPasswords')]
    public function test_registration_rejects_unsupported_password_without_creating_user(string $password): void
    {
        $this->post('/register', [
            'name' => 'Pelanggan', 'email' => 'new@example.com',
            'password' => $password, 'password_confirmation' => $password,
        ])->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    #[DataProvider('unsupportedPasswords')]
    public function test_password_update_rejects_unsupported_password_without_changing_credentials(string $password): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put('/password', [
            'current_password' => 'password',
            'password' => $password, 'password_confirmation' => $password,
        ])->assertSessionHasErrorsIn('updatePassword', 'password');
        $this->assertSame($user->password, $user->fresh()->password);
    }

    #[DataProvider('unsupportedPasswords')]
    public function test_password_reset_rejects_unsupported_password_without_changing_credentials(string $password): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);
        $this->post('/reset-password', [
            'token' => $token, 'email' => $user->email,
            'password' => $password, 'password_confirmation' => $password,
        ])->assertSessionHasErrors('password');
        $this->assertSame($user->password, $user->fresh()->password);
    }
}
