<x-admin-layout title="Produk toko" subtitle="Kelola harga, persediaan, dan tampilan produk PawMart dalam satu tempat.">
    <x-slot:actions>
        <a href="{{ route('admin.products.create') }}" class="pm-button shadow-xs">
            <svg class="h-4 w-4 shrink-0" width="16" height="16" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
            </svg>
            Tambah produk
        </a>
    </x-slot:actions>

    {{-- Search Form --}}
    <form method="GET" action="{{ route('admin.products.index') }}" class="pm-card mb-6 p-5">
        <div class="flex flex-wrap items-end gap-3 max-w-xl">
            <div class="min-w-0 flex-1">
                <label class="pm-label" for="q">Cari nama produk</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center" aria-hidden="true">
                        <svg class="h-4 w-4 text-pm-muted" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <input class="pm-input !pl-10"
                           id="q"
                           name="q"
                           type="search"
                           maxlength="100"
                           value="{{ request('q') }}"
                           placeholder="Contoh: makanan kucing">
                </div>
            </div>
            <button class="pm-button" type="submit">Cari</button>
            @if(request('q'))
                <a class="pm-secondary" href="{{ route('admin.products.index') }}">Reset</a>
            @endif
        </div>
    </form>

    <p class="mb-3 text-xs text-pm-muted sm:hidden">Geser tabel ke samping untuk melihat semua kolom dan tindakan.</p>

    {{-- Products Table Card --}}
    <div class="pm-card overflow-hidden">
        <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel dapat digulir secara horizontal">
            <table class="pm-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th class="text-right"><span class="sr-only">Tindakan</span>Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-pm-border dark:divide-gray-800">
                    @forelse($products as $product)
                        <tr class="transition-colors hover:bg-pm-cream/50 dark:hover:bg-gray-800/50">
                            {{-- Product info with thumbnail --}}
                            <td>
                                <a href="{{ route('admin.products.show', $product) }}" class="flex min-w-[220px] items-center gap-3.5 group">
                                    @if($product->image_path)
                                        <img class="h-12 w-12 shrink-0 rounded-xl object-cover border border-pm-border dark:border-gray-700"
                                             src="{{ Storage::disk('public')->url($product->image_path) }}"
                                             alt="{{ $product->name }}">
                                    @else
                                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-pm-cream text-xs font-semibold text-pm-muted border border-pm-border dark:bg-gray-800 dark:border-gray-700">
                                            No foto
                                        </span>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-bold text-pm-charcoal group-hover:text-pm-green dark:text-gray-100 dark:group-hover:text-green-400 truncate">
                                            {{ $product->name }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-pm-muted dark:text-gray-400">
                                            {{ $product->category->name }}
                                        </p>
                                    </div>
                                </a>
                            </td>

                            {{-- Price --}}
                            <td class="whitespace-nowrap font-bold text-pm-charcoal dark:text-gray-100">
                                Rp{{ number_format($product->price, 2, ',', '.') }}
                            </td>

                            {{-- Stock --}}
                            <td class="whitespace-nowrap">
                                @if($product->stock === 0)
                                    <span class="inline-flex rounded-full bg-red-50 border border-red-200 px-2.5 py-0.5 text-xs font-bold text-red-800 dark:bg-red-900/40 dark:border-red-800 dark:text-red-300">
                                        Habis
                                    </span>
                                @elseif($product->stock <= 5)
                                    <span class="inline-flex rounded-full bg-amber-50 border border-amber-200 px-2.5 py-0.5 text-xs font-bold text-amber-800 dark:bg-amber-900/40 dark:border-amber-800 dark:text-amber-300">
                                        {{ number_format($product->stock, 0, ',', '.') }} unit
                                    </span>
                                @else
                                    <span class="text-sm text-pm-charcoal dark:text-gray-200">
                                        {{ number_format($product->stock, 0, ',', '.') }} unit
                                    </span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $product->is_active ? 'bg-green-50 border border-green-200 text-green-800 dark:bg-green-900/40 dark:border-green-800 dark:text-green-300' : 'bg-gray-100 border border-gray-200 text-gray-600 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $product->is_active ? 'bg-green-500' : 'bg-gray-400' }}" aria-hidden="true"></span>
                                    {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-3">
                                    <a class="pm-link text-xs font-semibold" href="{{ route('admin.products.edit', $product) }}">
                                        Edit<span class="sr-only"> {{ $product->name }}</span>
                                    </a>
                                    <x-admin-delete :action="route('admin.products.destroy', $product)"
                                                    :name="$product->name"
                                                    :id="'delete-product-'.$product->id"
                                                    message="Produk akan dihapus dari daftar toko. Data, gambar, dan relasi transaksi tetap disimpan sebagai arsip." />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center text-sm text-pm-muted dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-pm-cream text-pm-muted dark:bg-gray-800">📦</span>
                                    <p class="mt-3 font-semibold text-pm-charcoal dark:text-gray-200">Tidak ada produk yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($products->hasPages())
        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @endif
</x-admin-layout>
