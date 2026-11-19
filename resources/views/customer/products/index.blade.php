<x-store-layout title="Katalog produk">
    @php
        $pawSvg = '<svg class="w-full h-full fill-current" viewBox="0 0 64 64"><ellipse cx="17" cy="22" rx="7" ry="10" transform="rotate(-25 17 22)"/><ellipse cx="32" cy="15" rx="7" ry="10"/><ellipse cx="47" cy="22" rx="7" ry="10" transform="rotate(25 47 22)"/><path d="M32 31c-7 0-10 5-15 11-8 11-1 18 8 14 5-2 9-2 14 0 9 4 16-3 8-14-5-6-8-11-15-11z"/></svg>';
        $catIcons = [
            0 => '🥣',
            1 => '🧼',
            2 => '🎾',
            3 => '🏠',
        ];
    @endphp

    <div class="mx-auto max-w-7xl px-5 py-8 sm:px-8 sm:py-12">
        {{-- Page Header --}}
        <div class="mb-8 text-center max-w-2xl mx-auto">
            <div class="inline-flex items-center gap-2 rounded-full bg-pm-orange-lt px-3.5 py-1 text-xs font-bold text-pm-orange">
                <span class="h-4 w-4">{!! $pawSvg !!}</span>
                Koleksi Pilihan Sahabat Bulu
            </div>
            <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-pm-charcoal dark:text-gray-50 sm:text-4xl">
                Katalog Produk PawMart
            </h1>
            <p class="mt-2 text-sm text-pm-muted dark:text-gray-400">
                Penuhi segala kebutuhan nutrisi, perawatan harian, dan mainan menyenangkan untuk hewan peliharaan Anda.
            </p>
        </div>

        {{-- Circular Category Filter Pills (Shop by Category like reference) --}}
        <div class="mb-10 flex flex-wrap items-center justify-center gap-4 sm:gap-6">
            <a href="{{ route('products.index') }}"
               class="flex flex-col items-center gap-2 group">
                <div class="flex h-16 w-16 items-center justify-center rounded-full border-2 {{ empty($filters['category']) ? 'border-pm-orange bg-pm-orange-lt text-pm-orange' : 'border-pm-border bg-white text-pm-charcoal' }} shadow-xs transition-all group-hover:border-pm-orange group-hover:scale-105">
                    <span class="text-2xl">🐾</span>
                </div>
                <span class="text-xs font-bold {{ empty($filters['category']) ? 'text-pm-orange' : 'text-pm-charcoal' }}">Semua</span>
            </a>

            @foreach($categories as $index => $cat)
                <a href="{{ route('products.index', ['category' => $cat->id, 'q' => $filters['q'] ?? null]) }}"
                   class="flex flex-col items-center gap-2 group">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full border-2 {{ ($filters['category'] ?? '') == $cat->id ? 'border-pm-orange bg-pm-orange-lt text-pm-orange' : 'border-pm-border bg-white text-pm-charcoal' }} shadow-xs transition-all group-hover:border-pm-orange group-hover:scale-105">
                        <span class="text-2xl">{{ $catIcons[$index % count($catIcons)] }}</span>
                    </div>
                    <span class="text-xs font-bold {{ ($filters['category'] ?? '') == $cat->id ? 'text-pm-orange' : 'text-pm-charcoal' }}">{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>

        {{-- Search & Filter Bar --}}
        <form action="{{ route('products.index') }}" method="GET"
              class="pm-card mb-8 p-5 rounded-[2rem] border-pm-border/80">
            <div class="grid items-end gap-4 sm:grid-cols-[1fr_auto_auto]">
                <div>
                    <label for="q" class="pm-label">Cari nama produk</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center" aria-hidden="true">
                            <svg class="h-4 w-4 text-pm-muted" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <input id="q" name="q" type="search"
                               class="pm-input !rounded-full !pl-10 text-xs sm:text-sm"
                               placeholder="Nama produk yang dicari…"
                               maxlength="100"
                               value="{{ $filters['q'] ?? '' }}">
                    </div>
                </div>
                <div>
                    <label for="category" class="pm-label">Kategori</label>
                    <select id="category" name="category" class="pm-input !rounded-full min-w-[160px] text-xs sm:text-sm">
                        <option value="">Semua kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button class="pm-button !rounded-full !px-6 font-bold" type="submit">Tampilkan</button>
            </div>

            {{-- Active filters --}}
            @if(!empty($filters['q']) || !empty($filters['category']))
                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-pm-border/80 pt-4 dark:border-gray-700">
                    <span class="text-xs font-semibold text-pm-muted">Filter aktif:</span>
                    @if(!empty($filters['q']))
                        <span class="pm-badge bg-pm-orange-lt text-pm-orange dark:bg-orange-950/60 dark:text-orange-300">
                            Cari: "{{ $filters['q'] }}"
                        </span>
                    @endif
                    @if(!empty($filters['category']))
                        @php($selectedCat = $categories->firstWhere('id', $filters['category']))
                        @if($selectedCat)
                            <span class="pm-badge bg-pm-orange-lt text-pm-orange dark:bg-orange-950/60 dark:text-orange-300">
                                Kategori: {{ $selectedCat->name }}
                            </span>
                        @endif
                    @endif
                    <a class="pm-link ml-auto text-xs font-bold" href="{{ route('products.index') }}">
                        Hapus pencarian & filter
                    </a>
                </div>
            @endif
        </form>

        {{-- Error messages --}}
        @if($errors->any())
            <div class="pm-alert-error mb-6" role="alert">
                <svg class="h-5 w-5 shrink-0 text-red-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        {{-- Results bar --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-pm-muted dark:text-gray-400">
                <span class="font-bold text-pm-charcoal dark:text-gray-100">{{ number_format($products->total(), 0, ',', '.') }}</span>
                produk ditemukan
                @if(!empty($filters['q']))
                    untuk "<span class="font-bold text-pm-charcoal dark:text-gray-100">{{ $filters['q'] }}</span>"
                @endif
            </p>
        </div>

        {{-- Products Grid --}}
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse($products as $product)
                <x-product-card :product="$product" />
            @empty
                <div class="col-span-full">
                    <div class="pm-card flex flex-col items-center px-8 py-20 text-center rounded-[2rem]">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-pm-orange-lt text-pm-orange dark:bg-gray-800">
                            <span class="h-8 w-8">{!! $pawSvg !!}</span>
                        </div>
                        <h2 class="mt-5 text-xl font-bold text-pm-charcoal dark:text-gray-100">
                            Belum ada produk yang sesuai
                        </h2>
                        <p class="mt-3 max-w-sm text-sm leading-6 text-pm-muted dark:text-gray-400">
                            Coba nama lain atau pilih kategori berbeda. Kami terus menambah koleksi baru.
                        </p>
                        @if(!empty($filters['q']) || !empty($filters['category']))
                            <a class="pm-button !rounded-full mt-7" href="{{ route('products.index') }}">
                                Hapus pencarian & filter
                            </a>
                        @else
                            <a class="pm-button !rounded-full mt-7" href="{{ route('products.index') }}">
                                Lihat semua produk
                            </a>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
            <div class="mt-12">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</x-store-layout>