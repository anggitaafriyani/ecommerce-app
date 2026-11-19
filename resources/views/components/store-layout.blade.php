@props(['title' => 'Kebutuhan sahabat kecil Anda'])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Jelajahi makanan, perawatan, dan aksesori hewan peliharaan di PawMart Pet Shop.">
    <title>{{ $title }} · PawMart Pet Shop</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-pm-cream font-sans text-pm-charcoal antialiased dark:bg-gray-950 dark:text-gray-100"
      x-data="{
          mobileNav: false,
          toastVisible: false,
          toastMessage: '',
          toastType: 'success',
          showToast(message, type = 'success') {
              this.toastMessage = message;
              this.toastType = type;
              this.toastVisible = true;
              setTimeout(() => { this.toastVisible = false; }, 4000);
          }
      }"
      @cart-added.window="showToast($event.detail.message || 'Produk ditambahkan ke keranjang!')">

    {{-- Skip Link --}}
    <a href="#content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:left-4 focus:top-4 focus:rounded-xl focus:bg-pm-green focus:px-5 focus:py-3 focus:text-sm focus:font-semibold focus:text-white focus:shadow-hover">
        Langsung ke konten
    </a>

    {{-- ════════════════════════════════════════
         TOAST NOTIFICATION
    ════════════════════════════════════════ --}}
    <div
        x-show="toastVisible"
        x-transition:enter="transform transition ease-out duration-300"
        x-transition:enter-start="translate-y-4 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transform transition ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-4 opacity-0"
        class="fixed bottom-6 right-6 z-50 max-w-sm"
        role="status"
        aria-live="polite"
    >
        <div :class="toastType === 'success' ? 'bg-pm-green text-white' : 'bg-red-600 text-white'"
             class="flex items-center gap-3 rounded-2xl px-5 py-4 shadow-hover">
            <template x-if="toastType === 'success'">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                </svg>
            </template>
            <span class="text-sm font-semibold" x-text="toastMessage"></span>
            <button @click="toastVisible = false" class="ml-2 shrink-0 opacity-70 hover:opacity-100" aria-label="Tutup notifikasi">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         HEADER
    ════════════════════════════════════════ --}}
    @php
        $logoRoute = !auth()->check()
            ? route('home')
            : (auth()->user()->role === 'admin' ? route('admin.dashboard') : route('dashboard'));
        $logoAriaLabel = !auth()->check()
            ? 'Beranda PawMart'
            : (auth()->user()->role === 'admin' ? 'Dashboard Admin PawMart' : 'Dashboard PawMart');
    @endphp
    <header class="sticky top-0 z-40 border-b border-pm-border bg-white/95 backdrop-blur-sm dark:border-gray-800 dark:bg-gray-900/95">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            {{-- Main Nav Row --}}
            <div class="flex h-16 items-center justify-between gap-4">
                {{-- Logo --}}
                <a href="{{ $logoRoute }}"
                   aria-label="{{ $logoAriaLabel }}"
                   class="flex shrink-0 items-center gap-2.5 text-xl font-bold tracking-tight text-pm-charcoal dark:text-gray-100">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-pm-orange text-white shadow-xs" aria-hidden="true">
                        <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24">
                            <path d="M12 11c-1.657 0-3 1.343-3 3 0 2.21 2.239 4 3 4s3-1.79 3-4c0-1.657-1.343-3-3-3zm-4.5-2C6.672 9 6 9.672 6 10.5S6.672 12 7.5 12 9 11.328 9 10.5 8.328 9 7.5 9zm9 0c-.828 0-1.5.672-1.5 1.5s.672 1.5 1.5 1.5 1.5-.672 1.5-1.5-.672-1.5-1.5-1.5zm-6.5-3C9.172 4 8.5 4.672 8.5 5.5S9.172 7 10 7s1.5-.672 1.5-1.5S10.828 4 10 4zm4 0c-.828 0-1.5.672-1.5 1.5s.672 1.5 1.5 1.5 1.5-.672 1.5-1.5S14.828 4 14 4z"/>
                        </svg>
                    </span>
                    <span>PawMart<span class="text-pm-orange">.</span></span>
                </a>

                {{-- Desktop Navigation --}}
                <nav aria-label="Navigasi toko" class="hidden items-center gap-6 md:flex">
                    @guest
                        <a href="{{ route('home') }}"
                           class="pm-nav-link"
                           @if(request()->routeIs('home')) aria-current="page" @endif>Beranda</a>
                    @elseif(auth()->user()->role === 'customer')
                        <a href="{{ route('dashboard') }}"
                           class="pm-nav-link"
                           @if(request()->routeIs('dashboard')) aria-current="page" @endif>Dashboard</a>
                    @else
                        <a href="{{ route('admin.dashboard') }}"
                           class="pm-nav-link"
                           @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>Dashboard Admin</a>
                    @endguest

                    <a href="{{ route('products.index') }}"
                       class="pm-nav-link"
                       @if(request()->routeIs('products.*')) aria-current="page" @endif>Produk</a>

                    <a href="{{ route('home') }}#categories"
                       class="pm-nav-link">Kategori</a>

                    @if(auth()->user()?->role === 'customer')
                        <a href="{{ route('orders.index') }}"
                           class="pm-nav-link"
                           @if(request()->routeIs('orders.*')) aria-current="page" @endif>Pesanan saya</a>
                    @endif
                </nav>

                {{-- Right Actions (Search pill + Cart + Auth) --}}
                <div class="flex items-center gap-2 sm:gap-3">
                    {{-- Search Pill (Matching reference) --}}
                    <form action="{{ route('products.index') }}" method="GET" class="hidden lg:flex items-center">
                        <div class="relative">
                            <input type="text"
                                   name="q"
                                   value="{{ request('q') }}"
                                   placeholder="Search..."
                                   class="h-9 w-44 xl:w-56 rounded-full border border-pm-border bg-pm-cream/60 pl-9 pr-4 text-xs font-medium text-pm-charcoal transition-all placeholder:text-pm-muted/60 focus:w-64 focus:border-pm-orange focus:bg-white focus:ring-2 focus:ring-pm-orange/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            <svg class="absolute left-3 top-2.5 h-4 w-4 text-pm-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </form>

                    {{-- Cart (customers & guests only) --}}
                    @if(!auth()->check() || auth()->user()->role === 'customer')
                        <a href="{{ route('cart.index') }}"
                           class="relative flex h-10 w-10 items-center justify-center rounded-full border border-pm-border bg-white text-pm-charcoal transition-all duration-150 hover:border-pm-orange/40 hover:bg-pm-cream dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                           aria-label="Keranjang belanja">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path d="M3 3h2l3 13h11l2-9H6"/>
                                <circle cx="9" cy="20" r="1"/>
                                <circle cx="18" cy="20" r="1"/>
                            </svg>
                            @auth
                                @php($cartCount = auth()->user()->cart?->items->sum('quantity') ?? 0)
                                @if($cartCount > 0)
                                    <span class="absolute -right-1 -top-1 flex h-4.5 min-w-[18px] items-center justify-center rounded-full bg-pm-orange px-1 text-[10px] font-bold text-white shadow-xs">
                                        {{ $cartCount > 99 ? '99+' : $cartCount }}
                                    </span>
                                @endif
                            @endauth
                        </a>
                    @endif

                    {{-- Auth Actions (Desktop / Avatar Dropdown for Customer) --}}
                    @guest
                        <div class="hidden items-center gap-3 sm:flex">
                            <a class="pm-link" href="{{ route('login') }}">Masuk</a>
                            <a class="pm-button !rounded-full !px-5 !py-2 text-xs font-semibold" href="{{ route('register') }}">Daftar</a>
                        </div>
                    @elseif(auth()->user()->role === 'admin')
                        <div class="hidden items-center gap-3 sm:flex">
                            <a class="pm-link font-semibold text-pm-orange" href="{{ route('admin.dashboard') }}">Dashboard Admin</a>
                            <a class="pm-link" href="{{ route('profile.edit') }}">Profil</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="pm-link text-pm-muted hover:text-red-600">Keluar</button>
                            </form>
                        </div>
                    @else
                        {{-- Customer Avatar Account Dropdown --}}
                        <x-account-dropdown :user="auth()->user()" />
                    @endguest

                    {{-- Mobile Menu Toggle --}}
                    <button
                        @click="mobileNav = !mobileNav"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-pm-border bg-white text-pm-charcoal transition-all duration-150 hover:bg-pm-cream md:hidden dark:border-gray-700 dark:bg-gray-800"
                        :aria-expanded="mobileNav.toString()"
                        aria-controls="mobile-nav"
                        aria-label="Menu navigasi">
                        <svg x-show="!mobileNav" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                        <svg x-show="mobileNav" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Mobile Navigation Drawer --}}
            <div
                id="mobile-nav"
                x-show="mobileNav"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="border-t border-pm-border pb-4 pt-3 md:hidden dark:border-gray-800">
                <nav aria-label="Navigasi mobile" class="flex flex-col gap-1">
                    @guest
                        <a href="{{ route('home') }}"
                           @click="mobileNav = false"
                           class="rounded-xl px-4 py-3 text-sm font-semibold transition-colors hover:bg-pm-cream dark:hover:bg-gray-800 {{ request()->routeIs('home') ? 'text-pm-green' : 'text-pm-charcoal dark:text-gray-100' }}">
                            Beranda
                        </a>
                    @elseif(auth()->user()->role === 'customer')
                        <a href="{{ route('dashboard') }}"
                           @click="mobileNav = false"
                           class="rounded-xl px-4 py-3 text-sm font-semibold transition-colors hover:bg-pm-cream dark:hover:bg-gray-800 {{ request()->routeIs('dashboard') ? 'text-pm-green font-bold' : 'text-pm-charcoal dark:text-gray-100' }}">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('admin.dashboard') }}"
                           @click="mobileNav = false"
                           class="rounded-xl px-4 py-3 text-sm font-semibold transition-colors hover:bg-pm-cream dark:hover:bg-gray-800 {{ request()->routeIs('admin.dashboard') ? 'text-pm-green font-bold' : 'text-pm-charcoal dark:text-gray-100' }}">
                            Dashboard Admin
                        </a>
                    @endguest

                    <a href="{{ route('products.index') }}"
                       @click="mobileNav = false"
                       class="rounded-xl px-4 py-3 text-sm font-semibold transition-colors hover:bg-pm-cream dark:hover:bg-gray-800 {{ request()->routeIs('products.*') ? 'text-pm-green font-bold' : 'text-pm-charcoal dark:text-gray-100' }}">
                        Produk
                    </a>
                    <a href="{{ route('home') }}#categories"
                       @click="mobileNav = false"
                       class="rounded-xl px-4 py-3 text-sm font-semibold text-pm-charcoal transition-colors hover:bg-pm-cream dark:text-gray-100 dark:hover:bg-gray-800">
                        Kategori
                    </a>

                    @if(auth()->user()?->role === 'customer')
                        <a href="{{ route('orders.index') }}"
                           @click="mobileNav = false"
                           class="rounded-xl px-4 py-3 text-sm font-semibold transition-colors hover:bg-pm-cream dark:hover:bg-gray-800 {{ request()->routeIs('orders.*') ? 'text-pm-green font-bold' : 'text-pm-charcoal dark:text-gray-100' }}">
                            Pesanan saya
                        </a>
                        <a href="{{ route('cart.index') }}"
                           @click="mobileNav = false"
                           class="rounded-xl px-4 py-3 text-sm font-semibold transition-colors hover:bg-pm-cream dark:hover:bg-gray-800 {{ request()->routeIs('cart.*') ? 'text-pm-green font-bold' : 'text-pm-charcoal dark:text-gray-100' }}">
                            Keranjang
                        </a>
                    @endif

                    <div class="mt-2 border-t border-pm-border pt-3 dark:border-gray-800">
                        @guest
                            <div class="flex gap-3 px-4">
                                <a class="pm-secondary flex-1 justify-center !py-2 text-center" href="{{ route('login') }}">Masuk</a>
                                <a class="pm-button flex-1 justify-center !py-2 text-center" href="{{ route('register') }}">Daftar</a>
                            </div>
                        @elseif(auth()->user()->role === 'admin')
                            <div class="flex flex-col gap-1 px-1">
                                <a @click="mobileNav = false" class="rounded-xl px-4 py-3 text-sm font-semibold text-pm-orange hover:bg-pm-cream" href="{{ route('admin.dashboard') }}">Dashboard Admin</a>
                                <a @click="mobileNav = false" class="rounded-xl px-4 py-3 text-sm font-semibold text-pm-charcoal hover:bg-pm-cream dark:text-gray-100" href="{{ route('profile.edit') }}">Profil</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full rounded-xl px-4 py-3 text-left text-sm font-semibold text-pm-muted hover:bg-pm-cream hover:text-red-600 dark:text-gray-300 dark:hover:bg-gray-800">
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="flex flex-col gap-1 px-1">
                                <div class="flex items-center gap-3 px-3 py-2.5 mb-1 rounded-2xl bg-pm-cream/70 border border-[#F3D6C3]/60 dark:bg-gray-850 dark:border-gray-800">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-pm-orange text-xs font-bold text-white shadow-xs">
                                        {{ strtoupper(mb_substr(auth()->user()->name ?? 'P', 0, 1)) }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-xs font-bold text-pm-charcoal dark:text-gray-100">{{ auth()->user()->name }}</p>
                                        <p class="truncate text-[11px] text-pm-muted dark:text-gray-400">{{ auth()->user()->email }}</p>
                                    </div>
                                </div>
                                <a @click="mobileNav = false" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold text-pm-charcoal hover:bg-pm-cream dark:text-gray-100 {{ request()->routeIs('profile.*') ? 'text-pm-orange font-bold' : '' }}" href="{{ route('profile.edit') }}">
                                    <svg class="h-4 w-4 text-pm-muted" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                    </svg>
                                    Profil Saya
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-3 w-full rounded-xl px-4 py-2.5 text-left text-sm font-semibold text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40">
                                        <svg class="h-4 w-4 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25z" clip-rule="evenodd" />
                                            <path fill-rule="evenodd" d="M19 10a.75.75 0 00-.75-.75H8.704l2.47-2.47a.75.75 0 10-1.06-1.06l-3.75 3.75a.75.75 0 000 1.06l3.75 3.75a.75.75 0 101.06-1.06l-2.47-2.47H18.25A.75.75 0 0019 10z" clip-rule="evenodd" />
                                        </svg>
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        @endguest
                    </div>
                </nav>
            </div>
        </div>
    </header>

    {{-- ════════════════════════════════════════
         MAIN CONTENT
    ════════════════════════════════════════ --}}
    <main id="content">
        {{-- Flash Messages --}}
        {{-- Global Flash / Success Notification --}}
        @if(session('status'))
            <div class="mx-auto mt-6 max-w-7xl px-5 sm:px-8" x-data="{ show: true }" x-show="show" x-transition>
                <div class="flex items-center justify-between rounded-2xl border border-pm-orange/30 bg-pm-cream/90 p-4 text-sm text-pm-charcoal shadow-xs dark:bg-gray-850 dark:border-gray-800 dark:text-gray-100" role="status">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-pm-orange text-white font-bold text-sm shadow-xs">
                            ✓
                        </span>
                        <p class="font-medium text-pm-charcoal dark:text-gray-100">{{ session('status') }}</p>
                    </div>
                    <button type="button" @click="show = false" class="text-pm-muted hover:text-pm-charcoal dark:hover:text-white ml-3 text-sm font-bold" aria-label="Tutup notifikasi">✕</button>
                </div>
            </div>
        @endif

        {{-- Global Error Notification --}}
        @if($errors->any())
            <div class="mx-auto mt-6 max-w-7xl px-5 sm:px-8" x-data="{ show: true }" x-show="show" x-transition>
                <div class="flex items-start justify-between rounded-2xl border border-red-200 bg-red-50/90 p-4 text-sm text-red-800 shadow-xs dark:bg-red-950/40 dark:border-red-900 dark:text-red-300" role="alert">
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600 font-bold text-base dark:bg-red-900/40 dark:text-red-300">
                            ⚠️
                        </span>
                        <div>
                            <p class="font-bold mb-1">Perhatian</p>
                            <ul class="list-disc list-inside space-y-0.5 text-xs">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button type="button" @click="show = false" class="text-red-400 hover:text-red-700 dark:hover:text-red-200 ml-3 text-sm font-bold" aria-label="Tutup notifikasi">✕</button>
                </div>
            </div>
        @endif

        {{ $slot }}
    </main>

    {{-- ════════════════════════════════════════
         FOOTER
    ════════════════════════════════════════ --}}
    <footer class="mt-20 border-t border-pm-border bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                {{-- Brand --}}
                <div class="sm:col-span-2 lg:col-span-1">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 text-xl font-bold tracking-tight text-pm-charcoal dark:text-gray-100">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-pm-orange text-white" aria-hidden="true">
                            <svg class="h-4.5 w-4.5 fill-current" viewBox="0 0 24 24">
                                <path d="M12 11c-1.657 0-3 1.343-3 3 0 2.21 2.239 4 3 4s3-1.79 3-4c0-1.657-1.343-3-3-3zm-4.5-2C6.672 9 6 9.672 6 10.5S6.672 12 7.5 12 9 11.328 9 10.5 8.328 9 7.5 9zm9 0c-.828 0-1.5.672-1.5 1.5s.672 1.5 1.5 1.5 1.5-.672 1.5-1.5-.672-1.5-1.5-1.5zm-6.5-3C9.172 4 8.5 4.672 8.5 5.5S9.172 7 10 7s1.5-.672 1.5-1.5S10.828 4 10 4zm4 0c-.828 0-1.5.672-1.5 1.5s.672 1.5 1.5 1.5 1.5-.672 1.5-1.5S14.828 4 14 4z"/>
                            </svg>
                        </span>
                        <span>PawMart<span class="text-pm-orange">.</span></span>
                    </a>
                    <p class="mt-4 max-w-xs text-sm leading-7 text-pm-muted dark:text-gray-400">
                        Kebutuhan sehari-hari untuk hewan kesayangan. Makanan, perawatan, dan aksesori dalam satu tempat.
                    </p>
                </div>

                {{-- Explore --}}
                <div>
                    <h2 class="text-sm font-semibold text-pm-charcoal dark:text-gray-100">Jelajahi</h2>
                    <div class="mt-4 flex flex-col gap-3">
                        <a class="pm-link" href="{{ route('products.index') }}">Semua produk</a>
                        <a class="pm-link" href="{{ route('home') }}#categories">Kategori produk</a>
                        @if(auth()->user()?->role === 'customer')
                            <a class="pm-link" href="{{ route('orders.index') }}">Riwayat pesanan</a>
                        @endif
                    </div>
                </div>

                {{-- Akun --}}
                <div>
                    <h2 class="text-sm font-semibold text-pm-charcoal dark:text-gray-100">Akun</h2>
                    <div class="mt-4 flex flex-col gap-3">
                        @guest
                            <a class="pm-link" href="{{ route('login') }}">Masuk</a>
                            <a class="pm-link" href="{{ route('register') }}">Daftar</a>
                        @else
                            <a class="pm-link" href="{{ route('profile.edit') }}">Profil saya</a>
                            <a class="pm-link" href="{{ route('cart.index') }}">Keranjang</a>
                        @endguest
                    </div>
                </div>

                {{-- Info --}}
                <div>
                    <h2 class="text-sm font-semibold text-pm-charcoal dark:text-gray-100">Informasi</h2>
                    <p class="mt-4 text-sm leading-7 text-pm-muted dark:text-gray-400">
                        Pilih produk, simpan di keranjang, dan buat pesanan. Pembayaran melalui Midtrans Sandbox.
                    </p>
                    <div class="mt-5 inline-flex items-center gap-2 rounded-xl bg-pm-green-lt px-4 py-2.5">
                        <svg class="h-4 w-4 text-pm-green" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-xs font-semibold text-pm-green">Transaksi aman & terenkripsi</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-pm-border px-5 py-5 dark:border-gray-800">
            <p class="text-center text-xs text-pm-muted dark:text-gray-500">
                © {{ date('Y') }} PawMart Pet Shop · Untuk sahabat kecil Anda.
            </p>
        </div>
    </footer>

</body>
</html>
