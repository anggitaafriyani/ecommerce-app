<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $order->order_number }} · PawMart</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-pm-cream font-sans text-pm-charcoal antialiased dark:bg-gray-950 dark:text-gray-100 print:!bg-white print:!text-black">
    <main class="mx-auto max-w-4xl px-4 py-8 sm:px-8 sm:py-12 print:max-w-none print:p-0">
        {{-- Navigation Actions (hidden when printed) --}}
        <nav class="mb-6 flex flex-wrap items-center justify-between gap-3 print:hidden" aria-label="Aksi invoice">
            <a class="pm-secondary !rounded-full inline-flex items-center gap-1.5 text-xs font-semibold" href="{{ route('orders.show', $order) }}">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 011 10z" clip-rule="evenodd"/>
                </svg>
                Kembali ke Detail Pesanan
            </a>
            <button type="button" class="pm-button !rounded-full inline-flex items-center gap-2 text-xs font-bold shadow-card" x-data @click="window.print()">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd"/>
                </svg>
                Cetak / Simpan PDF
            </button>
        </nav>

        {{-- Invoice Sheet --}}
        <article class="pm-card p-6 sm:p-10 rounded-[2.5rem] print:rounded-none print:border-0 print:!bg-white print:!text-black print:p-0 print:shadow-none">
            {{-- Header --}}
            <header class="flex flex-wrap items-start justify-between gap-6 border-b border-pm-border pb-6 dark:border-gray-800 print:border-gray-300">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-pm-orange text-white shadow-xs print:border print:border-black print:!bg-white print:!text-black">
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24">
                                <path d="M12 11c-1.657 0-3 1.343-3 3 0 2.21 2.239 4 3 4s3-1.79 3-4c0-1.657-1.343-3-3-3zm-4.5-2C6.672 9 6 9.672 6 10.5S6.672 12 7.5 12 9 11.328 9 10.5 8.328 9 7.5 9zm9 0c-.828 0-1.5.672-1.5 1.5s.672 1.5 1.5 1.5 1.5-.672 1.5-1.5-.672-1.5-1.5-1.5zm-6.5-3C9.172 4 8.5 4.672 8.5 5.5S9.172 7 10 7s1.5-.672 1.5-1.5S10.828 4 10 4zm4 0c-.828 0-1.5.672-1.5 1.5s.672 1.5 1.5 1.5 1.5-.672 1.5-1.5S14.828 4 14 4z"/>
                            </svg>
                        </span>
                        <span class="text-2xl font-bold tracking-tight text-pm-charcoal dark:text-gray-100 print:!text-black">PawMart<span class="text-pm-orange">.</span></span>
                    </div>
                    <p class="mt-2 text-sm font-bold text-pm-charcoal dark:text-gray-200 print:!text-black">PawMart Pet Shop</p>
                    <p class="text-xs text-pm-muted dark:text-gray-400 print:!text-gray-600">Transaksi Midtrans Sandbox</p>
                </div>
                <div class="text-right sm:text-right">
                    <span class="inline-block rounded-full bg-pm-orange-lt px-3 py-1 text-xs font-bold uppercase tracking-wider text-pm-orange print:border print:border-gray-400 print:!bg-transparent print:!text-black">
                        Invoice
                    </span>
                    <h1 class="mt-2 text-2xl font-bold text-pm-charcoal dark:text-gray-100 print:!text-black">Invoice pesanan</h1>
                    <p class="mt-1 break-all text-xs font-mono font-medium text-pm-muted dark:text-gray-400 print:!text-gray-700">{{ $order->order_number }}</p>
                    <p class="mt-0.5 text-xs text-pm-muted dark:text-gray-400 print:!text-gray-700">{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</p>
                </div>
            </header>

            {{-- Metadata Grid --}}
            <div class="my-6 grid gap-6 sm:grid-cols-2">
                <section class="rounded-2xl bg-pm-orange-lt/40 p-4 text-xs dark:bg-gray-800/50 print:border print:border-gray-200 print:!bg-transparent">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-pm-orange dark:text-orange-300 print:!text-black">Pelanggan</h2>
                    <p class="mt-2 text-sm font-semibold text-pm-charcoal dark:text-gray-100 break-words print:!text-black">{{ $order->user?->name }}</p>
                    <p class="mt-2 text-pm-muted dark:text-gray-400 print:!text-gray-700">
                        Status pembayaran: <strong class="text-pm-charcoal dark:text-gray-200 print:!text-black">{{ \App\Models\Order::PAYMENT_LABELS[$order->payment_status] ?? $order->payment_status }}</strong>
                    </p>
                </section>

                <section class="rounded-2xl bg-pm-cream/50 p-4 text-xs dark:bg-gray-800/50 print:border print:border-gray-200 print:!bg-transparent">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-pm-orange dark:text-orange-300 print:!text-black">Pengiriman</h2>
                    <p class="mt-2 text-sm font-semibold text-pm-charcoal dark:text-gray-100 break-words print:!text-black">{{ $order->recipient_name }} · {{ $order->recipient_phone }}</p>
                    <p class="mt-1.5 whitespace-pre-line text-pm-charcoal dark:text-gray-200 break-words leading-relaxed print:!text-black">{{ $order->shipping_address }}</p>
                    <p class="mt-1 text-pm-muted dark:text-gray-400 print:!text-gray-700">{{ $order->city }}, {{ $order->province }} {{ $order->postal_code }}</p>
                    @if($order->courier_name)
                        <div class="mt-2 border-t border-pm-border/60 pt-2 text-pm-charcoal dark:text-gray-300 print:border-gray-300 print:!text-black">
                            <span class="font-bold">Kurir:</span> {{ $order->courier_name }} ({{ $order->courier_service ?? 'Reguler' }})
                            @if($order->tracking_number)
                                · <span class="font-bold">Resi:</span> <span class="font-mono font-bold">{{ $order->tracking_number }}</span>
                            @endif
                        </div>
                    @endif
                </section>
            </div>

            {{-- Products Table --}}
            <div class="overflow-x-auto">
                <table class="w-full table-fixed text-left text-xs sm:text-sm print:text-sm">
                    <caption class="sr-only">Rincian transaksi historis</caption>
                    <thead>
                        <tr class="border-y border-pm-border dark:border-gray-800 print:border-gray-300">
                            <th class="w-[40%] py-3.5 pr-2 font-bold text-pm-charcoal dark:text-gray-200 print:!text-black">Produk</th>
                            <th class="w-[23%] py-3.5 pr-2 font-bold text-pm-charcoal dark:text-gray-200 print:!text-black">Harga satuan</th>
                            <th class="w-[10%] py-3.5 font-bold text-pm-charcoal dark:text-gray-200 print:!text-black">Qty</th>
                            <th class="w-[27%] py-3.5 text-right font-bold text-pm-charcoal dark:text-gray-200 print:!text-black">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-pm-border dark:divide-gray-800 print:divide-gray-200">
                        @foreach($order->items as $item)
                            <tr class="break-inside-avoid">
                                <td class="break-words py-4 pr-2 font-medium text-pm-charcoal dark:text-gray-100 print:!text-black">{{ $item->product_name }}</td>
                                <td class="break-words py-4 pr-2 text-pm-muted dark:text-gray-300 print:!text-gray-800">Rp{{ number_format($item->product_price, 2, ',', '.') }}</td>
                                <td class="py-4 text-pm-charcoal dark:text-gray-200 print:!text-black">{{ $item->quantity }}</td>
                                <td class="break-words py-4 text-right font-semibold text-pm-charcoal dark:text-gray-100 print:!text-black">Rp{{ number_format($item->subtotal, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totals Breakdown --}}
            <dl class="ml-auto mt-6 grid max-w-sm gap-2.5 text-xs sm:text-sm">
                @foreach(['subtotal' => 'Subtotal', 'shipping_cost' => 'Biaya pengiriman', 'total_amount' => 'Total pembayaran'] as $field => $label)
                    <div class="flex flex-wrap justify-between gap-3 {{ $field === 'total_amount' ? 'border-t border-pm-border pt-3 text-base font-bold text-pm-charcoal dark:border-gray-800 dark:text-gray-100 print:border-gray-400 print:!text-black' : 'text-pm-muted dark:text-gray-400 print:!text-gray-700' }}">
                        <dt>{{ $label }}</dt>
                        <dd class="{{ $field === 'total_amount' ? 'text-lg text-pm-green dark:text-green-400 print:!text-black' : 'font-medium text-pm-charcoal dark:text-gray-200 print:!text-black' }}">
                            Rp{{ number_format($order->$field, 2, ',', '.') }}
                        </dd>
                    </div>
                @endforeach
            </dl>

            {{-- Footer Note --}}
            <footer class="mt-8 border-t border-pm-border pt-5 text-xs text-pm-muted dark:border-gray-800 dark:text-gray-400 print:border-gray-300 print:!text-gray-600">
                Terima kasih telah berbelanja di PawMart. Invoice memakai nama dan harga produk saat transaksi. Invoice berstatus menunggu bukan bukti pembayaran berhasil.
            </footer>
        </article>
    </main>
</body>
</html>
