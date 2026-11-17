@props(['order'])
<div class="grid items-start gap-6 md:grid-cols-2">
    <section class="pm-card min-w-0 p-6">
        <h2 class="text-xl font-semibold">Produk pesanan</h2>
        @foreach($order->items as $item)
            <div class="flex flex-wrap justify-between gap-3 border-b py-4 dark:border-gray-700">
                <div class="min-w-0"><p class="break-words font-medium">{{ $item->product_name }}</p><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $item->quantity }} × Rp{{ number_format($item->product_price, 2, ',', '.') }}</p></div>
                <p>Rp{{ number_format($item->subtotal, 2, ',', '.') }}</p>
            </div>
        @endforeach
        <dl class="mt-5 grid gap-3">
            @foreach(['subtotal' => 'Subtotal', 'shipping_cost' => 'Ongkir simulasi', 'total_amount' => 'Total pembayaran'] as $field => $label)
                <div class="flex flex-wrap justify-between gap-2 {{ $field === 'total_amount' ? 'font-semibold' : '' }}"><dt>{{ $label }}</dt><dd>Rp{{ number_format($order->$field, 2, ',', '.') }}</dd></div>
            @endforeach
        </dl>
    </section>
    <section class="pm-card min-w-0 p-6">
        <h2 class="text-xl font-semibold">Alamat pengiriman</h2>
        <p class="mt-4 break-words font-medium">{{ $order->recipient_name }}</p><p class="mt-2">{{ $order->recipient_phone }}</p>
        <p class="mt-3 whitespace-pre-line break-words">{{ $order->shipping_address }}</p><p class="mt-2">{{ $order->city }}, {{ $order->province }} {{ $order->postal_code }}</p>
        @if($order->notes)<p class="mt-4 whitespace-pre-line break-words text-sm">Catatan: {{ $order->notes }}</p>@endif
        <h3 class="mt-6 border-t pt-5 font-semibold dark:border-gray-700">Pembayaran Midtrans Sandbox</h3>
        @if($order->payment)
            <dl class="mt-3 grid gap-2 text-sm"><div><dt class="text-gray-500 dark:text-gray-400">Metode</dt><dd>{{ strtoupper($order->payment->payment_type ?? 'Belum dipilih') }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Waktu pembayaran terverifikasi</dt><dd>{{ $order->payment->paid_at ? $order->payment->paid_at->timezone('Asia/Jakarta')->format('d M Y H:i').' WIB' : 'Belum terverifikasi' }}</dd></div>
            </dl>
            @if($order->payment->review_reason)<p class="mt-3 text-sm text-rose-700 dark:text-rose-300">Pembayaran memerlukan pemeriksaan pengelola. Jangan membayar ulang.</p>@endif
        @else<p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Belum ada permintaan pembayaran.</p>@endif
    </section>
</div>
