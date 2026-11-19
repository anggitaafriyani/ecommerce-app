<x-store-layout :title="$product->name">
    <div class="mx-auto max-w-7xl px-5 py-8 sm:px-8 sm:py-12">
        {{-- Breadcrumbs --}}
        <nav aria-label="Jejak navigasi" class="mb-8 flex flex-wrap items-center gap-2 text-sm text-pm-muted dark:text-gray-400">
            <a class="transition-colors hover:text-pm-orange font-medium" href="{{ route('home') }}">Beranda</a>
            <span aria-hidden="true" class="text-pm-border dark:text-gray-600">/</span>
            <a class="transition-colors hover:text-pm-orange font-medium" href="{{ route('products.index') }}">Produk</a>
            <span aria-hidden="true" class="text-pm-border dark:text-gray-600">/</span>
            <span class="font-bold text-pm-charcoal dark:text-gray-200 break-words">{{ $product->name }}</span>
        </nav>

        {{-- Main Article --}}
        <article class="grid items-start gap-10 lg:grid-cols-2 lg:gap-16">
            {{-- Left: Product Visual --}}
            <div class="overflow-hidden rounded-[2.5rem] border border-pm-border/80 bg-white p-4 shadow-card dark:border-gray-800 dark:bg-gray-900">
                <div class="relative aspect-square w-full overflow-hidden rounded-3xl bg-pm-cream/70 p-6 dark:bg-gray-800">
                    <x-product-image :product="$product" class="h-full w-full object-contain" />
                    @if($product->stock === 0)
                        <div class="absolute inset-0 flex items-center justify-center bg-white/70 backdrop-blur-[2px] dark:bg-gray-900/70">
                            <span class="rounded-full bg-pm-charcoal px-5 py-2 text-sm font-bold text-white shadow-card">
                                Stok habis
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right: Product Details & Purchase Card --}}
            <div>
                {{-- Category pill --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('products.index', ['category' => $product->category_id]) }}"
                       class="inline-flex items-center gap-1.5 rounded-full bg-pm-orange-lt px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-pm-orange transition-colors hover:bg-pm-orange hover:text-white dark:bg-orange-950/60 dark:text-orange-300">
                        <svg class="h-3 w-3" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true">
                            <circle cx="6" cy="6" r="3"/>
                        </svg>
                        {{ $product->category->name }}
                    </a>

                    {{-- Star Rating --}}
                    <div class="flex items-center gap-1.5 text-xs text-pm-muted">
                        <div class="flex text-amber-400">
                            @for($i = 0; $i < 5; $i++)
                                <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <span class="font-bold text-pm-charcoal">(4.9/5)</span>
                    </div>
                </div>

                {{-- Product Title --}}
                <h1 class="mt-4 break-words text-3xl font-extrabold leading-tight tracking-tight text-pm-charcoal dark:text-gray-50 sm:text-4xl">
                    {{ $product->name }}
                </h1>

                {{-- Price --}}
                <div class="mt-5 flex items-baseline">
                    <span class="text-3xl font-extrabold text-pm-charcoal dark:text-gray-100 sm:text-4xl">Rp{{ number_format($product->price, 2, ',', '.') }}</span>
                </div>

                {{-- Stock status badge --}}
                <div class="mt-4">
                    @if($product->stock > 0)
                        <span class="inline-flex items-center gap-2 rounded-full bg-green-50 px-3.5 py-1.5 text-xs font-bold text-green-800 dark:bg-green-900/40 dark:text-green-300">
                            <span class="h-2 w-2 rounded-full bg-green-500 animate-pulse" aria-hidden="true"></span>
                            Stok tersedia: {{ number_format($product->stock, 0, ',', '.') }} unit
                        </span>
                    @else
                        <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3.5 py-1.5 text-xs font-bold text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                            <span class="h-2 w-2 rounded-full bg-gray-400" aria-hidden="true"></span>
                            Stok habis
                        </span>
                    @endif
                </div>

                {{-- Description --}}
                <div class="mt-7 border-t border-pm-border/80 pt-6 dark:border-gray-800">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-pm-charcoal dark:text-gray-200">Tentang produk</h2>
                    <p class="mt-3 whitespace-pre-line break-words text-sm leading-7 text-pm-muted dark:text-gray-300">
                        {{ $product->description }}
                    </p>
                </div>

                {{-- Purchase Form Card --}}
                <form class="pm-card mt-8 p-6 rounded-[2rem] border-pm-border/80" method="POST" action="{{ route('cart.store', $product) }}">
                    @csrf
                    <div>
                        <label class="pm-label" for="quantity">Jumlah</label>
                        <div class="flex items-center gap-3">
                            <input id="quantity"
                                   name="quantity"
                                   x-data
                                   x-on:change="$el.value = Math.min(Number($el.max), Math.max(1, Math.trunc(Number($el.value) || 1)))"
                                   type="number"
                                   class="pm-input !rounded-full max-w-28 text-center font-bold"
                                   min="1"
                                   max="{{ max(1, $product->stock) }}"
                                   step="1"
                                   value="{{ $product->stock > 0 ? 1 : '' }}"
                                   @disabled($product->stock === 0)
                                   aria-describedby="quantity-help">
                            <span class="text-xs font-semibold text-pm-muted dark:text-gray-400">unit</span>
                        </div>
                        <p id="quantity-help" class="mt-2 text-xs text-pm-muted dark:text-gray-400">
                            {{ $product->stock > 0 ? 'Pilih 1 hingga '.$product->stock.' unit. ' : 'Jumlah tidak dapat dipilih karena stok habis.' }}
                        </p>
                    </div>

                    @if($product->stock === 0 || (auth()->check() && auth()->user()->role === 'admin'))
                        <button type="button" disabled class="pm-button !rounded-full mt-6 w-full opacity-60 cursor-not-allowed">Tambah ke Keranjang</button>
                        <p class="mt-3 text-center text-xs text-pm-muted dark:text-gray-400">
                            Pembelian diperuntukkan bagi Pelanggan dengan stok tersedia.
                        </p>
                    @elseif(auth()->guest())
                        <a class="pm-button !rounded-full mt-6 w-full font-bold" href="{{ route('shop.login', $product->slug) }}">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M1 1.75A.75.75 0 011.75 1h1.628a1.75 1.75 0 011.734 1.51L5.33 3h9.56a1.75 1.75 0 011.728 2.02l-.82 5a1.75 1.75 0 01-1.728 1.48H6.0l.294 1.5H15a.75.75 0 010 1.5H5.5a.75.75 0 01-.737-.616L3.001 3.49A.25.25 0 002.753 3H1.75A.75.75 0 011 2.25z"/>
                            </svg>
                            Tambah ke Keranjang
                        </a>
                        <p class="mt-3 text-center text-xs text-pm-muted dark:text-gray-400">
                            Masuk sebagai Pelanggan untuk menambahkan produk.
                        </p>
                    @else
                        <button class="pm-button !rounded-full mt-6 w-full font-bold" type="submit">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M1 1.75A.75.75 0 011.75 1h1.628a1.75 1.75 0 011.734 1.51L5.33 3h9.56a1.75 1.75 0 011.728 2.02l-.82 5a1.75 1.75 0 01-1.728 1.48H6.0l.294 1.5H15a.75.75 0 010 1.5H5.5a.75.75 0 01-.737-.616L3.001 3.49A.25.25 0 002.753 3H1.75A.75.75 0 011 2.25z"/>
                            </svg>
                            Tambah ke Keranjang
                        </button>
                    @endif
                </form>

                {{-- Trust badges --}}
                <div class="mt-8 grid grid-cols-3 gap-3 rounded-2xl border border-pm-border/80 bg-pm-cream/50 p-4 text-center dark:border-gray-800 dark:bg-gray-800/50">
                    <div>
                        <svg class="mx-auto h-5 w-5 text-pm-orange" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                        </svg>
                        <p class="mt-1.5 text-xs font-bold text-pm-charcoal dark:text-gray-200">100% Asli</p>
                    </div>
                    <div>
                        <svg class="mx-auto h-5 w-5 text-pm-orange" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M12.232 4.232a2.5 2.5 0 013.536 3.536l-1.225 1.224a.75.75 0 001.061 1.06l1.224-1.224a4 4 0 00-5.656-5.656l-3 3a4 4 0 00.225 5.865.75.75 0 00.977-1.138 2.5 2.5 0 01-.142-3.667l3-3z"/>
                            <path d="M11.603 7.963a.75.75 0 00-.977 1.138 2.5 2.5 0 01.142 3.667l-3 3a2.5 2.5 0 01-3.536-3.536l1.225-1.224a.75.75 0 00-1.061-1.06l-1.224 1.224a4 4 0 105.656 5.656l3-3a4 4 0 00-.225-5.865z"/>
                        </svg>
                        <p class="mt-1.5 text-xs font-bold text-pm-charcoal dark:text-gray-200">Higienis</p>
                    </div>
                    <div>
                        <svg class="mx-auto h-5 w-5 text-pm-orange" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd"/>
                        </svg>
                        <p class="mt-1.5 text-xs font-bold text-pm-charcoal dark:text-gray-200">Aman</p>
                    </div>
                </div>

                {{-- Back button --}}
                <div class="mt-8">
                    <a class="pm-link inline-flex items-center gap-1.5 text-sm" href="{{ route('products.index') }}">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 011 10z" clip-rule="evenodd"/>
                        </svg>
                        Kembali ke katalog
                    </a>
                </div>
            </div>
        </article>

        {{-- ════════════════════════════════════════
             SECTION: ULASAN PELANGGAN (Pet&Co Theme)
        ════════════════════════════════════════ --}}
        <section class="mt-16 border-t border-pm-border/80 pt-12 dark:border-gray-800">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-pm-orange-lt px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-pm-orange">
                        Ulasan Pembeli
                    </span>
                    <h2 class="mt-2 text-2xl font-extrabold text-pm-charcoal dark:text-gray-50">
                        Ulasan &amp; Pengalaman Pembeli
                    </h2>
                    <p class="text-xs text-pm-muted dark:text-gray-400 mt-0.5">
                        Testimoni riil dari pembeli yang telah menerima produk ini
                    </p>
                </div>

                {{-- Rating Summary Badge --}}
                @php
                    $reviewCount = $product->reviews->count();
                    $avgRating = $reviewCount > 0 ? round($product->reviews->avg('rating'), 1) : 5.0;
                @endphp
                <div class="flex items-center gap-3 rounded-2xl bg-pm-cream/70 p-3.5 border border-pm-orange/20 dark:bg-gray-800 self-start sm:self-auto">
                    <div class="text-center">
                        <span class="text-2xl font-black text-pm-orange">{{ $reviewCount > 0 ? number_format($avgRating, 1) : '-' }}</span>
                        <span class="text-[11px] text-pm-muted block">dari 5.0</span>
                    </div>
                    <div class="border-l border-pm-border/80 pl-3 dark:border-gray-700">
                        <div class="flex text-amber-400 text-sm">
                            @for($s = 1; $s <= 5; $s++)
                                <span>{{ $avgRating >= $s ? '★' : '☆' }}</span>
                            @endfor
                        </div>
                        <span class="text-xs font-bold text-pm-charcoal dark:text-gray-200 mt-0.5 block">
                            {{ $reviewCount }} Ulasan Terverifikasi
                        </span>
                    </div>
                </div>
            </div>

            {{-- Reviews Grid or Empty State --}}
            @if($product->reviews->isNotEmpty())
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($product->reviews as $rev)
                        <div class="pm-card p-5 rounded-2xl border-pm-border/80 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between pb-3 border-b border-pm-border/50 dark:border-gray-800">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-pm-orange-lt text-pm-orange font-bold text-xs uppercase">
                                            {{ substr($rev->user?->name ?? 'P', 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-xs text-pm-charcoal dark:text-gray-100">
                                                {{ Str::mask($rev->user?->name ?? 'Pelanggan', '*', 3) }}
                                            </p>
                                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">
                                                ✓ Pembeli Terverifikasi
                                            </span>
                                        </div>
                                    </div>
                                    <span class="text-[11px] text-pm-muted">
                                        {{ $rev->created_at->format('d M Y') }}
                                    </span>
                                </div>

                                <div class="mt-3 flex items-center gap-1 text-amber-400 text-xs">
                                    @for($s = 1; $s <= 5; $s++)
                                        <span>{{ $rev->rating >= $s ? '★' : '☆' }}</span>
                                    @endfor
                                    <span class="ml-1 text-xs font-bold text-pm-charcoal dark:text-gray-200">({{ $rev->rating }}.0)</span>
                                </div>

                                <p class="mt-2.5 text-xs text-pm-charcoal dark:text-gray-300 leading-relaxed">
                                    {{ $rev->comment }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="pm-card flex flex-col items-center p-8 text-center sm:p-12 rounded-[2rem] border-dashed border-pm-border">
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-pm-orange-lt text-pm-orange text-2xl">
                        ⭐
                    </span>
                    <h3 class="mt-3 text-base font-bold text-pm-charcoal dark:text-gray-100">
                        Belum Ada Ulasan untuk Produk Ini
                    </h3>
                    <p class="mt-1 max-w-sm text-xs text-pm-muted dark:text-gray-400">
                        Jadilah yang pertama memberikan ulasan setelah memesan dan menerima produk anabul ini!
                    </p>
                </div>
            @endif
        </section>
    </div>
</x-store-layout>
