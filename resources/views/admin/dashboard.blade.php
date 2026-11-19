<x-admin-layout title="Dashboard Admin" subtitle="Selamat datang kembali. Pantau toko dan rawat kebutuhan sahabat kecil pelanggan Anda.">
    <x-slot:actions>
        <a class="pm-button shadow-xs" href="{{ route('admin.products.create') }}">
            <svg class="h-4 w-4 shrink-0" width="16" height="16" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
            </svg>
            Tambah produk
        </a>
    </x-slot:actions>

    {{-- ════════════════════════════════════════
         6 CORE STATS CARDS
    ════════════════════════════════════════ --}}
    @php
        $statsConfig = [
            'products' => [
                'label' => 'Total produk',
                'hint'  => 'Produk dalam daftar toko',
                'icon'  => '<path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>',
                'color' => 'bg-pm-green-lt text-pm-green dark:bg-green-900/50 dark:text-green-300',
            ],
            'categories' => [
                'label' => 'Total kategori',
                'hint'  => 'Kelompok kebutuhan hewan',
                'icon'  => '<path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zm-2 4a2 2 0 012-2h12a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2v-6z"/>',
                'color' => 'bg-pm-peach/25 text-amber-900 dark:bg-amber-900/40 dark:text-amber-200',
            ],
            'customers' => [
                'label' => 'Total pelanggan',
                'hint'  => 'Pelanggan yang terdaftar',
                'icon'  => '<path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>',
                'color' => 'bg-blue-50 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
            ],
            'orders' => [
                'label' => 'Total pesanan',
                'hint'  => 'Seluruh pesanan tersimpan',
                'icon'  => '<path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h6a1 1 0 100-2H7zm0 4a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>',
                'color' => 'bg-purple-50 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
            ],
            'pending' => [
                'label' => 'Menunggu pembayaran',
                'hint'  => 'Pembayaran belum diselesaikan',
                'icon'  => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd"/>',
                'color' => 'bg-amber-50 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
            ],
            'paid' => [
                'label' => 'Pesanan telah dibayar',
                'hint'  => 'Pembayaran berhasil',
                'icon'  => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>',
                'color' => 'bg-pm-green text-white',
            ],
        ];
    @endphp

    <section aria-label="Statistik toko" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($statsConfig as $key => $conf)
            <article class="pm-card flex flex-col justify-between p-6 transition-all duration-200 hover:shadow-card {{ $key === 'paid' ? 'border-pm-green/40 bg-gradient-to-br from-white to-pm-green-lt/40 dark:from-gray-900 dark:to-gray-850' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wider text-pm-muted dark:text-gray-400">
                            {{ $conf['label'] }}
                        </p>
                        <p class="mt-3 text-3xl font-extrabold tracking-tight text-pm-charcoal dark:text-gray-50 sm:text-4xl" data-stat="{{ $key }}">
                            {{ number_format($stats[$key], 0, ',', '.') }}
                        </p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $conf['color'] }} shadow-xs" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            {!! $conf['icon'] !!}
                        </svg>
                    </span>
                </div>
                <div class="mt-4 border-t border-pm-border pt-3 dark:border-gray-800">
                    <p class="text-xs text-pm-muted dark:text-gray-400">
                        {{ $conf['hint'] }}
                    </p>
                </div>
            </article>
        @endforeach
    </section>

    {{-- ════════════════════════════════════════
         RECENT PRODUCTS & LOW STOCK ALERTS
    ════════════════════════════════════════ --}}
    <div class="mt-8 grid gap-6 xl:grid-cols-3">
        {{-- Recent Products (2 cols) --}}
        <section class="pm-card overflow-hidden xl:col-span-2">
            <div class="flex items-center justify-between gap-4 border-b border-pm-border p-6 dark:border-gray-800">
                <div>
                    <h2 class="text-lg font-bold text-pm-charcoal dark:text-gray-100">Produk terbaru</h2>
                    <p class="text-xs text-pm-muted dark:text-gray-400">Daftar produk yang baru saja diperbarui.</p>
                </div>
                <a href="{{ route('admin.products.index') }}" class="pm-link text-xs font-semibold">
                    Lihat semua →
                </a>
            </div>

            <div class="divide-y divide-pm-border dark:divide-gray-800">
                @forelse($recentProducts as $product)
                    <a href="{{ route('admin.products.show', $product) }}"
                       class="flex items-center gap-4 p-4 transition-colors hover:bg-pm-cream/70 dark:hover:bg-gray-800 sm:px-6">
                        @if($product->image_path)
                            <img class="h-12 w-12 shrink-0 rounded-xl object-cover border border-pm-border dark:border-gray-700"
                                 src="{{ Storage::disk('public')->url($product->image_path) }}"
                                 alt="{{ $product->name }}">
                        @else
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-pm-cream text-pm-green font-bold text-sm dark:bg-gray-800 dark:text-green-300" aria-hidden="true">
                                P
                            </span>
                        @endif

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-pm-charcoal dark:text-gray-100">{{ $product->name }}</p>
                            <span class="inline-block rounded-md bg-pm-green-lt px-2 py-0.5 text-[10px] font-semibold text-pm-green dark:bg-green-900/40 dark:text-green-300">
                                {{ $product->category->name }}
                            </span>
                        </div>

                        <p class="text-sm font-bold text-pm-charcoal dark:text-gray-100">
                            Rp{{ number_format($product->price, 2, ',', '.') }}
                        </p>
                    </a>
                @empty
                    <div class="p-8 text-center text-sm text-pm-muted dark:text-gray-400">
                        Belum ada produk. Tambahkan produk pertama toko Anda.
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Low Stock Alerts (1 col) --}}
        <section class="pm-card p-6">
            <div class="flex items-center justify-between gap-2 border-b border-pm-border pb-4 dark:border-gray-800">
                <div>
                    <h2 class="text-lg font-bold text-pm-charcoal dark:text-gray-100">Persediaan menipis</h2>
                    <p class="mt-1 text-xs text-pm-muted dark:text-gray-400">Produk aktif dengan stok 5 atau kurang.</p>
                </div>
                <span class="rounded-full bg-pm-peach/30 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-amber-900 dark:bg-amber-900/40 dark:text-amber-200">
                    Perlu perhatian
                </span>
            </div>

            <div class="mt-4 divide-y divide-pm-border dark:divide-gray-800">
                @forelse($lowStockProducts as $product)
                    <a href="{{ route('admin.products.edit', $product) }}"
                       class="flex items-center justify-between gap-3 py-3.5 transition-colors hover:text-pm-green">
                        <span class="text-sm font-medium truncate text-pm-charcoal dark:text-gray-200">
                            {{ $product->name }}
                        </span>
                        <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-bold {{ $product->stock === 0 ? 'bg-red-50 text-red-800 dark:bg-red-900/40 dark:text-red-300' : 'bg-amber-50 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' }}">
                            {{ $product->stock === 0 ? 'Habis' : $product->stock.' tersisa' }}
                        </span>
                    </a>
                @empty
                    <p class="py-6 text-center text-xs text-pm-muted dark:text-gray-400">
                        Tidak ada produk yang perlu diisi ulang.
                    </p>
                @endforelse
            </div>
        </section>
    </div>
</x-admin-layout>
