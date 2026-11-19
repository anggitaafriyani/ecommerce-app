<x-admin-layout title="Pesanan pelanggan" subtitle="Pantau pembayaran dan kelola pengiriman pesanan PawMart.">
    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('admin.orders.index') }}" class="pm-card mb-6 p-5">
        <div class="grid items-end gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="q" class="pm-label">Cari pesanan</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center" aria-hidden="true">
                        <svg class="h-4 w-4 text-pm-muted" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <input id="q"
                           name="q"
                           class="pm-input !pl-10"
                           placeholder="Nomor pesanan atau pelanggan"
                           maxlength="100"
                           value="{{ $filters['q'] ?? '' }}">
                </div>
            </div>

            <div>
                <label for="payment_status" class="pm-label">Pembayaran</label>
                <select id="payment_status" name="payment_status" class="pm-input">
                    <option value="">Semua pembayaran</option>
                    @foreach(\App\Models\Order::PAYMENT_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['payment_status'] ?? '') === $value)>
                            {{ $label }}{{ $value === 'success' ? ' (legacy)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="order_status" class="pm-label">Pemrosesan</label>
                <select id="order_status" name="order_status" class="pm-input">
                    <option value="">Semua proses</option>
                    @foreach(\App\Models\Order::STATUS_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['order_status'] ?? '') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-3">
                <button class="pm-button" type="submit">Terapkan</button>
                <a class="pm-secondary" href="{{ route('admin.orders.index') }}">Reset filter</a>
            </div>
        </div>
    </form>

    <p class="mb-4 text-sm text-pm-muted dark:text-gray-400">
        <span class="font-bold text-pm-charcoal dark:text-gray-100">{{ $orders->total() }}</span> pesanan ditemukan. Geser tabel untuk melihat semua kolom pada layar kecil.
    </p>

    {{-- Orders Table Card --}}
    <div class="pm-card overflow-hidden">
        <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel dapat digulir secara horizontal">
            <table class="pm-table">
                <caption class="sr-only">Daftar pesanan pelanggan</caption>
                <thead>
                    <tr>
                        <th>Pesanan / tanggal</th>
                        <th>Pelanggan</th>
                        <th class="text-center">Jumlah barang</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Proses</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-pm-border dark:divide-gray-800">
                    @forelse($orders as $order)
                        <tr class="transition-colors hover:bg-pm-cream/50 dark:hover:bg-gray-800/50">
                            {{-- Order Number & Date --}}
                            <td class="whitespace-nowrap">
                                <p class="font-bold font-mono text-xs text-pm-charcoal dark:text-gray-100">{{ $order->order_number }}</p>
                                <p class="mt-1 text-xs text-pm-muted dark:text-gray-400">{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</p>
                            </td>

                            {{-- Customer --}}
                            <td class="whitespace-nowrap text-sm font-medium text-pm-charcoal dark:text-gray-200">
                                {{ $order->user?->name ?? 'Pelanggan tidak tersedia' }}
                            </td>

                            {{-- Quantity --}}
                            <td class="text-center whitespace-nowrap text-sm text-pm-muted dark:text-gray-400">
                                {{ $order->items_sum_quantity ?? 0 }} unit
                            </td>

                            {{-- Total --}}
                            <td class="whitespace-nowrap font-bold text-pm-green dark:text-green-400">
                                Rp{{ number_format($order->total_amount, 2, ',', '.') }}
                            </td>

                            {{-- Payment Status --}}
                            <td class="whitespace-nowrap">
                                <x-order-status :status="$order->payment_status" type="payment" />
                            </td>

                            {{-- Order Status --}}
                            <td class="whitespace-nowrap">
                                <x-order-status :status="$order->order_status" />
                            </td>

                            {{-- Actions --}}
                            <td class="text-right whitespace-nowrap">
                                <a class="pm-button !py-1.5 !px-3 text-xs" href="{{ route('admin.orders.show', $order) }}">
                                    Lihat detail<span class="sr-only"> {{ $order->order_number }}</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-sm text-pm-muted dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-pm-cream text-pm-muted dark:bg-gray-800">📋</span>
                                    <p class="mt-3 font-semibold text-pm-charcoal dark:text-gray-200">Belum ada pesanan yang sesuai. Coba ubah pencarian atau filter.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($orders->hasPages())
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
</x-admin-layout>
