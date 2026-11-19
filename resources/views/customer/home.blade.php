<x-store-layout>
    {{-- Paw Print SVG Definition for reuse --}}
    @php
        $pawSvg = '<svg class="w-full h-full fill-current" viewBox="0 0 64 64"><ellipse cx="17" cy="22" rx="7" ry="10" transform="rotate(-25 17 22)"/><ellipse cx="32" cy="15" rx="7" ry="10"/><ellipse cx="47" cy="22" rx="7" ry="10" transform="rotate(25 47 22)"/><path d="M32 31c-7 0-10 5-15 11-8 11-1 18 8 14 5-2 9-2 14 0 9 4 16-3 8-14-5-6-8-11-15-11z"/></svg>';
    @endphp

    {{-- ════════════════════════════════════════
         1. HERO SECTION (Pet&Co Asymmetrical Hero)
    ════════════════════════════════════════ --}}
    <section class="relative overflow-hidden bg-white pb-16 pt-8 dark:bg-gray-900 sm:pb-24 sm:pt-12">
        {{-- Floating Paw Prints Background Accent --}}
        <div class="pointer-events-none absolute inset-0 overflow-hidden text-pm-orange/10 dark:text-orange-500/5" aria-hidden="true">
            <div class="absolute left-6 top-16 h-14 w-14 rotate-12">{!! $pawSvg !!}</div>
            <div class="absolute bottom-12 left-1/4 h-20 w-20 -rotate-12 opacity-75">{!! $pawSvg !!}</div>
            <div class="absolute right-1/3 top-8 h-10 w-10 rotate-45 opacity-60">{!! $pawSvg !!}</div>
        </div>

        <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-5 sm:px-8 lg:grid-cols-12 lg:gap-8">
            {{-- Left Column: Headline & CTA --}}
            <div class="lg:col-span-6 xl:col-span-5">
                <span class="inline-flex items-center gap-2 rounded-full bg-pm-orange-lt px-3.5 py-1.5 text-xs font-bold text-pm-orange dark:bg-orange-950/60 dark:text-orange-300">
                    <span class="h-2 w-2 rounded-full bg-pm-orange animate-pulse"></span>
                    Sahabat Terbaik Hewan Peliharaan
                </span>

                <h1 class="mt-5 text-4xl font-extrabold tracking-tight text-pm-charcoal dark:text-gray-50 sm:text-5xl xl:text-6xl leading-[1.12]">
                    Everything Your Pet Deserves, Delivered to Your Doorstep!
                </h1>

                <p class="mt-6 text-base leading-relaxed text-pm-muted dark:text-gray-300 sm:text-lg">
                    Makanan bernutrisi, perlengkapan higienis, dan mainan seru yang dipilih dengan penuh cinta untuk kesehatan dan keceriaan sahabat kecil Anda setiap hari.
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a href="{{ route('products.index') }}"
                       class="pm-button !rounded-full !px-8 !py-3.5 !text-base font-bold bg-pm-orange hover:bg-pm-orange-dk text-white shadow-card hover:shadow-hover hover:-translate-y-0.5 transition-all">
                        Belanja Sekarang
                    </a>
                    <a href="#categories"
                       class="pm-secondary !rounded-full !px-7 !py-3.5 !text-base font-semibold">
                        Lihat Kategori
                    </a>
                </div>

                {{-- Trust mini row --}}
                <div class="mt-10 flex items-center gap-6 border-t border-pm-border/80 pt-6 text-xs font-semibold text-pm-muted dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-pm-orange"></span>
                        100% Asli & Terjamin
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-pm-orange"></span>
                        Stok Real-Time
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-pm-orange"></span>
                        Midtrans Sandbox Aman
                    </div>
                </div>
            </div>

            {{-- Right Column: Organic Orange Shape with Pets & Floating Badges --}}
            <div class="relative lg:col-span-6 xl:col-span-7">
                {{-- Organic Orange Backdrop Blob --}}
                <div class="relative mx-auto flex w-full max-w-lg items-center justify-center overflow-hidden rounded-[3rem] sm:rounded-[4rem] lg:rounded-bl-[160px] bg-pm-orange px-[12%] py-[6%] shadow-hover lg:max-w-none">
                    {{-- Paw prints stamped inside orange blob --}}
                    <div class="pointer-events-none absolute inset-0 text-white/15" aria-hidden="true">
                        <div class="absolute left-8 top-10 h-16 w-16 -rotate-12">{!! $pawSvg !!}</div>
                        <div class="absolute right-12 top-6 h-24 w-24 rotate-45">{!! $pawSvg !!}</div>
                        <div class="absolute bottom-20 left-12 h-20 w-20 rotate-12">{!! $pawSvg !!}</div>
                        <div class="absolute bottom-8 right-16 h-14 w-14 -rotate-45">{!! $pawSvg !!}</div>
                    </div>

                    {{-- Main Hero Pets Photo --}}
                    <div class="relative z-10 aspect-[1.08/1] w-full overflow-hidden rounded-[17%_17%_15%_15%]">
                        <img
                            src="{{ asset('images/hero-pets.png') }}"
                            alt="Sahabat bulu anjing dan kucing PawMart"
                            class="h-full w-full object-cover object-[50%_55%]"
                            loading="eager"
                            width="1024"
                            height="1024"
                        >
                    </div>

                    {{-- Floating Pet Avatars (like Pet&Co reference) --}}
                    <div class="absolute right-[3%] top-[6%] z-20 flex w-[8%] flex-col items-center gap-2 sm:gap-3" aria-hidden="true">
                        <div class="flex aspect-square w-full items-center justify-center rounded-full border-2 border-white bg-white/95 p-1 shadow-card backdrop-blur-sm transition-transform hover:scale-110">
                            <span class="text-sm sm:text-xl">🐶</span>
                        </div>
                        <div class="flex aspect-square w-full items-center justify-center rounded-full border-2 border-white bg-white/95 p-1 shadow-card backdrop-blur-sm transition-transform hover:scale-110">
                            <span class="text-sm sm:text-xl">🐱</span>
                        </div>
                        <div class="flex aspect-square w-5/6 items-center justify-center rounded-full border-2 border-white bg-white/95 p-1 shadow-card backdrop-blur-sm transition-transform hover:scale-110">
                            <span class="w-full text-pm-orange">{!! $pawSvg !!}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════
         2. FEATURED COLLECTIONS (Circular Category Cards)
    ════════════════════════════════════════ --}}
    <section id="categories" class="scroll-mt-nav bg-pm-cream/50 py-16 sm:py-20 dark:bg-gray-950">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold tracking-tight text-pm-charcoal dark:text-gray-50 sm:text-4xl">
                    Featured Collections
                </h2>
                <p class="mt-2 text-sm text-pm-muted dark:text-gray-400">
                    Pilih kategori kebutuhan favorit untuk sahabat peliharaan Anda
                </p>
            </div>

            <div class="mt-12 flex flex-wrap items-center justify-center gap-8 sm:gap-12">

                @forelse($categories as $category)
                    <a href="{{ route('products.index', ['category' => $category->id]) }}"
                       class="pm-collection-circle group">
                        <div class="pm-collection-circle-img">
                            <x-category-image :category="$category" :illustration="true" />
                        </div>
                        <div class="text-center">
                            <h3 class="text-sm font-bold text-pm-charcoal group-hover:text-pm-orange transition-colors dark:text-gray-100">
                                {{ $category->name }}
                            </h3>
                            <span class="text-xs text-pm-muted">
                                {{ $category->products_count }} Produk
                            </span>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-pm-muted">Kategori sedang disiapkan. Silakan kembali lagi nanti.</p>
                @endforelse

                {{-- Additional friendly pet-type quick links --}}
                <a href="{{ route('products.index', ['q' => 'kucing']) }}" class="pm-collection-circle group">
                    <div class="pm-collection-circle-img">
                        <svg class="h-full w-full rounded-full" viewBox="0 0 120 120" role="img" aria-label="Kucing">
                            <circle cx="60" cy="60" r="60" fill="#fff7ed" />
                            <path d="M30 52V27l22 14a38 38 0 0 1 16 0l22-14v25c6 7 8 14 6 22-3 15-18 23-36 23S27 89 24 74c-2-8 0-15 6-22z" fill="#fed7aa" stroke="#c2410c" stroke-width="3" stroke-linejoin="round" />
                            <path d="M37 44v-6l9 6m28 0 9-6v6" fill="#fb923c" stroke="#fb923c" stroke-width="3" stroke-linejoin="round" />
                            <path d="M54 43l6 9 6-9" fill="#f97316" />
                            <path d="M42 62v5m36-5v5" stroke="#9a3412" stroke-width="4" stroke-linecap="round" />
                            <path d="m54 73 6 6 6-6z" fill="#c2410c" stroke="#c2410c" stroke-width="2" stroke-linejoin="round" />
                            <path d="M60 79c-2 6-7 7-11 3m11-3c2 6 7 7 11 3M36 75l-15-3m15 10-14 3m62-10 15-3M84 82l14 3" fill="none" stroke="#c2410c" stroke-width="2.5" stroke-linecap="round" />
                        </svg>
                    </div>
                    <div class="text-center">
                        <h3 class="text-sm font-bold text-pm-charcoal group-hover:text-pm-orange transition-colors dark:text-gray-100">Kucing</h3>
                        <span class="text-xs text-pm-muted">Koleksi Khusus</span>
                    </div>
                </a>

                <a href="{{ route('products.index', ['q' => 'anjing']) }}" class="pm-collection-circle group">
                    <div class="pm-collection-circle-img">
                        <svg class="h-full w-full rounded-full" viewBox="0 0 120 120" role="img" aria-label="Anjing">
                            <circle cx="60" cy="60" r="60" fill="#fff7ed" />
                            <path d="M37 35c-10-5-16 5-18 17l-3 22c-1 10 10 15 17 6l10-20m40-25c10-5 16 5 18 17l3 22c1 10-10 15-17 6L77 60" fill="#fb923c" stroke="#c2410c" stroke-width="3" stroke-linejoin="round" />
                            <path d="M60 29c-20 0-29 14-29 35v10c0 16 13 24 29 24s29-8 29-24V64c0-21-9-35-29-35z" fill="#fed7aa" stroke="#c2410c" stroke-width="3" />
                            <ellipse cx="45" cy="58" rx="11" ry="15" fill="#fb923c" />
                            <path d="M45 58v5m30-5v5" stroke="#9a3412" stroke-width="4" stroke-linecap="round" />
                            <ellipse cx="60" cy="79" rx="19" ry="13" fill="#ffedd5" />
                            <path d="M54 85v9a6 6 0 0 0 12 0v-9" fill="#f97316" stroke="#c2410c" stroke-width="2.5" />
                            <path d="M53 73q7-5 14 0l-7 7z" fill="#c2410c" stroke="#c2410c" stroke-width="2" stroke-linejoin="round" />
                            <path d="M60 80c-2 6-7 7-11 3m11-3c2 6 7 7 11 3" fill="none" stroke="#c2410c" stroke-width="2.5" stroke-linecap="round" />
                        </svg>
                    </div>
                    <div class="text-center">
                        <h3 class="text-sm font-bold text-pm-charcoal group-hover:text-pm-orange transition-colors dark:text-gray-100">Anjing</h3>
                        <span class="text-xs text-pm-muted">Koleksi Khusus</span>
                    </div>
                </a>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════
         3. BEST SELLING (Featured Products Grid)
    ════════════════════════════════════════ --}}
    <section class="bg-white py-16 sm:py-20 dark:bg-gray-900">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="mb-10 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <h2 class="text-3xl font-extrabold tracking-tight text-pm-charcoal dark:text-gray-50 sm:text-4xl">
                        Best Selling
                    </h2>
                    <span class="h-6 w-6 text-pm-orange" aria-hidden="true">
                        {!! $pawSvg !!}
                    </span>
                </div>
                <a href="{{ route('products.index') }}" class="pm-link font-bold text-pm-orange hover:text-pm-orange-dk">
                    Lihat Seluruh Produk →
                </a>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @forelse($products as $product)
                    <x-product-card :product="$product" />
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-pm-border p-12 text-center">
                        <p class="text-pm-muted">Produk sedang disiapkan. Nantikan koleksi kami.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════
         4. PROMOTIONAL BANNER ("Limited Time Discount Just for You!")
    ════════════════════════════════════════ --}}
    <section class="mx-auto max-w-7xl px-5 py-8 sm:px-8 sm:py-12">
        <div class="relative overflow-hidden rounded-[2.5rem] bg-pm-orange p-8 text-white shadow-card sm:p-14">
            {{-- Decorative Paw Patterns --}}
            <div class="pointer-events-none absolute inset-0 text-white/10" aria-hidden="true">
                <div class="absolute -left-6 top-4 h-32 w-32 rotate-12">{!! $pawSvg !!}</div>
                <div class="absolute right-8 top-6 h-28 w-28 -rotate-12">{!! $pawSvg !!}</div>
                <div class="absolute bottom-2 left-1/3 h-20 w-20 rotate-45">{!! $pawSvg !!}</div>
            </div>

            <div class="relative z-10 flex flex-col items-center text-center max-w-2xl mx-auto">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-1 text-xs font-bold uppercase tracking-wider text-white backdrop-blur-sm">
                    Penawaran Terbatas
                </span>

                <h2 class="mt-4 text-3xl font-extrabold sm:text-4xl lg:text-5xl leading-tight">
                    Limited Time Discount Just for You!
                </h2>

                <p class="mt-4 text-sm sm:text-base text-white/90 leading-relaxed">
                    Dapatkan nutrisi terbaik dan perlengkapan harian berkualitas untuk hewan peliharaan Anda. Stok terjaga dan siap dikirim langsung ke rumah Anda.
                </p>

                <div class="mt-8">
                    <a href="{{ route('products.index') }}"
                       class="inline-flex items-center gap-2 rounded-full bg-white px-8 py-3.5 text-sm font-bold text-pm-orange shadow-hover transition-transform hover:scale-105 hover:shadow-xl active:scale-95">
                        Shop Now
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════
         5. CATEGORY GROUPINGS (Best Selling in Category)
    ════════════════════════════════════════ --}}
    @foreach($categories as $category)
        @php
            $catProducts = $products->where('category_id', $category->id);
        @endphp
        @if($catProducts->isNotEmpty())
            <section class="bg-white py-12 sm:py-16 dark:bg-gray-900 border-t border-pm-border/40">
                <div class="mx-auto max-w-7xl px-5 sm:px-8">
                    <div class="mb-8 flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight text-pm-charcoal dark:text-gray-50 sm:text-3xl">
                                Best Selling in {{ $category->name }}
                            </h2>
                        </div>
                        <a href="{{ route('products.index', ['category' => $category->id]) }}" class="pm-link font-semibold text-xs sm:text-sm">
                            View All →
                        </a>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach($catProducts->take(4) as $catProduct)
                            <x-product-card :product="$catProduct" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    @endforeach

    {{-- ════════════════════════════════════════
         6. SUBSCRIBE NEWSLETTER BANNER (Matching reference)
    ════════════════════════════════════════ --}}
    <section class="mx-auto max-w-7xl px-5 py-12 sm:px-8 sm:py-16">
        <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-r from-orange-600 to-pm-orange p-8 sm:p-12 text-white shadow-card">
            {{-- Paw prints background --}}
            <div class="pointer-events-none absolute inset-0 text-white/10" aria-hidden="true">
                <div class="absolute left-1/4 top-4 h-24 w-24 rotate-45">{!! $pawSvg !!}</div>
                <div class="absolute right-12 bottom-6 h-28 w-28 -rotate-12">{!! $pawSvg !!}</div>
            </div>

            <div class="relative z-10 grid items-center gap-8 lg:grid-cols-12">
                <div class="lg:col-span-7">
                    <h2 class="text-3xl font-extrabold sm:text-4xl">
                        Subscribe to Our Newsletter
                    </h2>
                    <p class="mt-3 text-sm sm:text-base text-white/90">
                        Dapatkan informasi promo spesial, tips perawatan hewan peliharaan, dan rekomendasi produk terbaru langsung ke email Anda.
                    </p>
                    <form onsubmit="event.preventDefault(); alert('Terima kasih telah berlangganan newsletter PawMart!');"
                          class="mt-6 flex max-w-md flex-col sm:flex-row gap-3">
                        <input type="email"
                               required
                               placeholder="Masukkan email Anda..."
                               class="flex-1 rounded-full border-0 bg-white px-5 py-3 text-sm text-pm-charcoal placeholder:text-gray-400 focus:ring-2 focus:ring-white">
                        <button type="submit"
                                class="rounded-full bg-pm-charcoal px-7 py-3 text-sm font-bold text-white shadow-card hover:bg-black transition-colors">
                            Subscribe
                        </button>
                    </form>
                </div>

                {{-- Peeking dog face graphic on right (matching reference) --}}
                <div class="flex justify-center lg:col-span-5 lg:justify-end" aria-hidden="true">
                    <div class="flex h-36 w-36 sm:h-44 sm:w-44 items-center justify-center rounded-full bg-white/20 p-4 backdrop-blur-sm shadow-inner">
                        <span class="text-7xl sm:text-8xl">🐶</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-store-layout>
