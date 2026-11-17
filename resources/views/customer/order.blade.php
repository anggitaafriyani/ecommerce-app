<x-store-layout title="Detail pesanan">
    <div class="mx-auto max-w-5xl px-5 py-10 sm:px-8">
        <a class="pm-link" href="{{ route('orders.index') }}">← Riwayat pesanan</a>
        <p class="mt-6 text-sm uppercase tracking-widest text-[#2F5D50] dark:text-green-300">Pesanan Anda</p><h1 class="mt-3 break-all text-2xl font-semibold">{{ $order->order_number }}</h1><p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</p>
        <section class="my-6 rounded-2xl bg-[#F2D6C9]/40 p-5"><div class="flex flex-wrap gap-3"><x-order-status :status="$order->payment_status" type="payment" /><x-order-status :status="$order->order_status" /></div><p class="mt-3 text-sm">Status pembayaran diperbarui melalui verifikasi server Midtrans.</p>@if($order->stock_reserved)<p class="mt-2 text-sm">Reservasi stok sampai {{ $order->expires_at?->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB.</p>@endif</section>
        <div class="grid gap-6"><x-order-progress :order="$order" /><x-order-summary :order="$order" /></div>
        <div class="mt-6 flex flex-wrap gap-3"><a class="pm-button" href="{{ route('orders.invoice', $order) }}">Lihat Invoice</a><a class="pm-secondary" href="{{ route('payments.show', $order) }}">{{ $order->canContinuePayment() ? 'Lanjutkan pembayaran' : 'Lihat pembayaran' }}</a><a class="pm-secondary" href="{{ route('products.index') }}">Kembali ke katalog</a>@if($order->payment_status === 'pending' && $order->stock_reserved)<form method="POST" action="{{ route('orders.cancel', $order) }}">@csrf<button class="pm-secondary">Batalkan pesanan &amp; lepaskan stok</button></form>@endif</div>
    </div>
</x-store-layout>
