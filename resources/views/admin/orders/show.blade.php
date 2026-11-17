<x-admin-layout title="Detail pesanan" subtitle="Tinjau transaksi dan lanjutkan pengiriman setelah pembayaran terverifikasi.">
    <x-slot:actions><a class="pm-secondary" href="{{ route('admin.orders.index') }}">← Daftar pesanan</a></x-slot:actions>
    <section class="mb-6 flex flex-wrap items-start justify-between gap-5 rounded-2xl bg-[#F2D6C9]/40 p-6">
        <div class="min-w-0"><h2 class="break-all text-xl font-semibold">{{ $order->order_number }}</h2><p class="mt-2">{{ $order->user?->name }}</p><p class="mt-2 text-sm">{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</p></div>
        <div class="flex flex-wrap gap-3"><x-order-status :status="$order->payment_status" type="payment" /><x-order-status :status="$order->order_status" /></div>
    </section>
    <div class="grid gap-6"><x-order-progress :order="$order" /><x-order-summary :order="$order" />
        <section class="pm-card p-6"><h2 class="text-xl font-semibold">Perbarui pemrosesan</h2>
            @if($next = $order->nextFulfillmentStatus())
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">{{ $next === 'shipped' ? 'Tandai dikirim setelah barang diserahkan untuk pengiriman.' : 'Tandai selesai setelah pesanan diterima pelanggan.' }}</p>
                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="mt-5">@csrf @method('PATCH')<input type="hidden" name="order_status" value="{{ $next }}"><button class="pm-button">Tandai {{ \App\Models\Order::STATUS_LABELS[$next] }}</button></form>
            @else
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">{{ $order->order_status === 'completed' ? 'Pesanan telah selesai. Tidak ada perubahan lanjutan.' : 'Pemrosesan belum dapat dilanjutkan. Pesanan harus memiliki pembayaran terverifikasi, tanpa masalah pembayaran atau pelepasan stok.' }}</p>
            @endif
            <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">Status pembayaran ditentukan oleh verifikasi Midtrans dan tidak dapat diubah di halaman ini.</p>
        </section>
    </div>
</x-admin-layout>
