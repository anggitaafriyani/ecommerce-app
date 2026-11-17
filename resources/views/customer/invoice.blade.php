<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Invoice {{ $order->order_number }} · PawMart</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="bg-[#FAF7F2] font-sans text-[#25302B] dark:bg-gray-950 dark:text-gray-100 print:!bg-white print:!text-black">
    <main class="mx-auto max-w-4xl px-4 py-8 sm:px-8 print:max-w-none print:p-0">
        <nav class="mb-6 flex flex-wrap gap-3 print:hidden" aria-label="Aksi invoice"><a class="pm-secondary" href="{{ route('orders.show', $order) }}">← Detail pesanan</a><button type="button" class="pm-button" x-data @click="window.print()">Cetak / Simpan PDF</button></nav>
        <article class="pm-card p-5 sm:p-10 print:rounded-none print:border-0 print:!bg-white print:!text-black print:p-0 print:shadow-none">
            <header class="flex flex-wrap justify-between gap-6 border-b pb-6"><div><x-application-logo class="text-3xl print:!text-black" /><p class="mt-2 text-sm">PawMart Pet Shop</p><p class="mt-1 text-xs">Transaksi Midtrans Sandbox</p></div><div><h1 class="text-2xl font-semibold">Invoice pesanan</h1><p class="mt-2 break-all text-sm">{{ $order->order_number }}</p><p class="mt-2 text-sm">{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</p></div></header>
            <div class="my-6 grid gap-5 sm:grid-cols-2"><section><h2 class="text-sm font-semibold">Pelanggan</h2><p class="mt-2 break-words">{{ $order->user?->name }}</p><p class="mt-3 text-sm">Status pembayaran: {{ \App\Models\Order::PAYMENT_LABELS[$order->payment_status] ?? $order->payment_status }}</p></section><section><h2 class="text-sm font-semibold">Penerima</h2><p class="mt-2 break-words">{{ $order->recipient_name }} · {{ $order->recipient_phone }}</p><p class="mt-2 whitespace-pre-line break-words text-sm">{{ $order->shipping_address }}</p><p class="mt-1 text-sm">{{ $order->city }}, {{ $order->province }} {{ $order->postal_code }}</p></section></div>
            <table class="w-full table-fixed text-left text-xs sm:text-sm print:text-sm"><caption class="sr-only">Rincian transaksi historis</caption><thead class="border-y"><tr><th class="w-[40%] py-3 pr-2">Produk</th><th class="w-[23%] py-3 pr-2">Harga satuan</th><th class="w-[10%] py-3">Qty</th><th class="w-[27%] py-3 text-right">Subtotal</th></tr></thead><tbody>
                @foreach($order->items as $item)<tr class="break-inside-avoid border-b"><td class="break-words py-4 pr-2">{{ $item->product_name }}</td><td class="break-words py-4 pr-2">Rp{{ number_format($item->product_price, 2, ',', '.') }}</td><td class="py-4">{{ $item->quantity }}</td><td class="break-words py-4 text-right">Rp{{ number_format($item->subtotal, 2, ',', '.') }}</td></tr>@endforeach
            </tbody></table>
            <dl class="ml-auto mt-6 grid max-w-sm gap-3 text-sm">@foreach(['subtotal' => 'Subtotal', 'shipping_cost' => 'Biaya pengiriman', 'total_amount' => 'Total pembayaran'] as $field => $label)<div class="flex flex-wrap justify-between gap-3 {{ $field === 'total_amount' ? 'border-t pt-3 font-bold' : '' }}"><dt>{{ $label }}</dt><dd>Rp{{ number_format($order->$field, 2, ',', '.') }}</dd></div>@endforeach</dl>
            <footer class="mt-8 border-t pt-5 text-xs">Terima kasih telah berbelanja di PawMart. Invoice memakai nama dan harga produk saat transaksi. Invoice berstatus menunggu bukan bukti pembayaran berhasil.</footer>
        </article>
    </main>
</body>
</html>
