<x-admin-layout title="Dashboard Admin" subtitle="Selamat datang kembali. Pantau toko dan rawat kebutuhan sahabat kecil pelanggan Anda.">
    <x-slot:actions><a class="pm-button" href="{{ route('admin.products.create') }}">+ Tambah produk</a></x-slot:actions>
    <section aria-label="Statistik toko" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach (['products' => ['Total produk', 'Produk dalam daftar toko'], 'categories' => ['Total kategori', 'Kelompok kebutuhan hewan'], 'customers' => ['Total pelanggan', 'Pelanggan yang terdaftar'], 'orders' => ['Total pesanan', 'Seluruh pesanan tersimpan'], 'pending' => ['Menunggu pembayaran', 'Pembayaran belum diselesaikan'], 'paid' => ['Pesanan telah dibayar', 'Pembayaran berhasil']] as $key => [$label, $hint])
            <article class="pm-card p-6 {{ $key === 'paid' ? 'border-l-4 border-l-[#2F5D50]' : '' }}">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
                <p class="mt-4 text-4xl font-semibold tracking-tight" data-stat="{{ $key }}">{{ number_format($stats[$key], 0, ',', '.') }}</p>
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
            </article>
        @endforeach
    </section>
    <div class="mt-8 grid gap-6 xl:grid-cols-3">
        <section class="pm-card overflow-hidden xl:col-span-2">
            <div class="flex items-center justify-between gap-4 border-b border-[#E6E4DF] p-6 dark:border-gray-700"><h2 class="text-lg font-semibold">Produk terbaru</h2><a href="{{ route('admin.products.index') }}" class="pm-link">Lihat semua →</a></div>
            @forelse($recentProducts as $product)
                <a href="{{ route('admin.products.show', $product) }}" class="flex items-center gap-4 border-b border-[#E6E4DF] p-5 last:border-0 hover:bg-[#FAF7F2] dark:border-gray-700 dark:hover:bg-gray-800">
                    @if($product->image_path)<img class="h-12 w-12 rounded-xl object-cover" src="{{ Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}">@else<span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#FAF7F2] text-[#2F5D50]" aria-hidden="true">P</span>@endif
                    <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold">{{ $product->name }}</p><p class="mt-1 text-xs text-gray-500">{{ $product->category->name }}</p></div><p class="text-sm font-semibold">Rp{{ number_format($product->price, 2, ',', '.') }}</p>
                </a>
            @empty<div class="p-8 text-sm text-gray-500">Belum ada produk. Tambahkan produk pertama toko Anda.</div>@endforelse
        </section>
        <section class="pm-card p-6"><span class="rounded-full bg-[#E8B98A]/30 px-3 py-1 text-xs font-semibold dark:text-orange-200">Perlu perhatian</span><h2 class="mt-4 text-lg font-semibold">Persediaan menipis</h2><p class="mt-2 text-sm text-gray-500">Produk aktif dengan stok 5 atau kurang.</p>
            <div class="mt-4 divide-y divide-[#E6E4DF] dark:divide-gray-700">@forelse($lowStockProducts as $product)<a href="{{ route('admin.products.edit', $product) }}" class="flex items-center justify-between gap-3 py-4"><span class="text-sm">{{ $product->name }}</span><span class="shrink-0 rounded-full bg-orange-50 px-2 py-1 text-xs font-semibold text-orange-900">{{ $product->stock === 0 ? 'Habis' : $product->stock.' tersisa' }}</span></a>@empty<p class="py-5 text-sm text-gray-500">Tidak ada produk yang perlu diisi ulang.</p>@endforelse</div>
        </section>
    </div>
</x-admin-layout>
