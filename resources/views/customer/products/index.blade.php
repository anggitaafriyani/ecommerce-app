<x-store-layout title="Katalog produk">
    <section class="mx-auto max-w-7xl px-5 py-10 sm:px-8 sm:py-14">
        <p class="text-xs font-semibold uppercase tracking-widest text-[#2F5D50] dark:text-green-300">Pilihan untuk keseharian mereka</p>
        <h1 class="mt-3 text-4xl font-semibold tracking-tight">Katalog PawMart</h1>
        <p class="mt-4 max-w-xl text-sm leading-7 text-gray-500 dark:text-gray-400">Temukan makanan, perlengkapan perawatan, dan aksesori untuk sahabat kecil Anda.</p>
        <form action="{{ route('products.index') }}" method="GET" class="pm-card mt-8 grid items-end gap-4 p-5 md:grid-cols-[1fr_1fr_auto]">
            <div><label for="q" class="pm-label">Cari produk</label><input id="q" name="q" type="search" class="pm-input" placeholder="Nama produk yang dicari" maxlength="100" value="{{ $filters['q'] ?? '' }}"></div>
            <div><label for="category" class="pm-label">Kategori</label><select id="category" name="category" class="pm-input"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
            <button class="pm-button" type="submit">Tampilkan produk</button>
        </form>
        @if($errors->any())<div role="alert" class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>@endif
        <div class="my-7 flex flex-wrap items-center justify-between gap-3"><p class="text-sm text-gray-500">{{ $products->total() }} produk ditemukan @if(!empty($filters['q'])) untuk “{{ $filters['q'] }}” @endif</p>@if(!empty($filters['q']) || !empty($filters['category']))<a class="pm-link" href="{{ route('products.index') }}">Hapus pencarian & filter</a>@endif</div>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse($products as $product)
                <x-product-card :product="$product" />
            @empty
                <div class="pm-card col-span-full px-6 py-16 text-center"><h2 class="text-xl font-semibold">Belum ada produk yang sesuai</h2><p class="mt-3 text-sm text-gray-500">Coba nama lain atau pilih kategori berbeda.</p><a class="pm-button mt-6" href="{{ route('products.index') }}">Lihat semua produk</a></div>
            @endforelse
        </div>
        <div class="mt-8">{{ $products->links() }}</div>
    </section>
</x-store-layout>
