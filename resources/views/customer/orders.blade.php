<x-store-layout title="Riwayat pesanan">
    <div class="mx-auto max-w-5xl px-5 py-8 sm:px-8 sm:py-12">
        {{-- Header --}}
        <div class="mb-8">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-pm-orange-lt px-3.5 py-1 text-xs font-bold text-pm-orange">
                Daftar Transaksi Pelanggan
            </span>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-pm-charcoal dark:text-gray-50 sm:text-4xl">
                Riwayat Pesanan
            </h1>
            <p class="mt-2 text-sm text-pm-muted dark:text-gray-400">
                Pantau pembelian dan pengiriman untuk sahabat kecil Anda.
            </p>
        </div>

        {{-- Status Filter Tabs --}}
        <div class="mb-6 flex flex-wrap gap-2 border-b border-pm-border/80 pb-4 dark:border-gray-800">
            @php
                $currentStatus = request('status', 'all');
                $tabs = [
                    'all' => 'Semua',
                    'unpaid' => 'Belum Bayar',
                    'processing' => 'Diproses',
                    'shipped' => 'Dikirim',
                    'completed' => 'Selesai',
                ];
            @endphp
            @foreach($tabs as $key => $tabLabel)
                <a href="{{ $key === 'all' ? route('orders.index') : route('orders.index', ['status' => $key]) }}"
                   class="rounded-full px-4 py-1.5 text-xs font-bold transition-all {{ $currentStatus === $key ? 'bg-pm-orange text-white shadow-xs' : 'bg-white text-pm-muted border border-pm-border hover:border-pm-orange/50 hover:text-pm-charcoal dark:bg-gray-800 dark:border-gray-700' }}">
                    {{ $tabLabel }}
                </a>
            @endforeach
        </div>

        {{-- Order list --}}
        <div class="space-y-5">
            @forelse($orders as $order)
                <article class="pm-card min-w-0 p-6 transition-all duration-250 hover:shadow-card hover:border-pm-orange/30 sm:p-7 rounded-[2rem] border-pm-border/80">
                    {{-- Order Header --}}
                    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-pm-border/80 pb-4 dark:border-gray-800">
                        <div class="min-w-0">
                            <span class="text-xs font-bold uppercase tracking-wider text-pm-muted dark:text-gray-400">No. Pesanan</span>
                            <h2 class="break-all text-base font-bold text-pm-charcoal dark:text-gray-100">{{ $order->order_number }}</h2>
                            <p class="mt-0.5 text-xs text-pm-muted dark:text-gray-400">{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</p>
                            @if($order->courier_name)
                                <p class="mt-1 text-xs text-pm-muted dark:text-gray-400">
                                    Kurir: <span class="font-semibold text-pm-charcoal dark:text-gray-200">{{ $order->courier_name }} ({{ $order->courier_service ?? 'Reguler' }})</span>
                                    @if($order->tracking_number)
                                        · Resi: <span class="font-mono font-bold text-pm-orange">{{ $order->tracking_number }}</span>
                                    @endif
                                </p>
                            @endif
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-pm-muted dark:text-gray-400">Total Transaksi</span>
                            <p class="text-lg font-extrabold text-pm-orange">Rp{{ number_format($order->total_amount, 2, ',', '.') }}</p>
                        </div>
                    </div>

                    {{-- Items list --}}
                    <div class="my-4">
                        <ul class="grid gap-1.5 text-sm">
                            @foreach($order->items as $item)
                                <li class="flex items-center gap-2 text-pm-charcoal dark:text-gray-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-pm-orange" aria-hidden="true"></span>
                                    <span class="break-words font-medium">{{ $item->quantity }} × {{ $item->product_name }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Status Badges --}}
                    <div class="flex flex-wrap items-center gap-2.5 py-2">
                        <x-order-status :status="$order->payment_status" type="payment" />
                        <x-order-status :status="$order->order_status" />
                    </div>

                    {{-- Actions --}}
                    <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-pm-border/80 pt-4 dark:border-gray-800">
                        <a class="pm-button !rounded-full !py-2 !px-5 text-xs font-bold" href="{{ route('orders.show', $order) }}">
                            Lihat Detail
                        </a>
                        <a class="pm-secondary !rounded-full !py-2 !px-5 text-xs font-semibold" href="{{ route('orders.invoice', $order) }}">
                            Lihat Invoice
                        </a>
                        @if($order->canContinuePayment())
                            <a class="pm-button !bg-amber-600 hover:!bg-amber-700 !rounded-full !py-2 !px-5 text-xs font-bold text-white shadow-xs" href="{{ route('payments.show', $order) }}">
                                Bayar Sekarang →
                            </a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="pm-card flex flex-col items-center px-6 py-16 text-center sm:py-20">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-pm-cream text-pm-muted dark:bg-gray-800 dark:text-gray-400">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <h2 class="mt-5 text-xl font-bold text-pm-charcoal dark:text-gray-100">Belum ada pesanan.</h2>
                    <p class="mt-2 max-w-sm text-sm text-pm-muted dark:text-gray-400">Temukan kebutuhan hewan kesayangan Anda di katalog.</p>
                    <a class="pm-button mt-6" href="{{ route('products.index') }}">Jelajahi produk</a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
            <div class="mt-8">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</x-store-layout>
