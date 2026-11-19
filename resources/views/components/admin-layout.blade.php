@props(['title', 'subtitle' => ''])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · PawMart Admin</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="overflow-x-hidden bg-pm-cream font-sans text-pm-charcoal antialiased dark:bg-gray-950 dark:text-gray-100"
      x-data="{ mobileNav: false }">

    {{-- Skip Link --}}
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:left-4 focus:top-4 focus:rounded-xl focus:bg-pm-green focus:px-5 focus:py-3 focus:text-sm focus:font-semibold focus:text-white focus:shadow-hover">
        Langsung ke konten
    </a>

    {{-- ════════════════════════════════════════
         SIDEBAR (Desktop)
    ════════════════════════════════════════ --}}
    <aside class="hidden lg:fixed lg:inset-y-0 lg:z-30 lg:flex lg:w-64 lg:flex-col lg:border-r lg:border-pm-border lg:bg-white dark:border-gray-800 dark:bg-gray-900">
        {{-- Brand / Header --}}
        <div class="flex h-20 items-center justify-between border-b border-pm-border px-6 dark:border-gray-800">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 text-xl font-bold tracking-tight text-pm-charcoal dark:text-gray-100">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-pm-orange text-white shadow-xs" aria-hidden="true">
                    <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24">
                        <path d="M12 11c-1.657 0-3 1.343-3 3 0 2.21 2.239 4 3 4s3-1.79 3-4c0-1.657-1.343-3-3-3zm-4.5-2C6.672 9 6 9.672 6 10.5S6.672 12 7.5 12 9 11.328 9 10.5 8.328 9 7.5 9zm9 0c-.828 0-1.5.672-1.5 1.5s.672 1.5 1.5 1.5 1.5-.672 1.5-1.5-.672-1.5-1.5-1.5zm-6.5-3C9.172 4 8.5 4.672 8.5 5.5S9.172 7 10 7s1.5-.672 1.5-1.5S10.828 4 10 4zm4 0c-.828 0-1.5.672-1.5 1.5s.672 1.5 1.5 1.5 1.5-.672 1.5-1.5S14.828 4 14 4z"/>
                    </svg>
                </span>
                <span>PawMart<span class="text-pm-orange">.</span></span>
                <span class="sr-only">Dashboard Admin</span>
            </a>
            <span class="rounded-full bg-pm-orange-lt px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-pm-orange dark:bg-orange-950/60 dark:text-orange-300">
                Admin
            </span>
        </div>

        {{-- Nav Links --}}
        <div class="flex-1 overflow-y-auto px-4 py-6">
            <p class="px-3 text-[11px] font-bold uppercase tracking-widest text-pm-muted/80 dark:text-gray-400">
                Menu Utama
            </p>
            <nav aria-label="Navigasi Admin" class="mt-3 flex flex-col gap-1.5">
                @php
                    $navItems = [
                        [
                            'route' => 'admin.dashboard',
                            'label' => 'Dashboard',
                            'icon'  => '<path d="M3 4a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 10a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zm8-10a1 1 0 011-1h4a1 1 0 011 1v16a1 1 0 01-1 1h-4a1 1 0 01-1-1V4z"/>'
                        ],
                        [
                            'route' => 'admin.categories.index',
                            'label' => 'Kategori',
                            'icon'  => '<path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zm-2 4a2 2 0 012-2h12a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2v-6z"/>'
                        ],
                        [
                            'route' => 'admin.products.index',
                            'label' => 'Produk',
                            'icon'  => '<path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>'
                        ],
                        [
                            'route' => 'admin.orders.index',
                            'label' => 'Pesanan',
                            'icon'  => '<path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h6a1 1 0 100-2H7zm0 4a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>'
                        ],
                        [
                            'route' => 'admin.shipping.index',
                            'label' => 'Pengiriman',
                            'icon'  => '<path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/><path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-4.382a1 1 0 00-.293-.707l-2.618-2.618A1 1 0 0014.382 7H14z"/>'
                        ],
                    ];
                @endphp

                @foreach ($navItems as $item)
                    @php($active = request()->routeIs(str_replace('.index', '.*', $item['route'])))
                    <a href="{{ route($item['route']) }}"
                       @if($active) aria-current="page" @endif
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition-all duration-200 {{ $active ? 'bg-pm-orange text-white shadow-card font-bold' : 'text-pm-charcoal/80 hover:bg-pm-cream hover:text-pm-charcoal dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0 {{ $active ? 'text-white' : 'text-pm-muted dark:text-gray-400' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            {!! $item['icon'] !!}
                        </svg>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            {{-- Storefront Link --}}
            <div class="mt-8 pt-6 border-t border-pm-border dark:border-gray-800">
                <a href="{{ route('home') }}" target="_blank" class="flex items-center justify-between rounded-xl border border-pm-border bg-pm-cream/50 px-3.5 py-2.5 text-xs font-semibold text-pm-charcoal transition-colors hover:border-pm-orange/40 hover:bg-white dark:border-gray-800 dark:bg-gray-800 dark:text-gray-200">
                    <span class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-pm-orange" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        Lihat Toko
                    </span>
                    <span class="text-pm-muted">↗</span>
                </a>
            </div>
        </div>

    </aside>

    {{-- ════════════════════════════════════════
         MOBILE TOPBAR
    ════════════════════════════════════════ --}}
    <div class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-pm-border bg-white/95 px-5 backdrop-blur-sm lg:hidden dark:border-gray-800 dark:bg-gray-900/95">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-lg font-bold text-pm-charcoal dark:text-gray-100">
            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-pm-orange text-white">
                <svg class="h-[18px] w-[18px] shrink-0 fill-current" viewBox="0 0 24 24">
                    <path d="M12 11c-1.657 0-3 1.343-3 3 0 2.21 2.239 4 3 4s3-1.79 3-4c0-1.657-1.343-3-3-3zm-4.5-2C6.672 9 6 9.672 6 10.5S6.672 12 7.5 12 9 11.328 9 10.5 8.328 9 7.5 9zm9 0c-.828 0-1.5.672-1.5 1.5s.672 1.5 1.5 1.5 1.5-.672 1.5-1.5-.672-1.5-1.5-1.5zm-6.5-3C9.172 4 8.5 4.672 8.5 5.5S9.172 7 10 7s1.5-.672 1.5-1.5S10.828 4 10 4zm4 0c-.828 0-1.5.672-1.5 1.5s.672 1.5 1.5 1.5 1.5-.672 1.5-1.5S14.828 4 14 4z"/>
                </svg>
            </span>
            <span>PawMart<span class="text-pm-orange">.</span></span>
        </a>
        <button @click="mobileNav = !mobileNav"
                type="button"
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-pm-border bg-white text-pm-charcoal transition-all hover:bg-pm-cream dark:border-gray-700 dark:bg-gray-800"
                aria-label="Toggle menu navigasi admin">
            <svg x-show="!mobileNav" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
            <svg x-show="mobileNav" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    {{-- Mobile Drawer Overlay & Menu --}}
    <div x-show="mobileNav"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-black/40 backdrop-blur-xs lg:hidden"
         @click="mobileNav = false">
    </div>

    <div x-show="mobileNav"
         x-transition:enter="transition ease-in-out duration-200 transform"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in-out duration-150 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-white p-6 shadow-hover lg:hidden dark:bg-gray-900">
        <div class="flex items-center justify-between pb-6 border-b border-pm-border dark:border-gray-800">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-xl font-bold text-pm-charcoal dark:text-gray-100">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-pm-orange text-white">
                    <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24">
                        <path d="M12 11c-1.657 0-3 1.343-3 3 0 2.21 2.239 4 3 4s3-1.79 3-4c0-1.657-1.343-3-3-3zm-4.5-2C6.672 9 6 9.672 6 10.5S6.672 12 7.5 12 9 11.328 9 10.5 8.328 9 7.5 9zm9 0c-.828 0-1.5.672-1.5 1.5s.672 1.5 1.5 1.5 1.5-.672 1.5-1.5-.672-1.5-1.5-1.5zm-6.5-3C9.172 4 8.5 4.672 8.5 5.5S9.172 7 10 7s1.5-.672 1.5-1.5S10.828 4 10 4zm4 0c-.828 0-1.5.672-1.5 1.5s.672 1.5 1.5 1.5 1.5-.672 1.5-1.5S14.828 4 14 4z"/>
                    </svg>
                </span>
                <span>PawMart<span class="text-pm-orange">.</span></span>
            </a>
            <button @click="mobileNav = false" class="text-pm-muted hover:text-pm-charcoal" aria-label="Tutup menu">✕</button>
        </div>

        <nav aria-label="Navigasi Admin Mobile" class="mt-6 flex flex-col gap-1.5 flex-1">
            @foreach ($navItems as $item)
                @php($active = request()->routeIs(str_replace('.index', '.*', $item['route'])))
                <a href="{{ route($item['route']) }}"
                   @click="mobileNav = false"
                   @if($active) aria-current="page" @endif
                   class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold {{ $active ? 'bg-pm-orange text-white shadow-xs font-bold' : 'text-pm-charcoal/80 hover:bg-pm-cream dark:text-gray-300 dark:hover:bg-gray-800' }}">
                    <svg class="h-5 w-5 shrink-0 {{ $active ? 'text-white' : 'text-pm-muted' }}" viewBox="0 0 20 20" fill="currentColor">
                        {!! $item['icon'] !!}
                    </svg>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
            <a href="{{ route('home') }}" target="_blank" class="mt-4 flex items-center justify-between rounded-xl border border-pm-border bg-pm-cream/50 px-4 py-3 text-xs font-semibold text-pm-charcoal">
                <span>Lihat Toko Publik</span>
                <span>↗</span>
            </a>
        </nav>

    </div>

    {{-- ════════════════════════════════════════
         MAIN CONTENT AREA
    ════════════════════════════════════════ --}}
    <div class="lg:pl-64">
        {{-- Top App Bar --}}
        <header class="relative z-30 flex h-16 items-center justify-between border-b border-pm-border bg-white px-5 sm:px-9 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-pm-muted dark:text-gray-400">
                <span class="text-pm-orange font-bold">PawMart</span>
                <span>/</span>
                <span>Administrasi</span>
            </div>
            <x-account-dropdown :user="auth()->user()" />
        </header>

        {{-- Main Page Body --}}
        <main id="main" class="mx-auto max-w-7xl px-5 py-8 sm:px-9 sm:py-10">
            {{-- Page Title & Actions Slot --}}
            <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-pm-charcoal dark:text-gray-50 sm:text-4xl">{{ $title }}</h1>
                    @if($subtitle)
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-pm-muted dark:text-gray-400">{{ $subtitle }}</p>
                    @endif
                </div>
                <div class="w-full sm:w-auto">
                    {{ $actions ?? '' }}
                </div>
            </div>

            {{-- Flash Status Alert --}}
            @if(session('status'))
                <div role="status" class="pm-alert-success mb-6">
                    <svg class="h-5 w-5 shrink-0 text-green-600 dark:text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-semibold">{{ session('status') }}</span>
                </div>
            @endif

            {{-- Validation Error Alert --}}
            @if($errors->any())
                <div role="alert" class="pm-alert-error mb-6">
                    <svg class="h-5 w-5 shrink-0 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-semibold">Periksa kembali data Anda.</p>
                        <ul class="mt-1.5 list-inside list-disc text-xs space-y-1 opacity-90">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Main Page Content Slot --}}
            {{ $slot }}

            {{-- Footer --}}
            <footer class="mt-14 border-t border-pm-border pt-6 text-xs text-pm-muted dark:border-gray-800">
                PawMart Pet Shop · Dirawat dengan sepenuh hati.
            </footer>
        </main>
    </div>
</body>
</html>
