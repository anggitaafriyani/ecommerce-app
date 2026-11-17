<x-admin-layout :title="$product->exists ? 'Edit produk' : 'Tambah produk'" subtitle="Lengkapi informasi produk. Kolom bertanda * wajib diisi.">
    @if($categories->isEmpty())<div class="pm-card p-8"><p>Buat kategori terlebih dahulu sebelum menambahkan produk.</p><a href="{{ route('admin.categories.create') }}" class="pm-button mt-5">Tambah kategori</a></div>
    @else
    <form method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
        @csrf @if($product->exists) @method('PUT') @endif
        <div class="grid items-start gap-6 xl:grid-cols-3">
            <section class="pm-card p-6 sm:p-8 xl:col-span-2"><h2 class="mb-6 text-lg font-semibold">Informasi produk</h2>
                <label class="pm-label" for="name">Nama produk *</label><input class="pm-input" id="name" name="name" required maxlength="255" value="{{ old('name', $product->name) }}"><x-input-error :messages="$errors->get('name')" class="mt-2" />
                <div class="mt-6"><label class="pm-label" for="category_id">Kategori *</label><select class="pm-input" id="category_id" name="category_id" required><option value="">Pilih kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select><x-input-error :messages="$errors->get('category_id')" class="mt-2" /></div>
                <div class="mt-6"><label class="pm-label" for="description">Deskripsi *</label><textarea class="pm-input" id="description" name="description" required maxlength="10000" rows="7">{{ old('description', $product->description) }}</textarea><x-input-error :messages="$errors->get('description')" class="mt-2" /></div>
                <div class="mt-6 grid gap-6 sm:grid-cols-2"><div><label class="pm-label" for="price">Harga (Rp) *</label><input class="pm-input" id="price" name="price" type="number" step="0.01" min="0" max="9999999999.99" required value="{{ old('price', $product->price) }}"><x-input-error :messages="$errors->get('price')" class="mt-2" /></div><div><label class="pm-label" for="stock">Jumlah stok *</label><input class="pm-input" id="stock" name="stock" type="number" min="0" max="2147483647" step="1" required value="{{ old('stock', $product->stock) }}"><x-input-error :messages="$errors->get('stock')" class="mt-2" /></div></div>
            </section>
            <section class="pm-card p-6"><h2 class="mb-5 text-lg font-semibold">Gambar & status</h2>
                @if($product->image_path)<img class="mb-5 aspect-square w-full rounded-xl bg-[#FAF7F2] object-contain" src="{{ Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}">@endif
                <label class="pm-label" for="image">{{ $product->exists ? 'Ganti gambar' : 'Gambar produk *' }}</label><input class="pm-input p-3 file:mr-3 file:rounded file:border-0 file:bg-[#FAF7F2] file:px-3 file:py-2 file:text-sm file:text-[#2F5D50]" id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" @required(!$product->exists) aria-describedby="image-help"><p id="image-help" class="mt-3 text-xs leading-5 text-gray-500">JPG, PNG, atau WebP. Maksimal 2 MB dan 4096 × 4096 piksel. {{ $product->exists ? 'Kosongkan untuk mempertahankan gambar lama.' : '' }}</p><x-input-error :messages="$errors->get('image')" class="mt-2" />
                <div class="mt-6"><label class="pm-label" for="is_active">Status produk *</label><select class="pm-input" id="is_active" name="is_active"><option value="1" @selected((string) old('is_active', (int) $product->is_active) === '1')>Aktif</option><option value="0" @selected((string) old('is_active', (int) $product->is_active) === '0')>Nonaktif</option></select><p class="mt-3 text-xs leading-5 text-gray-500">Nonaktifkan produk untuk menghentikan penjualan tanpa menghapus datanya.</p></div>
            </section>
        </div>
        <div class="mt-7 flex flex-wrap gap-3"><button class="pm-button" type="submit">Simpan produk</button><a class="pm-secondary" href="{{ route('admin.products.index') }}">Batal</a></div>
    </form>
    @endif
</x-admin-layout>
