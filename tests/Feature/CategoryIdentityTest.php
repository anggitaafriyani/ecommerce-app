<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\CategoryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Concerns\UsesMemoryDatabase;
use Tests\TestCase;

class CategoryIdentityTest extends TestCase
{
    use UsesMemoryDatabase;

    #[TestWith(['makanan hewan'])]
    #[TestWith(['  Makanan   Hewan  '])]
    #[TestWith(["MAKANAN\tHEWAN"])]
    public function test_normalized_duplicate_names_are_rejected_on_create_and_edit(string $name): void
    {
        Category::factory()->create(['name' => 'Makanan Hewan']);
        $other = Category::factory()->create(['name' => 'Mainan']);
        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('admin.categories.store'), ['name' => $name])
            ->assertSessionHasErrors(['name' => 'Nama kategori sudah digunakan. Pilih nama yang berbeda.']);
        $this->put(route('admin.categories.update', $other), ['name' => $name])
            ->assertSessionHasErrors('name');

        $this->assertSame('Mainan', $other->fresh()->name);
        $this->assertDatabaseCount('categories', 2);
    }

    public function test_category_can_keep_its_own_name_after_space_normalization(): void
    {
        $category = Category::factory()->create(['name' => 'Makanan Hewan']);
        $product = Product::factory()->for($category)->create();

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.categories.update', $category), ['name' => ' Makanan   Hewan '])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.categories.index'));

        $this->assertSame('Makanan Hewan', $category->fresh()->name);
        $this->assertSame($category->id, $product->fresh()->category_id);
    }

    public function test_upload_is_shared_by_home_dashboard_and_admin_and_survives_reordering(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'hhhh', 'image' => UploadedFile::fake()->image('category.png'),
        ])->assertSessionHasNoErrors();
        $category = Category::query()->firstOrFail();
        Storage::disk('public')->assertExists($category->image_path);
        $url = Storage::disk('public')->url($category->image_path);
        $this->get(route('admin.categories.edit', $category))->assertOk()->assertSee($url);
        Category::factory()->create(['name' => 'AAA']);
        $this->actingAs(User::factory()->create());

        $this->get('/')->assertOk()->assertSee($url);
        $this->get('/dashboard')->assertOk()->assertSee($url);
    }

    public function test_identical_image_content_with_a_different_filename_is_rejected(): void
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('first.png');
        $contents = file_get_contents($image->getRealPath());
        $this->actingAs(User::factory()->admin()->create());
        $this->post(route('admin.categories.store'), ['name' => 'Makanan', 'image' => $image])->assertSessionHasNoErrors();
        $other = Category::factory()->create(['name' => 'Mainan']);

        $this->post(route('admin.categories.store'), [
            'name' => 'Perawatan', 'image' => UploadedFile::fake()->createWithContent('renamed.png', $contents),
        ])->assertSessionHasErrors(['image' => 'Gambar ini sudah digunakan kategori lain. Pilih gambar yang berbeda.']);
        $this->put(route('admin.categories.update', $other), [
            'name' => 'Mainan', 'image' => UploadedFile::fake()->createWithContent('copy.png', $contents),
        ])->assertSessionHasErrors('image');

        $this->assertNull($other->fresh()->image_path);
        $this->assertDatabaseCount('categories', 2);
        $this->assertCount(1, Storage::disk('public')->allFiles('categories'));
    }

    public function test_replacing_an_image_updates_both_pages_and_preserves_the_old_file(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->admin()->create());
        $this->post(route('admin.categories.store'), [
            'name' => 'Mainan', 'image' => UploadedFile::fake()->image('first.png', 20, 20),
        ])->assertSessionHasNoErrors();
        $category = Category::query()->firstOrFail();
        $oldPath = $category->image_path;

        $this->put(route('admin.categories.update', $category), [
            'name' => 'Mainan', 'image' => UploadedFile::fake()->image('next.png', 40, 40),
        ])->assertSessionHasNoErrors();

        $newPath = $category->fresh()->image_path;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertExists([$oldPath, $newPath]);
        $this->actingAs(User::factory()->create());
        foreach (['/', '/dashboard'] as $page) {
            $this->get($page)->assertOk()->assertSee(Storage::disk('public')->url($newPath))
                ->assertDontSee(Storage::disk('public')->url($oldPath));
        }
    }

    public function test_same_category_can_reupload_its_image_or_omit_it_when_editing(): void
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('first.png');
        $contents = file_get_contents($image->getRealPath());
        $this->actingAs(User::factory()->admin()->create());
        $this->post(route('admin.categories.store'), ['name' => 'Mainan', 'image' => $image])->assertSessionHasNoErrors();
        $category = Category::query()->firstOrFail();
        $path = $category->image_path;

        $this->put(route('admin.categories.update', $category), [
            'name' => 'Mainan', 'image' => UploadedFile::fake()->createWithContent('renamed.png', $contents),
        ])->assertSessionHasNoErrors();
        $this->put(route('admin.categories.update', $category), ['name' => 'Mainan Baru'])->assertSessionHasNoErrors();

        $this->assertSame($path, $category->fresh()->image_path);
        Storage::disk('public')->assertExists($path);
        $this->assertCount(1, Storage::disk('public')->allFiles('categories'));
    }

    #[TestWith(['hhhh'])]
    #[TestWith(['Makanan Hewan'])]
    #[TestWith(['Perawatan & Kebersihan'])]
    #[TestWith(['Aksesori & Mainan'])]
    public function test_missing_image_uses_an_illustration_on_both_store_pages(string $name): void
    {
        Category::factory()->create(['name' => $name]);
        $this->actingAs(User::factory()->create());

        foreach (['/', '/dashboard'] as $page) {
            $this->get($page)->assertOk()
                ->assertSee('role="img" aria-label="'.e($name).'"', false)
                ->assertDontSee('Perlu gambar');
        }
    }

    public function test_invalid_image_is_rejected_without_storing_a_category_or_file(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->admin()->create())->post(route('admin.categories.store'), [
            'name' => 'Mainan', 'image' => UploadedFile::fake()->createWithContent('fake.png', '<script>alert(1)</script>'),
        ])->assertSessionHasErrors('image');

        $this->assertDatabaseCount('categories', 0);
        $this->assertCount(0, Storage::disk('public')->allFiles());
    }

    public function test_database_uniqueness_handles_an_image_conflict_at_persistence(): void
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('first.png');
        $contents = file_get_contents($image->getRealPath());
        $service = app(CategoryService::class);
        $first = $service->save(new Category, ['name' => 'Makanan'], $image);

        try {
            $service->save(new Category, ['name' => 'Mainan'], UploadedFile::fake()->createWithContent('copy.png', $contents));
            $this->fail('A duplicate image must not be saved.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('image', $exception->errors());
        }

        $this->assertDatabaseCount('categories', 1);
        Storage::disk('public')->assertExists($first->image_path);
        $this->assertCount(1, Storage::disk('public')->allFiles('categories'));
    }

    public function test_admin_dropdown_has_only_its_own_account_and_safe_profile_and_logout_links(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin PawMart', 'email' => 'admin@example.test']);
        $customer = User::factory()->create(['email' => 'customer@example.test']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()->assertSee('Menu akun Admin PawMart')->assertSee($admin->email)
            ->assertDontSee($customer->email)->assertSee('Profil Saya')
            ->assertSee('href="'.route('profile.edit').'"', false)
            ->assertSee('method="POST" action="'.route('logout').'"', false)
            ->assertSee('name="_token"', false);
        $this->assertSame(1, substr_count($response->getContent(), 'action="'.route('logout').'"'));
        $this->get(route('profile.edit'))->assertOk()->assertViewHas('user', fn (User $user): bool => $user->is($admin));
    }

    public function test_shipping_actions_reference_each_existing_modal_once_without_duplicate_plus_text(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.shipping.index'));

        $response->assertOk()->assertSeeInOrder(['Ekspedisi Kurir', 'Tambah Layanan Kurir', 'Tambah Tarif Baru'])
            ->assertDontSee('+ Ekspedisi Kurir')->assertDontSee('+ Tambah');
        foreach (['courier', 'service', 'rate'] as $action) {
            $this->assertSame(1, substr_count($response->getContent(), "\$dispatch('open-modal', 'add-{$action}-modal')"));
        }
    }
}
