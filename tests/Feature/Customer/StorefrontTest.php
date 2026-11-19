<?php

namespace Tests\Feature\Customer;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\UsesMemoryDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use UsesMemoryDatabase;

    public function test_home_shows_database_categories_and_only_active_latest_products(): void
    {
        $category = Category::factory()->create(['name' => 'Makanan Kucing']);
        Product::factory()->for($category)->count(9)->create();
        Product::factory()->for($category)->create(['name' => 'Rahasia nonaktif', 'is_active' => false]);
        $archived = Product::factory()->for($category)->create(['name' => 'Produk arsip']);
        $archived->delete();
        $this->get('/')->assertOk()->assertSee('Makanan Kucing')->assertDontSee('Rahasia nonaktif')->assertDontSee('Produk arsip')
            ->assertViewHas('products', fn ($products) => $products->count() === 8)
            ->assertViewHas('categories', fn ($categories) => $categories->first()->products_count === 9);
    }

    public function test_catalog_is_public_and_lists_only_active_products_including_out_of_stock(): void
    {
        Product::factory()->create(['name' => 'Produk tersedia']);
        Product::factory()->create(['name' => 'Produk habis', 'stock' => 0]);
        Product::factory()->create(['name' => 'Tidak dijual', 'is_active' => false]);
        $archived = Product::factory()->create(['name' => 'Diarsipkan']);
        $archived->delete();
        $this->get('/products')->assertOk()->assertSee('Produk tersedia')->assertSee('Produk habis')->assertSee('Stok habis')
            ->assertDontSee('Tidak dijual')->assertDontSee('Diarsipkan')->assertViewHas('products', fn ($products) => $products->total() === 2);
    }

    public function test_name_search_and_category_filter_work_together(): void
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->create(['name' => 'Makanan Kucing Pilihan']);
        Product::factory()->for($category)->create(['name' => 'Pasir Bersih']);
        Product::factory()->create(['name' => 'Makanan Anjing']);
        $this->get('/products?q=Makanan&category='.$category->id)->assertOk()->assertSee('Makanan Kucing Pilihan')
            ->assertDontSee('Pasir Bersih')->assertDontSee('Makanan Anjing')->assertViewHas('products', fn ($products) => $products->total() === 1);
    }

    public function test_name_search_alone_works(): void
    {
        Product::factory()->create(['name' => 'Kalung Kucing']);
        Product::factory()->create(['name' => 'Sampo Anjing']);
        $this->get('/products?q=Kalung')->assertOk()->assertSee('Kalung Kucing')->assertDontSee('Sampo Anjing');
    }

    public function test_category_filter_alone_works(): void
    {
        $product = Product::factory()->create(['name' => 'Kategori pilihan']);
        Product::factory()->create(['name' => 'Kategori lain']);
        $this->get('/products?category='.$product->category_id)->assertOk()->assertSee('Kategori pilihan')->assertDontSee('Kategori lain');
    }

    public function test_pagination_keeps_search_and_category_filters(): void
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->count(13)->create(['name' => 'Kucing']);
        $url = '/products?q=Kucing&category='.$category->id;
        $this->get($url)->assertOk()->assertViewHas('products', fn ($products) => $products->count() === 12 && $products->total() === 13)
            ->assertSee('q=Kucing&category='.$category->id.'&page=2');
        $this->get($url.'&page=2')->assertOk()->assertViewHas('products', fn ($products) => $products->count() === 1);
    }

    public function test_empty_catalog_and_no_search_results_have_clear_empty_states(): void
    {
        $this->get('/')->assertOk()->assertSee('Produk sedang disiapkan');
        $this->get('/products')->assertOk()->assertSee('Belum ada produk yang sesuai');
        Product::factory()->create(['name' => 'Makanan']);
        $this->get('/products?q=tidakditemukan')->assertOk()->assertSee('Belum ada produk yang sesuai')->assertSee('Hapus pencarian');
    }

    public function test_detail_uses_slug_and_shows_image_price_description_stock_and_quantity(): void
    {
        $product = Product::factory()->create(['price' => '123456.75', 'stock' => 7, 'image_path' => 'products/example.jpg']);
        $this->get(route('products.show', $product->slug))->assertOk()->assertSee($product->name)->assertSee($product->description)
            ->assertSee('Rp123.456,75')->assertSee('7 unit')->assertSee('/storage/products/example.jpg')
            ->assertSee('min="1"', false)->assertSee('max="7"', false)->assertSee('step="1"', false)
            ->assertSee('Tambah ke Keranjang')->assertSee('Masuk sebagai Pelanggan');
    }

    public function test_inactive_archived_and_unknown_product_detail_return_404(): void
    {
        $inactive = Product::factory()->create(['is_active' => false]);
        $archived = Product::factory()->create();
        $archived->delete();
        $this->get(route('products.show', $inactive->slug))->assertNotFound();
        $this->get(route('products.show', $archived->slug))->assertNotFound();
        $this->get('/products/not-a-product')->assertNotFound();
    }

    public function test_out_of_stock_detail_disables_quantity_and_cart(): void
    {
        $product = Product::factory()->create(['stock' => 0]);
        $response = $this->get(route('products.show', $product->slug))->assertOk()->assertSee('Stok habis')->assertSee('Jumlah tidak dapat dipilih');
        $this->assertMatchesRegularExpression('/<input[^>]+id="quantity"[^>]+disabled/s', $response->getContent());
        $this->assertMatchesRegularExpression('/<button[^>]+disabled[^>]*>Tambah ke Keranjang/s', $response->getContent());
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_public_pages_escape_user_content_and_render_missing_image_placeholder(): void
    {
        $product = Product::factory()->create(['name' => '<script>alert(1)</script>', 'description' => '<img src=x onerror=alert(1)>']);
        $this->get(route('products.show', $product->slug))->assertOk()->assertSee($product->name)->assertDontSee($product->name, false)
            ->assertSee($product->description)->assertDontSee($product->description, false)->assertSee('Foto produk belum tersedia');
    }

    #[TestWith(['admin', 'Dashboard Admin'])]
    #[TestWith(['customer', 'Dashboard'])]
    public function test_authenticated_navigation_uses_correct_dashboard(string $role, string $label): void
    {
        $user = User::factory()->create(['role' => $role]);
        $this->actingAs($user)->get('/products')->assertOk()->assertSee($label)->assertSee(route($user->dashboardRoute()))->assertSee('Profil');
    }

    public function test_guest_navigation_has_login_registration_and_honest_cart_information(): void
    {
        $this->get('/products')->assertOk()->assertSee(route('login'))->assertSee(route('register'))
            ->assertSee('Keranjang belanja')->assertSee(route('cart.index'));
    }

    public function test_invalid_search_parameters_are_rejected(): void
    {
        $this->getJson('/products?q[]=test&category=invalid&page=0')->assertUnprocessable()->assertJsonValidationErrors(['q', 'category', 'page']);
    }

    public function test_admin_changes_are_reflected_on_public_detail_and_deactivation_hides_it(): void
    {
        $product = Product::factory()->create();
        $this->actingAs(User::factory()->admin()->create())->put('/admin/products/'.$product->id, [
            'name' => $product->name, 'category_id' => $product->category_id, 'description' => $product->description,
            'price' => '99000.25', 'stock' => 4, 'is_active' => 1,
        ])->assertSessionHasNoErrors();
        $this->get(route('products.show', $product->slug))->assertOk()->assertSee('Rp99.000,25')->assertSee('4 unit');
        $product->update(['is_active' => false]);
        $this->get(route('products.show', $product->slug))->assertNotFound();
    }

    public function test_customer_sees_avatar_dropdown_with_dynamic_initials_name_and_email(): void
    {
        $customer = User::factory()->create([
            'name' => 'Farhan Saputra',
            'email' => 'farhan@example.com',
            'role' => 'customer',
        ]);

        $response = $this->actingAs($customer)->get('/products');

        $response->assertOk()
            ->assertSee('Menu akun Farhan Saputra')
            ->assertSee('Farhan Saputra')
            ->assertSee('farhan@example.com')
            ->assertSee('Profil Saya')
            ->assertSee(route('profile.edit'))
            ->assertSee(route('logout'))
            ->assertDontSee(route('register'));
    }

    public function test_customer_can_logout_safely_and_redirects_to_home(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_guest_does_not_see_avatar_account_dropdown(): void
    {
        $response = $this->get('/products');

        $response->assertOk()
            ->assertSee(route('login'))
            ->assertSee(route('register'))
            ->assertDontSee('Profil Saya')
            ->assertDontSee('Menu akun');
    }
}
