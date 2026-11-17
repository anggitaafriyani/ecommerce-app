<?php

namespace Database\Seeders;

use App\Models\User;
use App\Rules\BcryptPassword;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = config('pawmart.admin.email');
        Validator::make(['email' => $email], ['email' => ['required', 'email', 'max:255']])->validate();

        $existing = User::query()->where('email', $email)->first();

        if ($existing) {
            if ($existing->role !== User::ROLE_ADMIN) {
                throw new RuntimeException('Email admin sudah digunakan Pelanggan. Pilih PAWMART_ADMIN_EMAIL lain.');
            }

            $this->command?->info('Admin sudah tersedia; data dan password tidak diubah.');

            return;
        }

        $data = Validator::make(config('pawmart.admin'), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols(), new BcryptPassword],
        ])->validate();

        $admin = new User($data);
        $admin->role = User::ROLE_ADMIN;
        $admin->save();
    }
}
