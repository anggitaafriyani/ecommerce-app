@props(['title', 'subtitle' => ''])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · PawMart Admin</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="overflow-x-hidden bg-[#FAF7F2] font-sans text-[#25302B] antialiased dark:bg-gray-950 dark:text-gray-100">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-4">Langsung ke konten</a>
    <aside class="border-b border-[#E6E4DF] bg-white p-5 lg:fixed lg:inset-y-0 lg:w-64 lg:border-r lg:p-7 dark:border-gray-700 dark:bg-gray-900">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 text-2xl font-bold tracking-tight text-[#2F5D50] dark:text-green-300">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#2F5D50] text-white" aria-hidden="true">P</span>PawMart<span class="sr-only"> Dashboard Admin</span>
        </a>
        <p class="mt-3 hidden text-xs uppercase tracking-[0.18em] text-gray-500 lg:block">Pet shop · Ruang Admin</p>
        <nav aria-label="Navigasi Admin" class="mt-6 flex flex-wrap gap-2 lg:mt-12 lg:flex-col">
            @foreach (['admin.dashboard' => 'Dashboard', 'admin.categories.index' => 'Kategori', 'admin.products.index' => 'Produk', 'admin.orders.index' => 'Pesanan'] as $route => $label)
                @php($active = request()->routeIs(str_replace('.index', '.*', $route)))
                <a href="{{ route($route) }}" @if($active) aria-current="page" @endif class="rounded-xl px-4 py-3 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-[#2F5D50] {{ $active ? 'bg-[#2F5D50] text-white' : 'text-gray-600 hover:bg-[#FAF7F2] dark:text-gray-300 dark:hover:bg-gray-800' }}">{{ $label }}</a>
            @endforeach
        </nav>
        <div class="mt-6 border-t border-[#E6E4DF] pt-5 lg:absolute lg:bottom-7 lg:left-7 lg:right-7 dark:border-gray-700">
            <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
            <div class="mt-3 flex flex-wrap items-center gap-4 text-sm">
                <a class="pm-link" href="{{ route('profile.edit') }}">Profil</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="pm-link" type="submit">Keluar</button></form>
            </div>
        </div>
    </aside>
    <div class="lg:pl-64">
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-[#E6E4DF] px-5 py-5 sm:px-9 dark:border-gray-800">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">PawMart / Administrasi</p>
            <span class="rounded-full border border-[#E6E4DF] bg-white px-3 py-1 text-xs text-[#2F5D50] dark:border-gray-700 dark:bg-gray-900 dark:text-green-300">Administrator</span>
        </header>
        <main id="main" class="mx-auto max-w-7xl px-5 py-8 sm:px-9 sm:py-10">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                <div><h1 class="text-3xl font-semibold tracking-tight">{{ $title }}</h1><p class="mt-2 max-w-2xl text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $subtitle }}</p></div>
                {{ $actions ?? '' }}
            </div>
            @if(session('status'))<div role="status" class="mb-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-900">{{ session('status') }}</div>@endif
            @if($errors->any())<div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-900"><p class="font-semibold">Periksa kembali data Anda.</p><ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            {{ $slot }}
            <footer class="mt-12 border-t border-[#E6E4DF] pt-5 text-xs text-gray-500 dark:border-gray-800">PawMart Pet Shop · Dirawat dengan sepenuh hati.</footer>
        </main>
    </div>
</body>
</html>
