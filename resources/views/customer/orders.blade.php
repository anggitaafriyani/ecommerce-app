<x-store-layout title="Riwayat pesanan">
    <div class="mx-auto max-w-5xl px-5 py-10 sm:px-8">
        <h1 class="text-3xl font-semibold">Riwayat Pesanan</h1><p class="mt-3 text-gray-500 dark:text-gray-400">Pantau pembelian dan pengiriman untuk sahabat kecil Anda.</p>
        <div class="mt-8 grid gap-5">
            @forelse($orders as $order)
                <article class="pm-card min-w-0 p-5 sm:p-6">
                    <div class="flex flex-wrap justify-between gap-4"><div class="min-w-0"><h2 class="break-all font-semibold">{{ $order->order_number }}</h2><p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</p></div><p class="font-semibold">Rp{{ number_format($order->total_amount, 2, ',', '.') }}</p></div>
                    <ul class="my-4 grid gap-2 text-sm">@foreach($order->items as $item)<li class="break-words">{{ $item->quantity }} × {{ $item->product_name }}</li>@endforeach</ul>
                    <div class="flex flex-wrap gap-3"><x-order-status :status="$order->payment_status" type="payment" /><x-order-status :status="$order->order_status" /></div>
                    <div class="mt-5 flex flex-wrap gap-3"><a class="pm-button" href="{{ route('orders.show', $order) }}">Lihat Detail</a><a class="pm-secondary" href="{{ route('orders.invoice', $order) }}">Lihat Invoice</a><a class="pm-link self-center" href="{{ route('payments.show', $order) }}">{{ $order->canContinuePayment() ? 'Lanjutkan pembayaran' : 'Lihat pembayaran' }}</a></div>
                </article>
            @empty
                <div class="pm-card p-8 text-center"><h2 class="text-xl font-semibold">Belum ada pesanan.</h2><p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Temukan kebutuhan hewan kesayangan Anda di katalog.</p><a class="pm-button mt-5" href="{{ route('products.index') }}">Jelajahi produk</a></div>
            @endforelse
        </div>
        <div class="mt-6">{{ $orders->links() }}</div>
    </div>
</x-store-layout>
