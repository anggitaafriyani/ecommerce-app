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
<body class="bg-[#FAF7F2] font-sans text-[#25302B] antialiased dark:bg-gray-950 dark:text-gray-100">
    <a href="#content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-4">Langsung ke konten</a>
    <div class="bg-[#2F5D50] px-4 py-2 text-center text-xs tracking-wide text-white">Untuk kaki kecil, kebahagiaan besar.</div>
    <header class="border-b border-[#E6E4DF] bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-5 sm:px-8">
            <a href="{{ route('home') }}" aria-label="Beranda PawMart" class="flex items-center gap-2 text-2xl font-bold tracking-tight text-[#2F5D50] dark:text-green-300"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#2F5D50] text-lg text-white" aria-hidden="true">P</span>PawMart<span class="text-[#E8B98A]">.</span></a>
            <div class="flex flex-wrap items-center gap-4 text-sm">
                @guest
                    <a class="pm-link" href="{{ route('login') }}">Masuk</a>
                    <a class="pm-secondary !px-4 !py-2" href="{{ route('register') }}">Daftar</a>
                @else
                    <a class="pm-link" href="{{ route('profile.edit') }}">Profil</a>
                    <a class="pm-link" href="{{ route(auth()->user()->dashboardRoute()) }}">{{ auth()->user()->role === 'admin' ? 'Dashboard Admin' : 'Akun saya' }}</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="pm-link">Keluar</button></form>
                @endguest
            </div>
        </div>
        <nav aria-label="Navigasi toko" class="mx-auto flex max-w-7xl flex-wrap items-center gap-x-6 gap-y-3 px-5 pb-4 text-sm sm:px-8">
            <a href="{{ route('home') }}" class="pm-link" @if(request()->routeIs('home')) aria-current="page" @endif>Beranda</a>
            <a href="{{ route('products.index') }}" class="pm-link" @if(request()->routeIs('products.*')) aria-current="page" @endif>Produk</a>
            <a href="{{ route('home') }}#categories" class="pm-link">Kategori</a>
            @if(auth()->user()?->role === 'customer')<a href="{{ route('orders.index') }}" class="pm-link" @if(request()->routeIs('orders.*')) aria-current="page" @endif>Pesanan saya</a>@endif
            @if(!auth()->check() || auth()->user()->role === 'customer')
                <a href="{{ route('cart.index') }}" class="pm-link ml-auto inline-flex items-center gap-2" aria-label="Keranjang belanja">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 3h2l3 13h11l2-9H6"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>Keranjang
                </a>
            @endif
        </nav>
    </header>
    <main id="content">@if(session('status'))<div role="status" class="mx-auto mt-5 max-w-7xl rounded-xl bg-green-100 p-4 text-green-900">{{ session('status') }}</div>@endif @if($errors->any())<div role="alert" class="mx-auto mt-5 max-w-7xl rounded-xl bg-red-50 p-4 text-red-800"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif{{ $slot }}</main>
    <footer class="mt-16 border-t border-[#E6E4DF] bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="mx-auto grid max-w-7xl gap-8 px-5 py-12 sm:grid-cols-3 sm:px-8">
            <div><a href="{{ route('home') }}" class="text-2xl font-bold text-[#2F5D50] dark:text-green-300">PawMart.</a><p class="mt-3 max-w-xs text-sm leading-6 text-gray-500 dark:text-gray-400">Kebutuhan sehari-hari untuk hewan kesayangan. Temukan makanan, perawatan, dan aksesori dalam satu tempat.</p></div>
            <div><h2 class="font-semibold">Jelajahi PawMart</h2><div class="mt-4 flex flex-col items-start gap-3"><a class="pm-link" href="{{ route('products.index') }}">Semua produk</a><a class="pm-link" href="{{ route('home') }}#categories">Kategori produk</a></div></div>
            <div><h2 class="font-semibold">Selangkah lebih dekat</h2><p class="mt-4 text-sm leading-6 text-gray-500 dark:text-gray-400">Pilih produk, simpan di keranjang, dan buat pesanan. Pembayaran melalui Midtrans Sandbox.</p></div>
        </div>
        <div class="border-t border-[#E6E4DF] px-5 py-5 text-center text-xs text-gray-500 dark:border-gray-800">© {{ date('Y') }} PawMart Pet Shop · Untuk sahabat kecil Anda.</div>
    </footer>

</body>
</html>
