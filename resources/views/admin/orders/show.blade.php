<x-admin-layout title="Detail pesanan" subtitle="Tinjau transaksi, kelola pengiriman, dan pantau status pesanan pelanggan.">
    <x-slot:actions>
        <a class="pm-secondary" href="{{ route('admin.orders.index') }}">
            ← Daftar pesanan
        </a>
    </x-slot:actions>

    {{-- Order Summary Header Banner --}}
    <section class="mb-6 flex flex-wrap items-center justify-between gap-5 rounded-2xl border border-pm-orange/30 bg-pm-orange-lt/30 p-6 dark:border-amber-800 dark:bg-amber-900/30">
        <div class="min-w-0">
            <span class="text-xs font-semibold uppercase tracking-wider text-pm-muted dark:text-gray-400">Nomor Pesanan</span>
            <h2 class="break-all text-xl font-bold font-mono text-pm-charcoal dark:text-gray-100 sm:text-2xl">{{ $order->order_number }}</h2>
            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-pm-muted dark:text-gray-400">
                <span class="font-semibold text-pm-charcoal dark:text-gray-200">{{ $order->user?->name }}</span>
                <span aria-hidden="true">·</span>
                <span>{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <x-order-status :status="$order->payment_status" type="payment" />
            <x-order-status :status="$order->order_status" />
        </div>
    </section>

    {{-- Reported Issues Box if any --}}
    @if($order->issues && $order->issues->isNotEmpty())
        <section class="mb-6 rounded-2xl border border-red-300 bg-red-50/70 p-6 dark:border-red-900/60 dark:bg-red-950/30">
            <div class="flex items-center gap-2 text-red-800 dark:text-red-300">
                <span class="text-xl">⚠️</span>
                <h3 class="font-bold text-base">Laporan Kendala dari Pelanggan</h3>
            </div>
            <div class="mt-4 space-y-3">
                @foreach($order->issues as $issue)
                    <div class="rounded-xl border border-red-200 bg-white p-4 text-xs dark:border-red-900 dark:bg-gray-850">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-red-700 dark:text-red-400 uppercase tracking-wider">
                                {{ \App\Models\OrderIssue::CATEGORY_LABELS[$issue->category] ?? $issue->category }}
                            </span>
                            <span class="text-pm-muted dark:text-gray-400">{{ $issue->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</span>
                        </div>
                        <p class="mt-2 text-sm text-pm-charcoal dark:text-gray-200 leading-relaxed">{{ $issue->description }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Order Progress & Order Summary Components --}}
    <div class="space-y-6">
        <x-order-progress :order="$order" />

        {{-- Shipping & Courier Snapshot Card --}}
        <section class="pm-card p-6 sm:p-7">
            <div class="flex items-center justify-between border-b border-pm-border pb-4 dark:border-gray-800">
                <div>
                    <h2 class="text-lg font-bold text-pm-charcoal dark:text-gray-100">Informasi Kurir & Ekspedisi</h2>
                    <p class="text-xs text-pm-muted dark:text-gray-400">Pilihan kurir dan tarif pengiriman yang dipilih pelanggan</p>
                </div>
                <span class="rounded-full bg-pm-orange-lt px-3 py-1 text-xs font-bold text-pm-orange">
                    {{ $order->courier_name ?? 'JNE' }} ({{ $order->courier_service ?? 'Reguler' }})
                </span>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-3 text-sm">
                <div class="rounded-xl border border-pm-border bg-pm-cream/30 p-3.5 dark:border-gray-800 dark:bg-gray-850">
                    <span class="text-xs font-semibold text-pm-muted dark:text-gray-400">Berat Paket</span>
                    <p class="mt-1 font-bold text-pm-charcoal dark:text-gray-100">{{ number_format($order->total_weight ?? 500, 0, ',', '.') }} gram</p>
                </div>
                <div class="rounded-xl border border-pm-border bg-pm-cream/30 p-3.5 dark:border-gray-800 dark:bg-gray-850">
                    <span class="text-xs font-semibold text-pm-muted dark:text-gray-400">Biaya Ongkos Kirim</span>
                    <p class="mt-1 font-bold text-pm-charcoal dark:text-gray-100">Rp{{ number_format($order->shipping_cost, 2, ',', '.') }}</p>
                </div>
                <div class="rounded-xl border border-pm-border bg-pm-cream/30 p-3.5 dark:border-gray-800 dark:bg-gray-850">
                    <span class="text-xs font-semibold text-pm-muted dark:text-gray-400">Nomor Resi</span>
                    <p class="mt-1 font-mono font-bold text-pm-orange">{{ $order->tracking_number ?? 'Belum ada resi' }}</p>
                </div>
            </div>
        </section>

        <x-order-summary :order="$order" />

        {{-- Fulfillment Processing Section --}}
        <section class="pm-card p-6 sm:p-8">
            <h2 class="text-xl font-bold text-pm-charcoal dark:text-gray-100 border-b border-pm-border pb-4 dark:border-gray-800">
                Konfirmasi & Pemrosesan Pengiriman
            </h2>

            @if($order->order_status === 'processing' && $order->payment_status === 'paid')
                {{-- Form to confirm shipment & enter tracking number --}}
                <div class="mt-4 rounded-xl bg-blue-50 border border-blue-200 p-4 text-xs text-blue-900 dark:bg-blue-950/40 dark:border-blue-800 dark:text-blue-200">
                    <p class="font-bold">Pembayaran terverifikasi! Masukkan nomor resi ekspedisi setelah barang diserahkan ke kurir.</p>
                </div>

                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="mt-6 space-y-4 max-w-xl">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="order_status" value="shipped">

                    <div>
                        <label class="pm-label">Kurir & Layanan (Pilihan Pelanggan)</label>
                        <input type="text" class="pm-input !bg-gray-100 cursor-not-allowed font-semibold text-pm-charcoal"
                               value="{{ $order->courier_name ?? 'JNE' }} - {{ $order->courier_service ?? 'Reguler' }}" readonly>
                    </div>

                    <div>
                        <label class="pm-label" for="tracking_number">
                            Nomor Resi Pengiriman <span class="text-red-500">*</span>
                        </label>
                        <input class="pm-input @error('tracking_number') pm-input-error @enderror"
                               id="tracking_number"
                               name="tracking_number"
                               type="text"
                               required
                               placeholder="Contoh: JNE88291029103"
                               value="{{ old('tracking_number', $order->tracking_number) }}">
                        <x-input-error :messages="$errors->get('tracking_number')" class="mt-1.5" />
                    </div>

                    <div>
                        <label class="pm-label" for="shipping_notes">Catatan Pengiriman (opsional)</label>
                        <input class="pm-input @error('shipping_notes') pm-input-error @enderror"
                               id="shipping_notes"
                               name="shipping_notes"
                               type="text"
                               placeholder="Contoh: Paket diantar kurir jam 14.00"
                               value="{{ old('shipping_notes', $order->shipping_notes) }}">
                        <x-input-error :messages="$errors->get('shipping_notes')" class="mt-1.5" />
                    </div>

                    <button class="pm-button-lg shadow-card font-bold" type="submit">
                        Tandai Dikirim &amp; Simpan Resi
                    </button>
                </form>
            @elseif($order->order_status === 'shipped')
                <div class="mt-4 rounded-xl bg-pm-orange-lt/50 border border-pm-orange/30 p-4 text-sm text-pm-charcoal dark:bg-orange-950/30 dark:border-orange-800 dark:text-orange-200">
                    <p class="font-bold text-pm-orange">📦 Paket telah berstatus Dikirim (Nomor Resi: {{ $order->tracking_number ?? '—' }}).</p>
                    <p class="mt-1 text-xs text-pm-muted dark:text-gray-400">
                        Menunggu konfirmasi penerimaan langsung dari pelanggan di dashboard toko. Admin tidak memiliki tombol penyelesaian pesanan sepihak.
                    </p>
                </div>
            @elseif($order->order_status === 'completed')
                <div class="mt-4 rounded-xl bg-green-50 border border-green-200 p-4 text-sm text-green-800 dark:bg-green-950/40 dark:border-green-800 dark:text-green-200">
                    <p class="font-bold">✓ Pesanan telah Selesai dan diterima dengan baik oleh pelanggan.</p>
                    @if($order->completed_at)
                        <p class="mt-1 text-xs text-green-700 dark:text-green-300">Waktu konfirmasi pelanggan: {{ $order->completed_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</p>
                    @endif
                </div>
            @else
                <div class="mt-4 rounded-xl bg-pm-cream/60 p-4 text-sm text-pm-muted dark:bg-gray-800/60 dark:text-gray-400">
                    <p>Pemrosesan belum dapat dilanjutkan. Pesanan harus memiliki pembayaran terverifikasi oleh Midtrans, tanpa masalah pembayaran atau pelepasan stok.</p>
                </div>
            @endif

            <p class="mt-5 text-xs text-pm-muted dark:text-gray-400 border-t border-pm-border pt-4 dark:border-gray-800">
                Status pembayaran ditentukan oleh verifikasi Midtrans dan tidak dapat diubah di halaman ini.
            </p>
        </section>

        {{-- Order Status History Timeline --}}
        @if($order->histories && $order->histories->isNotEmpty())
            <section class="pm-card p-6 sm:p-7">
                <h3 class="text-sm font-bold uppercase tracking-wider text-pm-charcoal dark:text-gray-100 border-b border-pm-border pb-3 dark:border-gray-800">
                    Riwayat Aktivitas &amp; Status Pesanan
                </h3>
                <ol class="mt-4 relative border-l-2 border-pm-orange/30 ml-3 space-y-4">
                    @foreach($order->histories as $history)
                        <li class="ml-5">
                            <span class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border-2 border-white bg-pm-orange dark:border-gray-900"></span>
                            <div class="flex flex-wrap items-baseline gap-2">
                                <span class="font-bold text-xs uppercase tracking-wider text-pm-charcoal dark:text-gray-200">
                                    {{ \App\Models\Order::STATUS_LABELS[$history->status] ?? $history->status }}
                                </span>
                                <span class="text-[11px] text-pm-muted dark:text-gray-400">
                                    oleh {{ $history->actor_name ?? $history->actor_type }} · {{ $history->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB
                                </span>
                            </div>
                            @if($history->notes)
                                <p class="mt-1 text-xs text-pm-muted dark:text-gray-400">{{ $history->notes }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif
    </div>
</x-admin-layout>
