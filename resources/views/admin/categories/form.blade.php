<x-admin-layout :title="$category->exists ? 'Edit kategori' : 'Tambah kategori'" subtitle="Berikan nama yang jelas agar produk mudah dikelompokkan.">
    <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="pm-card max-w-2xl p-6 sm:p-8">
        @csrf @if($category->exists) @method('PUT') @endif
        <label for="name" class="pm-label">Nama kategori <span aria-hidden="true">*</span></label><input class="pm-input" id="name" name="name" value="{{ old('name', $category->name) }}" required maxlength="255" autofocus><x-input-error :messages="$errors->get('name')" class="mt-2" />
        <div class="mt-6"><label for="description" class="pm-label">Deskripsi <span class="font-normal text-gray-500">(opsional)</span></label><textarea class="pm-input" id="description" name="description" rows="4" maxlength="3000">{{ old('description', $category->description) }}</textarea><x-input-error :messages="$errors->get('description')" class="mt-2" /></div>
        <div class="mt-8 flex flex-wrap gap-3"><button class="pm-button" type="submit">Simpan kategori</button><a href="{{ route('admin.categories.index') }}" class="pm-secondary">Batal</a></div>
    </form>
</x-admin-layout>
