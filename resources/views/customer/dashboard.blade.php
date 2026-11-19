<x-store-layout title="Akun Pelanggan">
    @php
        $pawSvg = '<svg class="w-full h-full fill-current" viewBox="0 0 64 64"><ellipse cx="17" cy="22" rx="7" ry="10" transform="rotate(-25 17 22)"/><ellipse cx="32" cy="15" rx="7" ry="10"/><ellipse cx="47" cy="22" rx="7" ry="10" transform="rotate(25 47 22)"/><path d="M32 31c-7 0-10 5-15 11-8 11-1 18 8 14 5-2 9-2 14 0 9 4 16-3 8-14-5-6-8-11-15-11z"/></svg>';
    @endphp

    <div class="mx-auto max-w-7xl px-5 py-8 sm:px-8 sm:py-12">
        {{-- ════════════════════════════════════════
             SECTION 1: HERO PERSONAL (Pet&Co Style)
        ════════════════════════════════════════ --}}
        <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-white via-pm-cream/30 to-pm-orange-lt/40 border border-pm-border/80 shadow-xs dark:bg-gray-900 dark:border-gray-800">
            {{-- Background decorative paw motifs --}}
            <div class="pointer-events-none absolute -right-12 -top-12 h-64 w-64 rounded-full bg-pm-orange/10 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute right-12 top-6 h-28 w-28 text-pm-orange/10" aria-hidden="true">{!! $pawSvg !!}</div>

            <div class="grid items-center gap-8 p-6 sm:p-10 lg:grid-cols-12 lg:gap-12">
                {{-- Left Content --}}
                <div class="lg:col-span-7">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="rounded-full bg-pm-orange-lt px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-pm-orange dark:bg-orange-950/60 dark:text-orange-300">
                            Akun Pelanggan
                        </span>
                        <span class="text-xs text-pm-muted dark:text-gray-400">
                            {{ auth()->user()->email }}
                        </span>
                    </div>

                    <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-pm-charcoal dark:text-gray-50 sm:text-4xl lg:text-5xl leading-tight">
                        Halo, {{ auth()->user()->name }}! 🐾
                    </h1>

                    <p class="mt-3 max-w-xl text-base text-pm-muted dark:text-gray-300 leading-relaxed">
                        Penuhi kebutuhan nutrisi, kebersihan, dan perlengkapan sahabat bulu kesayangan Anda dengan mudah, cepat, dan aman di PawMart.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3.5">
                        <a href="{{ route('products.index') }}" class="pm-button !rounded-full !px-7 !py-3 text-sm font-bold shadow-xs">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                            </svg>
                            Mulai Belanja
                        </a>
                        <a href="{{ route('profile.edit') }}" class="pm-secondary !rounded-full !px-6 !py-3 text-sm font-semibold">
                            Kelola Profil
                        </a>
                    </div>
                </div>

                {{-- Right Pet Composition Illustration --}}
                <div class="relative flex items-center justify-center lg:col-span-5">
                    <div class="relative w-full max-w-sm">
                        {{-- Organic curved backdrop --}}
                        <div class="absolute -inset-2 rounded-[2.5rem] bg-gradient-to-tr from-pm-orange to-pm-orange-dk opacity-90 -rotate-3 scale-95 shadow-card" aria-hidden="true"></div>
                        <div class="relative overflow-hidden rounded-[2.5rem] border-4 border-white bg-white/40 p-2 shadow-card backdrop-blur-xs">
                            <img src="{{ asset('images/hero-pets.png') }}"
                                 alt="Sahabat Hewan PawMart"
                                 class="aspect-[4/3] w-full rounded-[2rem] object-cover object-top">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════
             SECTION 2: PESANAN SAYA (Shopee Style Navigation)
        ════════════════════════════════════════ --}}
        <section class="mt-8 rounded-[2rem] border border-pm-border/80 bg-white p-6 shadow-xs sm:p-8 dark:border-gray-800 dark:bg-gray-900" aria-label="Pesanan Saya">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-pm-border pb-5 dark:border-gray-800">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-pm-charcoal dark:text-gray-100 sm:text-2xl">
                        Pesanan Saya
                    </h2>
                    <p class="mt-0.5 text-xs text-pm-muted dark:text-gray-400">Pantau proses pengiriman paket belanja Anda secara langsung</p>
                </div>
                <a href="{{ route('orders.index') }}" class="pm-link inline-flex items-center gap-1.5 text-xs font-bold">
                    Lihat Semua Riwayat ({{ $stats['total'] }}) →
                </a>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                {{-- 1. Belum Bayar --}}
                <a href="{{ route('orders.index', ['status' => 'unpaid']) }}"
                   class="group relative flex flex-col items-center justify-center rounded-2xl border border-pm-border/70 bg-pm-cream/30 p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-pm-orange hover:bg-white hover:shadow-card dark:border-gray-800 dark:bg-gray-850 dark:hover:bg-gray-800">
                    @if($stats['unpaid'] > 0)
                        <span class="absolute -top-2 -right-2 flex h-6 min-w-6 items-center justify-center rounded-full bg-red-600 px-1.5 text-[11px] font-bold text-white shadow-xs">
                            {{ $stats['unpaid'] }}
                        </span>
                    @endif
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-800 transition-colors group-hover:bg-pm-orange group-hover:text-white dark:bg-amber-950/60 dark:text-amber-200">
                        {{-- Wallet/Credit Card Icon --}}
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="2" y="5" width="20" height="14" rx="2"/>
                            <line x1="2" y1="10" x2="22" y2="10"/>
                        </svg>
                    </span>
                    <span class="mt-3 text-sm font-bold text-pm-charcoal group-hover:text-pm-orange dark:text-gray-100">
                        Belum Bayar
                    </span>
                    <span class="mt-0.5 text-[11px] text-pm-muted dark:text-gray-400">
                        {{ $stats['unpaid'] > 0 ? $stats['unpaid'].' transaksi' : 'Tidak ada tagihan' }}
                    </span>
                </a>

                {{-- 2. Diproses --}}
                <a href="{{ route('orders.index', ['status' => 'processing']) }}"
                   class="group relative flex flex-col items-center justify-center rounded-2xl border border-pm-border/70 bg-pm-cream/30 p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-pm-orange hover:bg-white hover:shadow-card dark:border-gray-800 dark:bg-gray-850 dark:hover:bg-gray-800">
                    @if($stats['processing'] > 0)
                        <span class="absolute -top-2 -right-2 flex h-6 min-w-6 items-center justify-center rounded-full bg-blue-600 px-1.5 text-[11px] font-bold text-white shadow-xs">
                            {{ $stats['processing'] }}
                        </span>
                    @endif
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-blue-800 transition-colors group-hover:bg-pm-orange group-hover:text-white dark:bg-blue-950/60 dark:text-blue-200">
                        {{-- Package Icon --}}
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M16.5 9.4 7.55 4.24a1.78 1.78 0 0 0-1.78 0L2 6.5l10 5.8 10-5.8-3.72-2.16a1.78 1.78 0 0 0-1.78 0L16.5 9.4z"/>
                            <path d="m2 6.5 10 5.8v10L2 16.5v-10z"/>
                            <path d="M22 6.5 12 12.3v10l10-5.8v-10z"/>
                        </svg>
                    </span>
                    <span class="mt-3 text-sm font-bold text-pm-charcoal group-hover:text-pm-orange dark:text-gray-100">
                        Diproses
                    </span>
                    <span class="mt-0.5 text-[11px] text-pm-muted dark:text-gray-400">
                        {{ $stats['processing'] > 0 ? $stats['processing'].' disiapkan' : 'Sedang kosong' }}
                    </span>
                </a>

                {{-- 3. Dikirim --}}
                <a href="{{ route('orders.index', ['status' => 'shipped']) }}"
                   class="group relative flex flex-col items-center justify-center rounded-2xl border border-pm-border/70 bg-pm-cream/30 p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-pm-orange hover:bg-white hover:shadow-card dark:border-gray-800 dark:bg-gray-850 dark:hover:bg-gray-800">
                    @if($stats['shipped'] > 0)
                        <span class="absolute -top-2 -right-2 flex h-6 min-w-6 items-center justify-center rounded-full bg-pm-orange px-1.5 text-[11px] font-bold text-white shadow-xs">
                            {{ $stats['shipped'] }}
                        </span>
                    @endif
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-100 text-pm-orange transition-colors group-hover:bg-pm-orange group-hover:text-white dark:bg-orange-950/60 dark:text-orange-200">
                        {{-- Truck Icon --}}
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="1" y="3" width="15" height="13"/>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                            <circle cx="5.5" cy="18.5" r="2.5"/>
                            <circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                    </span>
                    <span class="mt-3 text-sm font-bold text-pm-charcoal group-hover:text-pm-orange dark:text-gray-100">
                        Dikirim
                    </span>
                    <span class="mt-0.5 text-[11px] text-pm-muted dark:text-gray-400">
                        {{ $stats['shipped'] > 0 ? $stats['shipped'].' dalam perjalanan' : 'Tidak ada pengiriman' }}
                    </span>
                </a>

                {{-- 4. Selesai --}}
                <a href="{{ route('orders.index', ['status' => 'completed']) }}"
                   class="group relative flex flex-col items-center justify-center rounded-2xl border border-pm-border/70 bg-pm-cream/30 p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-pm-orange hover:bg-white hover:shadow-card dark:border-gray-800 dark:bg-gray-850 dark:hover:bg-gray-800">
                    @if($stats['completed'] > 0)
                        <span class="absolute -top-2 -right-2 flex h-6 min-w-6 items-center justify-center rounded-full bg-green-600 px-1.5 text-[11px] font-bold text-white shadow-xs">
                            {{ $stats['completed'] }}
                        </span>
                    @endif
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-green-100 text-green-800 transition-colors group-hover:bg-pm-orange group-hover:text-white dark:bg-green-950/60 dark:text-green-200">
                        {{-- Check Circle Icon --}}
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>
                    </span>
                    <span class="mt-3 text-sm font-bold text-pm-charcoal group-hover:text-pm-orange dark:text-gray-100">
                        Selesai
                    </span>
                    <span class="mt-0.5 text-[11px] text-pm-muted dark:text-gray-400">
                        {{ $stats['completed'] > 0 ? $stats['completed'].' pesanan' : 'Belum ada transaksi' }}
                    </span>
                </a>
            </div>
        </section>

        {{-- ════════════════════════════════════════
             SECTION 3: FEATURED COLLECTIONS (Circular Categories)
        ════════════════════════════════════════ --}}
        <section class="mt-10 rounded-[2rem] bg-pm-cream/50 p-6 sm:p-8 border border-pm-border/60" aria-label="Kategori Pilihan">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-pm-charcoal dark:text-gray-100 sm:text-2xl">
                        Kategori Pilihan
                    </h2>
                    <p class="text-xs text-pm-muted dark:text-gray-400">Pilih kelompok kebutuhan hewan peliharaan Anda</p>
                </div>
                <a href="{{ route('products.index') }}" class="pm-link text-xs font-bold">
                    Lihat Semua →
                </a>
            </div>

            <div class="flex flex-wrap items-center justify-around gap-6">
                @forelse($categories as $category)
                    <a href="{{ route('products.index', ['category' => $category->slug ?? $category->id]) }}" class="pm-collection-circle group">
                        <div class="pm-collection-circle-img !h-24 !w-24 sm:!h-28 sm:!w-28">
                            <x-category-image :category="$category" :illustration="true" />
                        </div>
                        <span class="text-xs font-bold text-pm-charcoal group-hover:text-pm-orange transition-colors dark:text-gray-200">
                            {{ $category->name }}
                        </span>
                    </a>
                @empty
                    <p class="text-sm text-pm-muted">Kategori sedang disiapkan.</p>
                @endforelse
            </div>
        </section>

        {{-- ════════════════════════════════════════
             SECTION 4: VISUAL PROMOTIONAL BANNER
        ════════════════════════════════════════ --}}
        <section class="mt-10 overflow-hidden rounded-[2.5rem] bg-gradient-to-r from-pm-orange to-pm-orange-dk p-8 sm:p-10 text-white shadow-card relative" aria-label="Banner Promosi">
            <div class="pointer-events-none absolute -right-10 -bottom-10 h-64 w-64 text-white/10" aria-hidden="true">{!! $pawSvg !!}</div>
            <div class="relative z-10 flex flex-col justify-between gap-6 md:flex-row md:items-center">
                <div class="max-w-xl">
                    <span class="inline-block rounded-full bg-white/20 px-3.5 py-1 text-xs font-bold uppercase tracking-wider backdrop-blur-xs">
                        Spesial Sahabat Kecil
                    </span>
                    <h2 class="mt-3 text-2xl font-extrabold sm:text-3xl lg:text-4xl leading-tight">
                        Temukan Kebutuhan Terbaik untuk Hewan Kesayanganmu
                    </h2>
                    <p class="mt-2 text-sm text-white/90 leading-relaxed">
                        Nutrisi harian, perawatan bulu, hingga mainan interaktif yang aman dan berkualitas. Belanja nyaman dari rumah, pesanan dikirim langsung ke alamat Anda.
                    </p>
                </div>
                <div class="shrink-0">
                    <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-white px-8 py-3.5 text-sm font-bold text-pm-orange shadow-md transition-all duration-200 hover:bg-pm-cream hover:scale-105 active:scale-95">
                        Jelajahi Produk
                    </a>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════
             SECTION 5: REKOMENDASI PRODUK DARI DATABASE
        ════════════════════════════════════════ --}}
        @if($recommendedProducts->isNotEmpty())
            <section class="mt-12" aria-label="Rekomendasi Produk">
                <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-pm-orange">Rekomendasi Pilihan</span>
                        <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-pm-charcoal dark:text-gray-50">
                            Produk Favorit Sahabat Bulu
                        </h2>
                    </div>
                    <a href="{{ route('products.index') }}" class="pm-link text-xs font-bold">
                        Lihat Semua Produk →
                    </a>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($recommendedProducts as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ════════════════════════════════════════
             SECTION 6: PESANAN TERBARU
        ════════════════════════════════════════ --}}
        <section class="mt-14" aria-label="Pesanan Terbaru">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-pm-orange">Aktivitas Terkini</span>
                    <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-pm-charcoal dark:text-gray-50">
                        Pesanan Terbaru Anda
                    </h2>
                </div>
                @if($recentOrders->isNotEmpty())
                    <a href="{{ route('orders.index') }}" class="pm-link text-xs font-bold">
                        Lihat Semua ({{ $stats['total'] }}) →
                    </a>
                @endif
            </div>

            @if($recentOrders->isNotEmpty())
                <div class="space-y-4">
                    @foreach($recentOrders as $order)
                        <article class="pm-card p-5 sm:p-6 transition-all duration-200 hover:shadow-card hover:border-pm-orange/30 rounded-[2rem] border-pm-border/80">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-pm-border pb-4 dark:border-gray-800">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2.5">
                                        <span class="font-mono text-xs font-bold text-pm-charcoal dark:text-gray-100">
                                            {{ $order->order_number }}
                                        </span>
                                        <span class="text-xs text-pm-muted dark:text-gray-400">·</span>
                                        <span class="text-xs text-pm-muted dark:text-gray-400">
                                            {{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB
                                        </span>
                                    </div>
                                    @if($order->courier_name)
                                        <p class="mt-1 text-xs text-pm-muted dark:text-gray-400">
                                            Kurir: <span class="font-semibold text-pm-charcoal dark:text-gray-200">{{ $order->courier_name }}</span> ({{ $order->courier_service ?? 'Reguler' }})
                                            @if($order->tracking_number)
                                                · Resi: <span class="font-mono font-bold text-pm-orange">{{ $order->tracking_number }}</span>
                                            @endif
                                        </p>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2">
                                    <x-order-status :status="$order->payment_status" type="payment" />
                                    <x-order-status :status="$order->order_status" />
                                </div>
                            </div>

                            <div class="my-4 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                                <ul class="space-y-1 text-sm text-pm-charcoal dark:text-gray-200">
                                    @foreach($order->items->take(2) as $item)
                                        <li class="flex items-center gap-2">
                                            <span class="h-1.5 w-1.5 rounded-full bg-pm-orange" aria-hidden="true"></span>
                                            <span class="truncate">{{ $item->quantity }} × {{ $item->product_name }}</span>
                                        </li>
                                    @endforeach
                                    @if($order->items->count() > 2)
                                        <li class="text-xs text-pm-muted dark:text-gray-400 pl-3.5">
                                            +{{ $order->items->count() - 2 }} produk lainnya
                                        </li>
                                    @endif
                                </ul>

                                <div class="text-left sm:text-right">
                                    <span class="text-xs text-pm-muted dark:text-gray-400">Total Pembayaran</span>
                                    <p class="text-lg font-bold text-pm-charcoal dark:text-gray-100">
                                        Rp{{ number_format($order->total_amount, 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 border-t border-pm-border pt-4 dark:border-gray-800">
                                <a class="pm-button !rounded-full !py-1.5 !px-4 text-xs font-semibold" href="{{ route('orders.show', $order) }}">
                                    Lihat Detail & Lacak
                                </a>
                                <a class="pm-secondary !rounded-full !py-1.5 !px-4 text-xs font-semibold" href="{{ route('orders.invoice', $order) }}">
                                    Invoice
                                </a>
                                @if($order->canContinuePayment())
                                    <a class="pm-button !bg-amber-600 hover:!bg-amber-700 !rounded-full !py-1.5 !px-4 text-xs font-bold shadow-xs" href="{{ route('payments.show', $order) }}">
                                        Bayar Sekarang
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                {{-- Empty State --}}
                <div class="pm-card flex flex-col items-center p-8 text-center sm:p-14 rounded-[2.5rem]">
                    <span class="flex h-16 w-16 items-center justify-center rounded-full bg-pm-orange-lt text-pm-orange">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                    </span>
                    <h3 class="mt-4 text-lg font-bold text-pm-charcoal dark:text-gray-100">
                        Belum ada riwayat pesanan
                    </h3>
                    <p class="mt-2 max-w-sm text-sm text-pm-muted dark:text-gray-400">
                        Temukan berbagai produk favorit untuk hewan kesayangan Anda dan nikmati kemudahan berbelanja di PawMart.
                    </p>
                    <a class="pm-button !rounded-full mt-6 shadow-xs" href="{{ route('products.index') }}">
                        Jelajahi Katalog Produk
                    </a>
                </div>
            @endif
        </section>
    </div>
</x-store-layout>
