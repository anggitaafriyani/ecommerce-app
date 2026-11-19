<x-admin-layout title="Detail produk" subtitle="Informasi produk yang tersimpan di PawMart.">
    <x-slot:actions>
        <div class="flex items-center gap-3">
            <a class="pm-secondary" href="{{ route('admin.products.index') }}">
                ← Kembali
            </a>
            <a class="pm-button" href="{{ route('admin.products.edit', $product) }}">
                Edit produk
            </a>
        </div>
    </x-slot:actions>

    <article class="pm-card grid gap-8 p-6 sm:p-10 md:grid-cols-2">
        {{-- Product Image View --}}
        <div class="flex aspect-square items-center justify-center overflow-hidden rounded-2xl border border-pm-border bg-pm-cream/70 dark:border-gray-800 dark:bg-gray-800">
            @if($product->image_path)
                <img class="h-full w-full object-contain"
                     src="{{ Storage::disk('public')->url($product->image_path) }}"
                     alt="{{ $product->name }}">
            @else
                <div class="flex flex-col items-center gap-2 text-pm-muted">
                    <span class="text-4xl">📷</span>
                    <p class="text-sm font-medium">Gambar belum tersedia</p>
                </div>
            @endif
        </div>

        {{-- Product Info --}}
        <div class="flex flex-col justify-between">
            <div>
                <span class="inline-flex rounded-full bg-pm-green-lt px-3 py-1 text-xs font-bold uppercase tracking-wider text-pm-green dark:bg-green-900/40 dark:text-green-300">
                    {{ $product->category->name }}
                </span>

                <h2 class="mt-3 text-2xl font-bold text-pm-charcoal dark:text-gray-100 sm:text-3xl">
                    {{ $product->name }}
                </h2>

                <p class="mt-4 text-3xl font-extrabold text-pm-green dark:text-green-400">
                    Rp{{ number_format($product->price, 2, ',', '.') }}
                </p>

                <div class="mt-5 flex flex-wrap items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $product->is_active ? 'bg-green-50 border border-green-200 text-green-900 dark:bg-green-900/40 dark:text-green-300' : 'bg-gray-100 border border-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $product->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>

                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $product->stock === 0 ? 'bg-red-50 border border-red-200 text-red-900 dark:bg-red-900/40 dark:text-red-300' : 'bg-amber-50 border border-amber-200 text-amber-900 dark:bg-amber-900/40 dark:text-amber-300' }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $product->stock === 0 ? 'bg-red-500' : 'bg-amber-500' }}"></span>
                        {{ $product->stock === 0 ? 'Stok habis' : $product->stock.' unit tersedia' }}
                    </span>

                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 border border-blue-200 px-3 py-1 text-xs font-semibold text-blue-900 dark:bg-blue-900/40 dark:text-blue-300">
                        ⚖️ {{ number_format($product->weight ?? 500, 0, ',', '.') }} gram
                    </span>
                </div>

                <div class="mt-8 border-t border-pm-border pt-6 dark:border-gray-800">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-pm-muted dark:text-gray-400">Deskripsi</h3>
                    <p class="mt-3 whitespace-pre-line break-words text-sm leading-7 text-pm-charcoal dark:text-gray-300">
                        {{ $product->description }}
                    </p>
                </div>
            </div>

            <div class="mt-8 border-t border-pm-border pt-5 dark:border-gray-800">
                <x-admin-delete :action="route('admin.products.destroy', $product)"
                                :name="$product->name"
                                :id="'delete-product-'.$product->id"
                                message="Produk akan dihapus dari daftar toko. Data, gambar, dan relasi transaksi tetap disimpan sebagai arsip." />
            </div>
        </div>
    </article>
</x-admin-layout>
