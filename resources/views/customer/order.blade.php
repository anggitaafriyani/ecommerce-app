<x-store-layout title="Detail pesanan">
    <div class="mx-auto max-w-5xl px-5 py-8 sm:px-8 sm:py-12"
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
                ← Riwayat pesanan
            </a>
        </div>

        {{-- Header --}}
        <div class="mb-6">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-pm-orange-lt px-3.5 py-1 text-xs font-bold text-pm-orange">
                Detail Transaksi Sahabat Bulu
            </span>
            <h1 class="mt-2 break-all text-2xl font-extrabold tracking-tight text-pm-charcoal dark:text-gray-50 sm:text-3xl">
                {{ $order->order_number }}
            </h1>
            <p class="mt-1 text-xs text-pm-muted dark:text-gray-400">
                Dibuat pada {{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB
            </p>
        </div>

        {{-- Status Notification Card --}}
        @if($order->payment_status === 'pending' && $order->stock_reserved && !in_array($order->order_status, ['shipped', 'completed', 'cancelled'], true))
            <section class="my-6 rounded-3xl border border-pm-orange/30 bg-pm-cream/70 p-6 sm:p-7 dark:bg-gray-850 dark:border-gray-800 shadow-xs">
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
                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-pm-muted dark:text-gray-400">
                            <span>Batas Pembayaran: <strong class="font-bold text-pm-charcoal dark:text-gray-200">{{ $order->expires_at?->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</strong></span>
                            <span aria-hidden="true">·</span>
                            <span>Total Tagihan: <strong class="text-sm font-extrabold text-pm-orange dark:text-orange-400">Rp{{ number_format($order->total_amount, 2, ',', '.') }}</strong></span>
                        </div>
                        <p class="mt-2 text-xs text-pm-muted dark:text-gray-400">
                            Status pembayaran diperbarui melalui verifikasi server Midtrans.
                        </p>
                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            @if($order->canContinuePayment())
                                <a class="pm-button !rounded-full !px-6 !py-2.5 text-xs font-bold text-white shadow-card" href="{{ route('payments.show', $order) }}">
                                    Bayar Sekarang →
                                </a>
                            @endif
                            <button type="button"
                                    x-on:click="$dispatch('open-modal', 'cancel-order-modal')"
                                    class="pm-secondary !rounded-full !px-5 !py-2.5 font-semibold text-xs text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40">
                                Batalkan Pesanan
                            </button>
                        </div>
                    </div>

                    {{-- Digital Countdown Timer --}}
                    @if($order->expires_at)
                        <div class="flex flex-col items-start md:items-end shrink-0">
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
        @else
            <section class="my-6 rounded-3xl border border-pm-orange/20 bg-pm-cream/40 p-5 dark:border-amber-800/40 dark:bg-amber-950/20">
                <div class="flex flex-wrap items-center gap-3">
                    <x-order-status :status="$order->payment_status" type="payment" />
                    <x-order-status :status="$order->order_status" />
                </div>
                @if($order->order_status === 'cancelled')
                    <p class="mt-3 text-sm font-semibold text-red-600 dark:text-red-400">
                        {{ $order->payment_status === 'expired' ? 'Waktu pembayaran telah habis. Pesanan telah dibatalkan dan stok dikembalikan.' : 'Pesanan ini telah dibatalkan dan reservasi stok produk telah dilepaskan.' }}
                    </p>
                @else
                    <p class="mt-3 text-sm text-pm-charcoal dark:text-gray-200">
                        Status pembayaran diperbarui melalui verifikasi server Midtrans.
                    </p>
                @endif
            </section>
        @endif

        {{-- Progress Timeline & Summary --}}
        <div class="space-y-6">
            <x-order-progress :order="$order" />

            {{-- ════════════════════════════════════════
                 SHIPPING & TRACKING INFO CARD
            ════════════════════════════════════════ --}}
            <section class="pm-card p-6 sm:p-7 rounded-[2rem] border-pm-border/80">
                <div class="flex items-center justify-between border-b border-pm-border/80 pb-4 dark:border-gray-800">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-pm-orange-lt text-pm-orange">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="1" y="3" width="15" height="13"/>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                                <circle cx="5.5" cy="18.5" r="2.5"/>
                                <circle cx="18.5" cy="18.5" r="2.5"/>
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-lg font-bold text-pm-charcoal dark:text-gray-100">Informasi Pengiriman</h2>
                            <p class="text-xs text-pm-muted dark:text-gray-400">Status ekspedisi dan pelacakan internal PawMart</p>
                        </div>
                    </div>
                    <span class="rounded-full bg-pm-cream px-3 py-1 text-xs font-bold text-pm-charcoal dark:bg-gray-800 dark:text-gray-200">
                        {{ $order->courier_name ?? 'JNE' }} ({{ $order->courier_service ?? 'Reguler' }})
                    </span>
                </div>

                <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 text-sm">
                    {{-- Tracking Number (Resi) --}}
                    <div class="rounded-2xl border border-pm-border/70 bg-white p-4 dark:border-gray-800 dark:bg-gray-850"
                         x-data="{ copied: false }">
                        <span class="text-xs font-semibold text-pm-muted dark:text-gray-400">Nomor Resi Pengiriman</span>
                        @if($order->tracking_number)
                            <div class="mt-2 flex items-center justify-between gap-2">
                                <span class="font-mono font-bold text-base text-pm-orange select-all">{{ $order->tracking_number }}</span>
                                <button type="button"
                                        x-on:click="navigator.clipboard.writeText('{{ $order->tracking_number }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="rounded-lg bg-pm-orange-lt px-2.5 py-1 text-xs font-bold text-pm-orange hover:bg-pm-orange hover:text-white transition-colors">
                                    <span x-text="copied ? 'Tersalin! ✓' : 'Salin Resi'">Salin Resi</span>
                                </button>
                            </div>
                        @else
                            <p class="mt-2 text-xs italic text-pm-muted dark:text-gray-400">
                                Nomor resi akan tersedia setelah paket diserahkan ke kurir oleh pengelola toko.
                            </p>
                        @endif
                    </div>

                    {{-- Weight & Shipping Cost --}}
                    <div class="rounded-2xl border border-pm-border/70 bg-white p-4 dark:border-gray-800 dark:bg-gray-850">
                        <span class="text-xs font-semibold text-pm-muted dark:text-gray-400">Berat &amp; Ongkos Kirim</span>
                        <p class="mt-2 font-bold text-pm-charcoal dark:text-gray-100">
                            {{ number_format($order->total_weight ?? 500, 0, ',', '.') }} gram · Rp{{ number_format($order->shipping_cost, 2, ',', '.') }}
                        </p>
                    </div>

                    {{-- Shipping Timestamps --}}
                    <div class="rounded-2xl border border-pm-border/70 bg-white p-4 dark:border-gray-800 dark:bg-gray-850">
                        <span class="text-xs font-semibold text-pm-muted dark:text-gray-400">Waktu Pengiriman</span>
                        <p class="mt-2 font-medium text-pm-charcoal dark:text-gray-100">
                            {{ $order->shipped_at ? $order->shipped_at->timezone('Asia/Jakarta')->format('d M Y H:i').' WIB' : 'Menunggu pengiriman' }}
                        </p>
                        @if($order->completed_at)
                            <p class="mt-1 text-xs font-bold text-pm-orange dark:text-orange-300">
                                Diterima: {{ $order->completed_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Internal Tracking Timeline --}}
                @if($order->histories->isNotEmpty())
                    <div class="mt-6 border-t border-pm-border/80 pt-5 dark:border-gray-800">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-pm-muted dark:text-gray-400">Riwayat Status Pesanan</h3>
                        <ol class="mt-4 relative border-l-2 border-pm-orange/30 ml-3 space-y-4">
                            @foreach($order->histories as $history)
                                <li class="ml-5">
                                    <span class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border-2 border-white bg-pm-orange dark:border-gray-900"></span>
                                    <div class="flex flex-wrap items-baseline gap-2">
                                        <span class="font-bold text-xs uppercase tracking-wider text-pm-charcoal dark:text-gray-200">
                                            {{ \App\Models\Order::STATUS_LABELS[$history->status] ?? $history->status }}
                                        </span>
                                        <span class="text-[11px] text-pm-muted dark:text-gray-400">
                                            {{ $history->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB
                                        </span>
                                    </div>
                                    @if($history->notes)
                                        <p class="mt-1 text-xs text-pm-muted dark:text-gray-400">{{ $history->notes }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif
            </section>

            <x-order-summary :order="$order" />
        </div>

        {{-- ════════════════════════════════════════
             ACTION BUTTONS & RECEIPT CONFIRMATION
        ════════════════════════════════════════ --}}
        <div class="mt-8 flex flex-wrap items-center gap-4 border-t border-pm-border/80 pt-6 dark:border-gray-800">
            {{-- Primary Customer Confirmation Action (Warm Pet&Co Themed) --}}
            @if($order->order_status === 'shipped' && $order->payment_status === 'paid')
                <div class="w-full rounded-3xl bg-pm-cream/70 border border-pm-orange/30 p-5 text-xs text-pm-charcoal dark:bg-gray-850 dark:border-gray-800 dark:text-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                    <div class="flex items-center gap-3.5">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-pm-orange-lt text-pm-orange text-xl">
                            📦
                        </span>
                        <div>
                            <p class="font-bold text-sm text-pm-charcoal dark:text-gray-100">Paket anabul kamu sudah tiba?</p>
                            <p class="text-xs text-pm-muted dark:text-gray-300 mt-0.5">Konfirmasikan penerimaan agar transaksi selesai dan kamu bisa memberikan ulasan produk.</p>
                        </div>
                    </div>
                    <button type="button"
                            x-data
                            x-on:click="$dispatch('open-modal', 'confirm-received-modal')"
                            class="pm-button !rounded-full !px-6 !py-3 text-xs font-bold shadow-card shrink-0">
                        ✓ Pesanan Diterima
                    </button>
                </div>
            @endif

            <a class="pm-button !rounded-full !px-6 font-bold" href="{{ route('orders.invoice', $order) }}">
                Lihat Invoice
            </a>

            {{-- Only show payment continuation if order is pending and valid --}}
            @if($order->canContinuePayment())
                <a class="pm-button !bg-amber-600 hover:!bg-amber-700 !rounded-full !px-6 font-bold text-white shadow-xs" href="{{ route('payments.show', $order) }}">
                    Bayar Sekarang →
                </a>
            @endif

            <a class="pm-secondary !rounded-full !px-6 font-semibold" href="{{ route('products.index') }}">
                Kembali ke katalog
            </a>

            @if(in_array($order->order_status, ['shipped', 'completed'], true))
                <button type="button"
                        x-data
                        x-on:click="$dispatch('open-modal', 'report-issue-modal')"
                        class="pm-secondary !rounded-full !px-5 font-semibold text-xs text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40">
                    ⚠️ Laporkan Kendala
                </button>
            @endif

            {{-- Cancel Order Button (Only for eligible pending orders) --}}
            @if($order->payment_status === 'pending' && $order->stock_reserved && !in_array($order->order_status, ['shipped', 'completed'], true))
                <button type="button"
                        x-data
                        x-on:click="$dispatch('open-modal', 'cancel-order-modal')"
                        class="pm-secondary !rounded-full !px-6 font-semibold text-xs text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40">
                    Batalkan Pesanan
                </button>
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════════════
         MODAL 1: KONFIRMASI PENERIMAAN PESANAN (Pet&Co Theme)
    ════════════════════════════════════════ --}}
    <x-modal name="confirm-received-modal" focusable>
        <form method="POST" action="{{ route('orders.confirm-received', $order) }}" class="p-6 sm:p-8 text-pm-charcoal dark:text-gray-100">
            @csrf
            <div class="flex items-center gap-3.5">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-pm-orange-lt text-pm-orange text-2xl shadow-xs">
                    🎁
                </span>
                <div>
                    <h2 class="text-xl font-extrabold text-pm-charcoal dark:text-gray-100">Konfirmasi Penerimaan Paket</h2>
                    <p class="text-xs text-pm-muted dark:text-gray-400">Nomor: {{ $order->order_number }}</p>
                </div>
            </div>

            <div class="mt-4 rounded-2xl bg-pm-cream/60 p-4 text-xs text-pm-charcoal dark:bg-gray-800/60 dark:text-gray-200 leading-relaxed border border-pm-border/60">
                <p class="font-bold text-sm text-pm-orange mb-1">Penting sebelum konfirmasi:</p>
                <p>Pastikan seluruh makanan, cemilan, vitamin, atau mainan anabul telah Anda terima secara lengkap dan tanpa kerusakan fisik. Setelah dikonfirmasi, status pesanan akan diselesaikan secara permanen.</p>
            </div>

            <div class="mt-6 flex justify-end gap-3 border-t border-pm-border pt-4 dark:border-gray-800">
                <button type="button" class="pm-secondary !py-2.5 !px-5 text-xs font-semibold" x-on:click="$dispatch('close')">
                    Periksa Lagi
                </button>
                <button type="submit" class="pm-button !py-2.5 !px-6 text-xs font-bold text-white shadow-card">
                    Ya, Saya Sudah Terima Paket
                </button>
            </div>
        </form>
    </x-modal>

    {{-- ════════════════════════════════════════
         MODAL 2: KONFIRMASI PEMBATALAN PESANAN (Pet&Co Theme)
    ════════════════════════════════════════ --}}
    @if($order->payment_status === 'pending' && $order->stock_reserved && !in_array($order->order_status, ['processing', 'shipped', 'completed', 'cancelled'], true))
        <x-modal name="cancel-order-modal" focusable>
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

    {{-- ════════════════════════════════════════
         MODAL 3: LAPORKAN MASALAH PESANAN
    ════════════════════════════════════════ --}}
    <x-modal name="report-issue-modal" focusable>
        <form method="POST" action="{{ route('orders.issues.store', $order) }}" class="p-6 sm:p-8 text-pm-charcoal dark:text-gray-100">
            @csrf
            <div class="flex items-center gap-3">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 9v4"/>
                        <path d="M12 17h.01"/>
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-xl font-bold">Laporkan Kendala Pesanan</h2>
                    <p class="text-xs text-pm-muted dark:text-gray-400">Pengelola toko akan memeriksa laporan kendala Anda</p>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                <div>
                    <label class="pm-label" for="issue_category">Kategori kendala <span class="text-red-500">*</span></label>
                    <select id="issue_category" name="category" class="pm-input" required>
                        <option value="">Pilih jenis kendala...</option>
                        @foreach(\App\Models\OrderIssue::CATEGORY_LABELS as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="pm-label" for="issue_description">Penjelasan kendala <span class="text-red-500">*</span></label>
                    <textarea id="issue_description"
                              name="description"
                              rows="4"
                              class="pm-input"
                              placeholder="Jelaskan secara rinci kendala paket atau barang yang Anda terima..."
                              required
                              maxlength="2000"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 border-t border-pm-border pt-4 dark:border-gray-800">
                <button type="button" class="pm-secondary !py-2 !px-5 text-xs font-semibold" x-on:click="$dispatch('close')">
                    Batal
                </button>
                <button type="submit" class="pm-button !bg-red-600 hover:!bg-red-700 !py-2 !px-5 text-xs font-bold text-white shadow-xs">
                    Kirim Laporan
                </button>
            </div>
        </form>
    </x-modal>
</x-store-layout>
