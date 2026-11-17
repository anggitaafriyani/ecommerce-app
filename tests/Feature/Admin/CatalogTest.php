<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductService;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\UsesMemoryDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use UsesMemoryDatabase;

    private function productData(Category $category): array
    {
        return ['name' => 'Makanan Kucing', 'category_id' => $category->id, 'description' => 'Makanan lengkap untuk kucing.', 'price' => '125000.50', 'stock' => 12, 'is_active' => 1];
    }

    public function test_dashboard_counts_real_data_and_existing_payment_statuses(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        User::factory()->create();
        $category = Category::factory()->create();
        Product::factory()->for($category)->count(3)->create();
        $archived = Product::factory()->for($category)->create();
        $archived->delete();
        foreach (['pending', 'pending', 'success', 'failed', 'expired'] as $status) {
            DB::table('orders')->insert(['user_id' => $customer->id, 'total_price' => 10000, 'payment_status' => $status]);
        }
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->assertViewHas('stats', [
            'products' => 3, 'categories' => 1, 'customers' => 2, 'orders' => 5, 'pending' => 2, 'paid' => 1,
        ]);
    }

    public static function protectedRoutes(): array
    {
        $cases = [];
        foreach (['GET /admin/dashboard', 'GET /admin/categories', 'GET /admin/categories/create', 'POST /admin/categories', 'GET /admin/categories/1/edit', 'PUT /admin/categories/1', 'DELETE /admin/categories/1', 'GET /admin/products', 'GET /admin/products/create', 'POST /admin/products', 'GET /admin/products/1', 'GET /admin/products/1/edit', 'PUT /admin/products/1', 'DELETE /admin/products/1'] as $route) {
            [$method, $path] = explode(' ', $route);
            $cases[$route] = [$method, $path];
        }

        return $cases;
    }

    #[DataProvider('protectedRoutes')]
    public function test_customers_cannot_access_any_admin_operation(string $method, string $path): void
    {
        Product::factory()->create();
        $this->actingAs(User::factory()->create())->call($method, $path)->assertForbidden();
        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('categories', 1);
    }

    #[DataProvider('protectedRoutes')]
    public function test_guests_cannot_access_any_admin_operation(string $method, string $path): void
    {
        Product::factory()->create();
        $this->call($method, $path)->assertRedirect(route('login'));
        $this->assertDatabaseCount('products', 1);
    }

    public function test_admin_can_create_edit_and_delete_unused_category(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $this->get('/admin/categories/create')->assertOk();
        $this->post('/admin/categories', ['name' => 'Grooming', 'description' => 'Perawatan hewan'])
            ->assertRedirect(route('admin.categories.index'))->assertSessionHas('status');
        $category = Category::query()->firstOrFail();
        $slug = $category->slug;
        $this->get('/admin/categories')->assertOk()->assertSee('Grooming');
        $this->get('/admin/categories/'.$category->id.'/edit')->assertOk();
        $this->put('/admin/categories/'.$category->id, ['name' => 'Grooming Baru', 'description' => 'Baru'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Grooming Baru', 'slug' => $slug]);
        $this->delete('/admin/categories/'.$category->id)->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_name_is_required_unique_and_can_remain_unchanged_on_update(): void
    {
        $category = Category::factory()->create();
        $this->actingAs(User::factory()->admin()->create());
        $this->post('/admin/categories', [])->assertSessionHasErrors('name');
        $this->post('/admin/categories', ['name' => $category->name])->assertSessionHasErrors('name');
        $this->put('/admin/categories/'.$category->id, ['name' => $category->name])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_category_used_by_product_or_archived_product_cannot_be_deleted(): void
    {
        $product = Product::factory()->create();
        $this->actingAs(User::factory()->admin()->create());
        $this->delete('/admin/categories/'.$product->category_id)->assertSessionHasErrors('category');
        $product->delete();
        $this->delete('/admin/categories/'.$product->category_id)->assertSessionHasErrors('category');
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_product_create_update_replace_image_and_archive_preserve_history(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $this->get('/admin/products/create')->assertOk();
        $data = $this->productData($category);
        $this->post('/admin/products', $data + ['image' => UploadedFile::fake()->image('cat.jpg')])->assertSessionHasNoErrors();
        $product = Product::query()->firstOrFail();
        $oldPath = $product->image_path;
        Storage::disk('public')->assertExists($oldPath);
        $this->assertSame('125000.50', $product->price);
        $this->get('/admin/products')->assertOk()->assertSee('Makanan Kucing');
        $this->get('/admin/products/'.$product->id)->assertOk();
        $this->get('/admin/products/'.$product->id.'/edit')->assertOk();
        $this->put('/admin/products/'.$product->id, array_replace($data, ['stock' => 0, 'is_active' => 0]))->assertSessionHasNoErrors();
        $this->assertSame($oldPath, $product->fresh()->image_path);
        $this->assertFalse($product->fresh()->is_active);
        $this->get('/admin/products')->assertSee('Habis')->assertSee('Nonaktif');
        $other = Category::factory()->create();
        $this->put('/admin/products/'.$product->id, array_replace($data, ['category_id' => $other->id, 'image' => UploadedFile::fake()->image('new.png')]))->assertSessionHasNoErrors();
        $product->refresh();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($product->image_path);
        $this->assertSame($other->id, $product->category_id);
        $orderId = DB::table('orders')->insertGetId(['user_id' => $admin->id, 'total_price' => 125000, 'payment_status' => 'success']);
        DB::table('order_details')->insert(['order_id' => $orderId, 'product_id' => $product->id, 'quantity' => 1, 'subtotal_price' => 125000]);
        $this->delete('/admin/products/'.$product->id)->assertRedirect(route('admin.products.index'));
        $this->assertSoftDeleted($product);
        $this->assertDatabaseHas('order_details', ['product_id' => $product->id, 'subtotal_price' => 125000]);
        Storage::disk('public')->assertExists($product->image_path);
        $this->get('/admin/products/'.$product->id)->assertNotFound();
    }

    public static function invalidProductFields(): array
    {
        return [
            'negative price' => ['price', -1], 'negative stock' => ['stock', -1],
            'fractional stock' => ['stock', 1.5], 'unknown category' => ['category_id', 9999],
            'invalid status' => ['is_active', 'maybe'], 'missing description' => ['description', ''],
            'missing name' => ['name', ''], 'price precision' => ['price', '12.345'],
            'price overflow' => ['price', '10000000000'], 'stock overflow' => ['stock', 2147483648],
        ];
    }

    #[DataProvider('invalidProductFields')]
    public function test_invalid_product_fields_are_rejected_without_writing_files(string $field, mixed $value): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();
        $data = array_replace($this->productData($category), [$field => $value, 'image' => UploadedFile::fake()->image('cat.jpg')]);
        $this->actingAs(User::factory()->admin()->create())->post('/admin/products', $data)->assertSessionHasErrors($field);
        $this->assertDatabaseCount('products', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_missing_non_image_and_oversize_uploads_are_rejected(): void
    {
        Storage::fake('public');
        $data = $this->productData(Category::factory()->create());
        $this->actingAs(User::factory()->admin()->create());
        $this->post('/admin/products', $data)->assertSessionHasErrors('image');
        $this->post('/admin/products', $data + ['image' => UploadedFile::fake()->create('bad.svg', 10, 'image/svg+xml')])->assertSessionHasErrors('image');
        $this->post('/admin/products', $data + ['image' => UploadedFile::fake()->image('large.jpg')->size(2049)])->assertSessionHasErrors('image');
        $this->assertDatabaseCount('products', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_invalid_update_preserves_existing_product_and_image(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();
        Storage::disk('public')->put('products/original.jpg', 'existing');
        $product->image_path = 'products/original.jpg';
        $product->save();
        $this->actingAs(User::factory()->admin()->create())->put('/admin/products/'.$product->id,
            array_replace($this->productData($product->category), ['stock' => -1, 'image' => UploadedFile::fake()->image('new.jpg')]))->assertSessionHasErrors('stock');
        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame(['products/original.jpg'], Storage::disk('public')->allFiles());
    }

    public function test_admin_search_and_escaped_product_text(): void
    {
        Product::factory()->create(['name' => '<script>alert(1)</script> Kucing']);
        Product::factory()->create(['name' => 'Mainan Anjing']);
        $this->actingAs(User::factory()->admin()->create())->get('/admin/products?q=Kucing')->assertOk()
            ->assertSee('<script>alert(1)</script> Kucing')->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('Mainan Anjing');
    }

    public function test_create_product_page_without_categories_offers_category_creation(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/admin/products/create')->assertOk()->assertSee('Buat kategori terlebih dahulu');
    }

    public function test_failed_database_save_removes_the_newly_uploaded_file(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();
        $data = $this->productData($category);
        $category->delete();

        try {
            app(ProductService::class)->save(new Product, $data, UploadedFile::fake()->image('cat.jpg'));
            $this->fail('Foreign key harus menolak kategori yang telah dihapus.');
        } catch (QueryException $exception) {
            $this->assertSame('23000', $exception->getCode());
        }

        $this->assertDatabaseCount('products', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
