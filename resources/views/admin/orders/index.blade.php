<x-admin-layout title="Pesanan pelanggan" subtitle="Pantau pembayaran dan kelola pengiriman pesanan PawMart.">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="pm-card mb-6 grid items-end gap-4 p-5 sm:grid-cols-2 xl:grid-cols-4">
        <div><label for="q" class="pm-label">Cari pesanan</label><input id="q" name="q" class="pm-input" placeholder="Nomor pesanan atau pelanggan" maxlength="100" value="{{ $filters['q'] ?? '' }}"></div>
        <div><label for="payment_status" class="pm-label">Pembayaran</label><select id="payment_status" name="payment_status" class="pm-input"><option value="">Semua pembayaran</option>@foreach(\App\Models\Order::PAYMENT_LABELS as $value => $label)<option value="{{ $value }}" @selected(($filters['payment_status'] ?? '') === $value)>{{ $label }}{{ $value === 'success' ? ' (legacy)' : '' }}</option>@endforeach</select></div>
        <div><label for="order_status" class="pm-label">Pemrosesan</label><select id="order_status" name="order_status" class="pm-input"><option value="">Semua proses</option>@foreach(\App\Models\Order::STATUS_LABELS as $value => $label)<option value="{{ $value }}" @selected(($filters['order_status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="flex flex-wrap items-center gap-3"><button class="pm-button">Terapkan</button><a class="pm-link" href="{{ route('admin.orders.index') }}">Reset filter</a></div>
    </form>
    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">{{ $orders->total() }} pesanan ditemukan. Geser tabel untuk melihat semua kolom pada layar kecil.</p>
    <div class="pm-card overflow-x-auto">
        <table class="pm-table"><caption class="sr-only">Daftar pesanan pelanggan</caption><thead><tr><th>Pesanan / tanggal</th><th>Pelanggan</th><th>Jumlah barang</th><th>Total</th><th>Pembayaran</th><th>Proses</th><th>Aksi</th></tr></thead>
            <tbody>@forelse($orders as $order)<tr>
                <td><p class="font-semibold">{{ $order->order_number }}</p><p class="mt-2 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</p></td>
                <td>{{ $order->user?->name ?? 'Pelanggan tidak tersedia' }}</td><td>{{ $order->items_sum_quantity ?? 0 }}</td><td class="whitespace-nowrap font-semibold">Rp{{ number_format($order->total_amount, 2, ',', '.') }}</td>
                <td><x-order-status :status="$order->payment_status" type="payment" /></td><td><x-order-status :status="$order->order_status" /></td>
                <td><a class="pm-link whitespace-nowrap" href="{{ route('admin.orders.show', $order) }}">Lihat detail<span class="sr-only"> {{ $order->order_number }}</span></a></td>
            </tr>@empty<tr><td colspan="7" class="text-center">Belum ada pesanan yang sesuai. Coba ubah pencarian atau filter.</td></tr>@endforelse</tbody>
        </table>
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
</x-admin-layout>
