<x-admin-layout title="Detail produk" subtitle="Informasi produk yang tersimpan di PawMart.">
    <x-slot:actions><a class="pm-secondary" href="{{ route('admin.products.index') }}">Kembali</a><a class="pm-button" href="{{ route('admin.products.edit', $product) }}">Edit produk</a></x-slot:actions>
    <article class="pm-card grid gap-8 p-6 sm:p-8 md:grid-cols-2">
        <div class="flex aspect-square items-center justify-center rounded-2xl bg-[#FAF7F2] dark:bg-gray-800">@if($product->image_path)<img class="h-full w-full rounded-2xl object-contain" src="{{ Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}">@else<p class="text-sm text-gray-500">Gambar belum tersedia</p>@endif</div>
        <div><p class="text-sm font-semibold text-[#2F5D50] dark:text-green-300">{{ $product->category->name }}</p><h2 class="mt-3 text-3xl font-semibold">{{ $product->name }}</h2><p class="mt-5 text-2xl font-semibold">Rp{{ number_format($product->price, 2, ',', '.') }}</p>
            <div class="mt-5 flex flex-wrap gap-3"><span class="rounded-full bg-green-50 px-3 py-1 text-sm text-green-900">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</span><span class="rounded-full bg-orange-50 px-3 py-1 text-sm text-orange-900">{{ $product->stock === 0 ? 'Stok habis' : $product->stock.' unit tersedia' }}</span></div>
            <h3 class="mt-8 font-semibold">Deskripsi</h3><p class="mt-3 whitespace-pre-line break-words text-sm leading-7 text-gray-600 dark:text-gray-300">{{ $product->description }}</p>
            <div class="mt-8 border-t border-[#E6E4DF] pt-5 dark:border-gray-700"><x-admin-delete :action="route('admin.products.destroy', $product)" :name="$product->name" :id="'delete-product-'.$product->id" message="Produk akan dihapus dari daftar toko. Data, gambar, dan relasi transaksi tetap disimpan sebagai arsip." /></div>
        </div>
    </article>
</x-admin-layout>
