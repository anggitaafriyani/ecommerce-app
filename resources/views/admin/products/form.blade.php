<x-admin-layout :title="$product->exists ? 'Edit produk' : 'Tambah produk'" subtitle="Lengkapi informasi produk. Kolom bertanda * wajib diisi.">
    @if($categories->isEmpty())
        <div class="pm-card p-8 sm:p-10 text-center max-w-xl mx-auto">
            <span class="flex h-14 w-14 mx-auto items-center justify-center rounded-2xl bg-pm-peach/25 text-amber-900 text-2xl">
                ⚠️
            </span>
            <h2 class="mt-4 text-xl font-bold text-pm-charcoal dark:text-gray-100">Kategori Belum Tersedia</h2>
            <p class="mt-2 text-sm text-pm-muted dark:text-gray-400">Buat kategori terlebih dahulu sebelum menambahkan produk.</p>
            <a href="{{ route('admin.categories.create') }}" class="pm-button mt-6">
                Tambah kategori
            </a>
        </div>
    @else
        <form method="POST"
              enctype="multipart/form-data"
              action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
            @csrf
            @if($product->exists)
                @method('PUT')
            @endif

            <div class="grid items-start gap-8 lg:grid-cols-12">
                {{-- Left: Main Details (7 cols) --}}
                <section class="pm-card p-6 sm:p-8 lg:col-span-7">
                    <h2 class="text-xl font-bold text-pm-charcoal dark:text-gray-100 border-b border-pm-border pb-4 dark:border-gray-800">
                        Informasi produk
                    </h2>

                    {{-- Product Name --}}
                    <div class="mt-6">
                        <label class="pm-label" for="name">
                            Nama produk <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input class="pm-input @error('name') pm-input-error @enderror"
                               id="name"
                               name="name"
                               type="text"
                               required
                               maxlength="255"
                               placeholder="Contoh: Royal Canin Kitten 2kg"
                               value="{{ old('name', $product->name) }}">
                        <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                    </div>

                    {{-- Category Select --}}
                    <div class="mt-6">
                        <label class="pm-label" for="category_id">
                            Kategori <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <select class="pm-input @error('category_id') pm-input-error @enderror"
                                id="category_id"
                                name="category_id"
                                required>
                            <option value="">Pilih kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-1.5" />
                    </div>

                    {{-- Description --}}
                    <div class="mt-6">
                        <label class="pm-label" for="description">
                            Deskripsi <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <textarea class="pm-input @error('description') pm-input-error @enderror"
                                  id="description"
                                  name="description"
                                  required
                                  maxlength="10000"
                                  rows="6"
                                  placeholder="Tuliskan komposisi, manfaat, dan petunjuk penggunaan produk...">{{ old('description', $product->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-1.5" />
                    </div>

                    {{-- Price, Stock & Weight --}}
                    <div class="mt-6 grid gap-5 sm:grid-cols-3">
                        <div>
                            <label class="pm-label" for="price">
                                Harga (Rp) <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input class="pm-input @error('price') pm-input-error @enderror"
                                   id="price"
                                   name="price"
                                   type="number"
                                   step="0.01"
                                   min="0"
                                   max="9999999999.99"
                                   required
                                   placeholder="Contoh: 75000"
                                   value="{{ old('price', $product->price) }}">
                            <x-input-error :messages="$errors->get('price')" class="mt-1.5" />
                        </div>

                        <div>
                            <label class="pm-label" for="stock">
                                Stok unit <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input class="pm-input @error('stock') pm-input-error @enderror"
                                   id="stock"
                                   name="stock"
                                   type="number"
                                   min="0"
                                   max="2147483647"
                                   step="1"
                                   required
                                   placeholder="Contoh: 25"
                                   value="{{ old('stock', $product->stock) }}">
                            <x-input-error :messages="$errors->get('stock')" class="mt-1.5" />
                        </div>

                        <div>
                            <label class="pm-label" for="weight">
                                Berat (gram) <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input class="pm-input @error('weight') pm-input-error @enderror"
                                   id="weight"
                                   name="weight"
                                   type="number"
                                   min="1"
                                   max="1000000"
                                   step="1"
                                   required
                                   placeholder="Contoh: 500"
                                   value="{{ old('weight', $product->weight ?? 500) }}">
                            <x-input-error :messages="$errors->get('weight')" class="mt-1.5" />
                        </div>
                    </div>
                </section>

                {{-- Right: Image Upload & Status (5 cols) --}}
                <section class="pm-card p-6 sm:p-8 lg:col-span-5">
                    <h2 class="text-xl font-bold text-pm-charcoal dark:text-gray-100 border-b border-pm-border pb-4 dark:border-gray-800">
                        Gambar & status
                    </h2>

                    {{-- Image Preview --}}
                    @if($product->image_path)
                        <div class="mt-6">
                            <span class="pm-label">Gambar saat ini</span>
                            <div class="overflow-hidden rounded-2xl border border-pm-border bg-pm-cream/50 p-2 dark:border-gray-800 dark:bg-gray-800">
                                <img class="aspect-square w-full rounded-xl object-contain"
                                     src="{{ Storage::disk('public')->url($product->image_path) }}"
                                     alt="{{ $product->name }}">
                            </div>
                        </div>
                    @endif

                    {{-- File Input --}}
                    <div class="mt-6">
                        <label class="pm-label" for="image">
                            {{ $product->exists ? 'Ganti gambar' : 'Gambar produk *' }}
                        </label>
                        <input class="pm-input file:mr-3 file:rounded-xl file:border-0 file:bg-pm-green file:px-4 file:py-2 file:text-xs file:font-semibold file:text-white file:shadow-xs hover:file:bg-pm-green-dk"
                               id="image"
                               name="image"
                               type="file"
                               accept="image/jpeg,image/png,image/webp"
                               @required(!$product->exists)
                               aria-describedby="image-help">
                        <p id="image-help" class="mt-2.5 text-xs leading-5 text-pm-muted dark:text-gray-400">
                            JPG, PNG, atau WebP. Maksimal 2 MB dan 4096 × 4096 piksel. {{ $product->exists ? 'Kosongkan untuk mempertahankan gambar lama.' : '' }}
                        </p>
                        <x-input-error :messages="$errors->get('image')" class="mt-1.5" />
                    </div>

                    {{-- Active Status Select --}}
                    <div class="mt-6 border-t border-pm-border pt-6 dark:border-gray-800">
                        <label class="pm-label" for="is_active">
                            Status produk <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <select class="pm-input" id="is_active" name="is_active">
                            <option value="1" @selected((string) old('is_active', (int) $product->is_active) === '1')>Aktif</option>
                            <option value="0" @selected((string) old('is_active', (int) $product->is_active) === '0')>Nonaktif</option>
                        </select>
                        <p class="mt-2 text-xs leading-5 text-pm-muted dark:text-gray-400">
                            Nonaktifkan produk untuk menghentikan penjualan tanpa menghapus datanya.
                        </p>
                    </div>
                </section>
            </div>

            {{-- Action Buttons --}}
            <div class="mt-8 flex flex-wrap items-center gap-3">
                <button class="pm-button" type="submit">
                    Simpan produk
                </button>
                <a class="pm-secondary" href="{{ route('admin.products.index') }}">
                    Batal
                </a>
            </div>
        </form>
    @endif
</x-admin-layout>
