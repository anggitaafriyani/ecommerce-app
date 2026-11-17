<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Akun Pelanggan</h1>
    </x-slot>
    <div class="py-12 px-4">
        <div class="max-w-7xl mx-auto rounded-lg bg-white dark:bg-gray-800 p-6 text-gray-900 dark:text-gray-100 shadow-sm">
            <p class="text-sm font-semibold text-[#2F5D50] dark:text-green-300">PAWMART · PELANGGAN</p>
            <h2 class="mt-3 text-2xl font-semibold">Halo, {{ auth()->user()->name }}!</h2>
            <p class="mt-3">Selamat datang di PawMart Pet Shop. Akun Anda siap digunakan.</p>
            <p class="mt-2 text-gray-600 dark:text-gray-300">Keranjang dan checkout siap digunakan. Pembayaran menggunakan Midtrans Sandbox.</p>
            <a href="{{ route('orders.index') }}" class="pm-secondary mt-6 mr-4">Pesanan saya</a><a href="{{ route('cart.index') }}" class="pm-secondary mt-6 mr-4">Keranjang saya</a><a href="{{ route('products.index') }}" class="pm-button mt-6 mr-4">Jelajahi katalog</a><a href="{{ route('profile.edit') }}" class="mt-6 inline-block underline rounded focus:ring-2 focus:ring-green-700">Kelola profil</a>
        </div>
    </div>
</x-app-layout>
