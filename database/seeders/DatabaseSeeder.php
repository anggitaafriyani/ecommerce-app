<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Akun Admin
        User::create([
            'name' => 'Admin PetShop',
            'email' => 'admin@petshop.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Buat Akun Pelanggan
        User::create([
            'name' => 'Pecinta Anabul',
            'email' => 'customer@petshop.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        // 3. Buat Kategori Pet Shop
        $kategoriMakanan = Category::create(['name' => 'Makanan Hewan']);
        $kategoriPerawatan = Category::create(['name' => 'Perawatan & Kebersihan']);
        $kategoriAksesori = Category::create(['name' => 'Aksesori & Mainan']);

        // 4. Buat Produk Dummy Pet Shop
        Product::create([
            'category_id' => $kategoriMakanan->id,
            'name' => 'Royal Canin Kitten 2kg',
            'description' => 'Makanan kering bernutrisi tinggi untuk anak kucing usia 1-4 bulan.',
            'price' => 250000,
            'stock' => 20,
        ]);

        Product::create([
            'category_id' => $kategoriPerawatan->id,
            'name' => 'Pasir Kucing Bentonite 10L',
            'description' => 'Pasir gumpal wangi apel, cepat menyerap dan menghilangkan bau.',
            'price' => 65000,
            'stock' => 50,
        ]);

        Product::create([
            'category_id' => $kategoriAksesori->id,
            'name' => 'Kalung Lonceng Kulit Sintetis',
            'description' => 'Kalung leher dengan lonceng kecil, ukuran bisa disesuaikan.',
            'price' => 15000,
            'stock' => 100,
        ]);
    }
}