<x-admin-layout :title="$category->exists ? 'Edit kategori' : 'Tambah kategori'" subtitle="Berikan nama yang jelas agar produk mudah dikelompokkan.">
    <div class="max-w-2xl">
        <form method="POST" enctype="multipart/form-data"
              action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
              class="pm-card p-6 sm:p-8">
            @csrf
            @if($category->exists)
                @method('PUT')
            @endif

            <div>
                <label for="name" class="pm-label">
                    Nama kategori <span class="text-red-500" aria-hidden="true">*</span>
                </label>
                <input class="pm-input @error('name') pm-input-error @enderror"
                       id="name"
                       name="name"
                       type="text"
                       value="{{ old('name', $category->name) }}"
                       placeholder="Contoh: Makanan Kucing, Perawatan & Grooming"
                       required
                       maxlength="255"
                       autofocus>
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="mt-6">
                <label for="image" class="pm-label">Gambar kategori <span class="font-normal text-pm-muted">(opsional)</span></label>
                <div class="mb-3 flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl border border-pm-border bg-pm-cream p-2">
                    <x-category-image :category="$category" />
                </div>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="pm-input @error('image') pm-input-error @enderror" aria-describedby="image-help">
                <p id="image-help" class="mt-2 text-xs text-pm-muted">JPG, PNG, atau WebP, maksimal 2 MB dan 4096 × 4096 piksel. Gunakan gambar unik yang belum dipakai kategori lain. Kosongkan untuk mempertahankan gambar saat ini.</p>
                <x-input-error :messages="$errors->get('image')" class="mt-2" />
            </div>

            <div class="mt-6">
                <label for="description" class="pm-label">
                    Deskripsi <span class="font-normal text-pm-muted">(opsional)</span>
                </label>
                <textarea class="pm-input @error('description') pm-input-error @enderror"
                          id="description"
                          name="description"
                          rows="4"
                          placeholder="Jelaskan jenis produk yang ada di kategori ini..."
                          maxlength="3000">{{ old('description', $category->description) }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                <p class="mt-2 text-xs text-pm-muted">Maksimal 3.000 karakter.</p>
            </div>

            <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-pm-border pt-6 dark:border-gray-800">
                <button class="pm-button" type="submit">
                    Simpan kategori
                </button>
                <a href="{{ route('admin.categories.index') }}" class="pm-secondary">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-admin-layout>
