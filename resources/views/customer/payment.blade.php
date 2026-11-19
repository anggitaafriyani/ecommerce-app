<x-store-layout title="Pembayaran pesanan">
    <div class="mx-auto max-w-4xl px-5 py-8 sm:px-8 sm:py-12"
         x-data="{
             expiresAt: {{ $order->expires_at ? $order->expires_at->timestamp : 0 }},
             now: Math.floor(Date.now() / 1000),
             isExpired: {{ ($order->expires_at && $order->expires_at->isPast()) ? 'true' : 'false' }},
             timer: null,
             hours: '00',
             minutes: '00',
             seconds: '00',
             updateTimer() {
                 this.now = Math.floor(Date.now() / 1000);
                 let diff = this.expiresAt - this.now;
                 if (diff <= 0) {
                     this.isExpired = true;
                     this.hours = '00';
                     this.minutes = '00';
                     this.seconds = '00';
                     clearInterval(this.timer);
                     window.location.reload();
                     return;
                 }
                 let h = Math.floor(diff / 3600);
                 let m = Math.floor((diff % 3600) / 60);
                 let s = diff % 60;
                 this.hours = String(h).padStart(2, '0');
                 this.minutes = String(m).padStart(2, '0');
                 this.seconds = String(s).padStart(2, '0');
             },
             init() {
                 if (!this.isExpired && this.expiresAt > 0) {
                     this.updateTimer();
                     this.timer = setInterval(() => this.updateTimer(), 1000);
                 }
             }
         }">

        {{-- Back link --}}
        <div class="mb-6">
            <a class="pm-link inline-flex items-center gap-1.5 text-sm font-semibold hover:text-pm-orange" href="{{ route('orders.index') }}">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 011 10z" clip-rule="evenodd"/>
                </svg>
                Kembali ke Riwayat Pesanan
            </a>
        </div>

        {{-- Header Section --}}
        <div class="mb-8">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-pm-orange-lt px-3.5 py-1 text-xs font-bold text-pm-orange">
                Pembayaran Midtrans Sandbox
            </span>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-pm-charcoal dark:text-gray-50 sm:text-4xl">
                Pembayaran Pesanan
            </h1>
            <div class="mt-2.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-pm-muted dark:text-gray-400">
                <span>No. Pesanan: <strong class="font-mono font-bold text-pm-charcoal dark:text-gray-200 select-all">{{ $order->order_number }}</strong></span>
                <span aria-hidden="true">·</span>
                <span>{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</span>
            </div>
        </div>

        {{-- ════════════════════════════════════════
             STATUS BANNER & LIVE COUNTDOWN
        ════════════════════════════════════════ --}}
        @if(in_array($order->payment_status, ['paid', 'success'], true))
            {{-- CASE 1: PAID STATUS --}}
            <section class="rounded-3xl border border-pm-orange/30 bg-pm-cream/70 p-6 sm:p-7 dark:bg-gray-850 dark:border-gray-800">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-pm-orange text-white text-2xl shadow-xs">
                            ✓
                        </span>
                        <div>
                            <span class="rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider">
                                Lunas Terverifikasi
                            </span>
                            <h2 class="mt-1 text-xl font-extrabold text-pm-charcoal dark:text-gray-100">
                                Pembayaran Berhasil Diterima
                            </h2>
                            <p class="mt-0.5 text-xs text-pm-muted dark:text-gray-400">
                                Transaksi telah diverifikasi oleh gateway Midtrans. Pesanan sedang disiapkan penjual.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('orders.show', $order) }}" class="pm-button !rounded-full !px-6 text-xs font-bold shrink-0 self-start sm:self-auto">
                        Detail &amp; Lacak Pesanan
                    </a>
                </div>
            </section>

        @elseif($order->payment_status === 'expired' || ($order->expires_at && $order->expires_at->isPast() && $order->payment_status === 'pending') || $order->order_status === 'cancelled')
            {{-- CASE 2: EXPIRED / CANCELLED STATUS --}}
            <section class="rounded-3xl border border-red-200 bg-red-50/70 p-6 sm:p-7 dark:bg-red-950/20 dark:border-red-900/40">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-300 text-2xl shadow-xs">
                            ✕
                        </span>
                        <div>
                            <span class="rounded-full bg-red-100 text-red-700 dark:bg-red-900/60 dark:text-red-300 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider">
                                {{ $order->payment_status === 'expired' ? 'Waktu Habis / Kedaluwarsa' : 'Pesanan Dibatalkan' }}
                            </span>
                            <h2 class="mt-1 text-xl font-extrabold text-pm-charcoal dark:text-gray-100">
                                {{ $order->payment_status === 'expired' ? 'Batas Waktu Pembayaran Telah Berakhir' : 'Pesanan Telah Dibatalkan' }}
                            </h2>
                            <p class="mt-0.5 text-xs text-pm-muted dark:text-gray-300">
                                Reservasi stok produk telah dilepaskan kembali ke katalog toko secara aman.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('products.index') }}" class="pm-secondary !rounded-full !px-6 text-xs font-bold shrink-0 self-start sm:self-auto">
                        Belanja Produk Lain
                    </a>
                </div>
            </section>

        @else
            {{-- CASE 3: PENDING PAYMENT WITH COUNTDOWN --}}
            <section class="rounded-3xl border border-pm-orange/30 bg-pm-cream/70 p-6 sm:p-7 dark:bg-gray-850 dark:border-gray-800 shadow-xs">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">
                                Menunggu pembayaran
                            </span>
                        </div>
                        <h2 class="mt-1 text-2xl font-extrabold text-pm-charcoal dark:text-gray-100">
                            Selesaikan Pembayaran Kamu
                        </h2>
                        <p class="mt-1 text-xs text-pm-muted dark:text-gray-300 leading-relaxed max-w-lg">
                            Segera lakukan pembayaran sebelum batas waktu berakhir agar reservasi stok anabul kesayangan kamu tidak dilepas kembali ke toko.
                        </p>
                    </div>

                    {{-- Digital Countdown Timer --}}
                    @if($order->expires_at)
                        <div class="flex flex-col items-center md:items-end shrink-0">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-pm-muted mb-1.5">Sisa Waktu Pembayaran</span>
                            <div class="flex items-center gap-1.5">
                                <div class="flex h-12 w-12 flex-col items-center justify-center rounded-2xl bg-white shadow-xs border border-pm-border dark:bg-gray-800 dark:border-gray-700">
                                    <span class="text-base font-extrabold text-pm-orange" x-text="hours">00</span>
                                    <span class="text-[9px] font-bold uppercase text-pm-muted">Jam</span>
                                </div>
                                <span class="text-lg font-bold text-pm-orange">:</span>
                                <div class="flex h-12 w-12 flex-col items-center justify-center rounded-2xl bg-white shadow-xs border border-pm-border dark:bg-gray-800 dark:border-gray-700">
                                    <span class="text-base font-extrabold text-pm-orange" x-text="minutes">00</span>
                                    <span class="text-[9px] font-bold uppercase text-pm-muted">Menit</span>
                                </div>
                                <span class="text-lg font-bold text-pm-orange">:</span>
                                <div class="flex h-12 w-12 flex-col items-center justify-center rounded-2xl bg-white shadow-xs border border-pm-border dark:bg-gray-800 dark:border-gray-700">
                                    <span class="text-base font-extrabold text-pm-orange" x-text="seconds">00</span>
                                    <span class="text-[9px] font-bold uppercase text-pm-muted">Detik</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- ════════════════════════════════════════
             PAYMENT SUMMARY CARD
        ════════════════════════════════════════ --}}
        <section class="pm-card mt-6 p-6 sm:p-8 rounded-[2rem] border-pm-border/80 shadow-xs">
            <h2 class="text-lg font-bold text-pm-charcoal dark:text-gray-100 pb-3 border-b border-pm-border/60 dark:border-gray-800">
                Rincian Tagihan Belanja
            </h2>

            <div class="mt-4 divide-y divide-pm-border/60 dark:divide-gray-800">
                @foreach($order->items as $item)
                    <div class="flex flex-wrap items-center justify-between gap-4 py-3.5 text-sm">
                        <div class="min-w-0">
                            <p class="break-words font-bold text-pm-charcoal dark:text-gray-200">
                                {{ $item->product_name }}
                            </p>
                            <p class="mt-0.5 text-xs text-pm-muted dark:text-gray-400">
                                {{ $item->quantity }} × Rp{{ number_format($item->product_price, 2, ',', '.') }}
                            </p>
                        </div>
                        <span class="font-extrabold text-pm-charcoal dark:text-gray-200">
                            Rp{{ number_format($item->subtotal, 2, ',', '.') }}
                        </span>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 space-y-3 border-t border-pm-border/80 pt-4 text-sm dark:border-gray-800">
                <div class="flex justify-between text-pm-muted dark:text-gray-400">
                    <span>Subtotal produk</span>
                    <span class="font-bold text-pm-charcoal dark:text-gray-200">
                        Rp{{ number_format($order->subtotal, 2, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between text-pm-muted dark:text-gray-400">
                    <span>Ongkos kirim ({{ $order->courier_name ?? 'Kurir' }} {{ $order->courier_service ?? 'Reguler' }})</span>
                    <span class="font-bold text-pm-charcoal dark:text-gray-200">
                        Rp{{ number_format($order->shipping_cost, 2, ',', '.') }}
                    </span>
                </div>
                <div class="flex flex-wrap items-baseline justify-between gap-4 border-t border-pm-border/80 pt-4 text-lg font-bold text-pm-charcoal dark:text-gray-100 dark:border-gray-800">
                    <div>
                        <span class="block text-sm font-semibold text-pm-muted">Total Pembayaran</span>
                        <span class="text-xs font-normal text-pm-muted">Termasuk PPN &amp; seluruh biaya</span>
                    </div>
                    <span class="text-2xl font-extrabold text-pm-orange dark:text-orange-400">
                        Rp{{ number_format($order->total_amount, 2, ',', '.') }}
                    </span>
                </div>
            </div>
        </section>

        {{-- Payment error alert --}}
        <p id="payment-message" class="mt-4 text-xs font-semibold text-red-600 dark:text-red-400" role="alert"></p>

        {{-- Gateway unconfigured alert --}}
        @if(!$configured)
            <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-900 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-200">
                <p>Pembayaran belum tersedia: kredensial Midtrans Sandbox belum dikonfigurasi. Hubungi pengelola.</p>
            </div>
        @endif

        {{-- ════════════════════════════════════════
             ACTION BUTTONS
        ════════════════════════════════════════ --}}
        <div class="mt-8 flex flex-wrap items-center gap-3">
            {{-- Big Pay Now Button (Only when pending and valid) --}}
            @if($configured && $order->canContinuePayment() && !($order->expires_at && $order->expires_at->isPast()))
                <button type="button"
                        class="pm-button !rounded-full !py-3.5 !px-8 text-sm font-extrabold shadow-card"
                        id="pay-now"
                        data-url="{{ route('payments.start', $order) }}">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                    </svg>
                    Bayar Sekarang
                </button>
            @endif

            @if($configured && $order->payment_status === 'pending')
                <form id="payment-status-form" method="POST" action="{{ route('payments.refresh', $order) }}">
                    @csrf
                    <button class="pm-secondary !rounded-full !py-3 !px-5 text-xs font-bold">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                        </svg>
                        Periksa Status Pembayaran
                    </button>
                </form>
            @endif

            <a class="pm-secondary !rounded-full !py-3 !px-5 text-xs font-semibold" href="{{ route('orders.show', $order) }}">
                Detail Pesanan &amp; Alamat
            </a>

            @if(in_array($order->payment_status, ['paid', 'success'], true))
                <a class="pm-button !rounded-full !py-3 !px-6 text-xs font-bold" href="{{ route('orders.invoice', $order) }}">
                    Lihat Invoice
                </a>
            @endif

            {{-- Cancel Order Button with Modal trigger --}}
            @if($order->payment_status === 'pending' && $order->stock_reserved && !in_array($order->order_status, ['shipped', 'completed'], true))
                <button type="button"
                        x-data
                        x-on:click="$dispatch('open-modal', 'cancel-payment-modal')"
                        class="pm-secondary !rounded-full !py-3 !px-5 text-xs font-semibold text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40">
                    Batalkan Pesanan
                </button>
            @endif
        </div>

        {{-- Modal Pembatalan Pesanan --}}
        @if($order->payment_status === 'pending' && $order->stock_reserved && !in_array($order->order_status, ['processing', 'shipped', 'completed', 'cancelled'], true))
            <x-modal name="cancel-payment-modal" focusable>
                <form method="POST" action="{{ route('orders.cancel', $order) }}" class="p-6 sm:p-8 text-pm-charcoal dark:text-gray-100">
                    @csrf
                    <div class="flex items-center gap-3.5">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-red-100 text-red-600 dark:bg-red-950/60 dark:text-red-300 text-xl">
                            ✕
                        </span>
                        <div>
                            <h2 class="text-xl font-extrabold text-pm-charcoal dark:text-gray-100">Batalkan Pesanan Ini?</h2>
                            <p class="text-xs text-pm-muted dark:text-gray-400">Nomor: {{ $order->order_number }}</p>
                        </div>
                    </div>

                    <p class="mt-4 text-sm leading-relaxed text-pm-muted dark:text-gray-300">
                        Pesanan yang belum dibayar dapat dibatalkan. Setelah dibatalkan, pesanan tidak dapat dilanjutkan dan stok produk akan tersedia kembali.
                    </p>

                    <div class="mt-6 flex justify-end gap-3 border-t border-pm-border pt-4 dark:border-gray-800">
                        <button type="button" class="pm-secondary !py-2.5 !px-5 text-xs font-semibold" x-on:click="$dispatch('close')">
                            Kembali
                        </button>
                        <button type="submit" class="pm-danger !py-2.5 !px-6 text-xs font-bold shadow-xs">
                            Ya, Batalkan Pesanan
                        </button>
                    </div>
                </form>
            </x-modal>
        @endif

        {{-- Hidden form for Midtrans Snap callback --}}
        @if($configured)
            <form id="payment-callback-form" method="POST" action="{{ route('payments.refresh', $order) }}" hidden>
                @csrf
            </form>
            <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}" defer></script>
        @endif
    </div>
</x-store-layout>
