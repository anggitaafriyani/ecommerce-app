@props(['order'])
<div class="grid items-start gap-6 md:grid-cols-2">
    {{-- Left: Ordered Products --}}
    <section class="pm-card min-w-0 p-6 sm:p-7 rounded-[2rem]">
        <div class="flex items-center justify-between pb-3 border-b border-pm-border/80 dark:border-gray-800">
            <h2 class="text-lg font-bold text-pm-charcoal dark:text-gray-100">Produk Pesanan</h2>
            <span class="rounded-full bg-pm-cream px-3 py-0.5 text-xs font-bold text-pm-muted dark:bg-gray-800">
                {{ $order->items->count() }} item
            </span>
        </div>

        <div class="mt-4 divide-y divide-pm-border dark:divide-gray-800">
            @foreach($order->items as $item)
                <div class="py-4 text-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="break-words font-bold text-pm-charcoal dark:text-gray-200">{{ $item->product_name }}</p>
                            <p class="mt-0.5 text-xs text-pm-muted dark:text-gray-400">
                                {{ $item->quantity }} × Rp{{ number_format($item->product_price, 2, ',', '.') }}
                            </p>
                        </div>
                        <p class="font-extrabold text-pm-charcoal dark:text-gray-200">
                            Rp{{ number_format($item->subtotal, 2, ',', '.') }}
                        </p>
                    </div>

                    {{-- Review Section for Completed Orders --}}
                    @if($order->order_status === 'completed')
                        <div class="mt-3.5 flex flex-wrap items-center justify-between gap-2 rounded-2xl bg-pm-cream/60 p-3 dark:bg-gray-800/60">
                            @if($item->review)
                                <div class="min-w-0 flex-1 text-xs">
                                    <div class="flex items-center gap-1.5 font-bold text-amber-500">
                                        <span>{{ str_repeat('★', $item->review->rating) }}{{ str_repeat('☆', 5 - $item->review->rating) }}</span>
                                        <span class="text-[11px] text-pm-charcoal dark:text-gray-200">({{ $item->review->rating }}/5)</span>
                                    </div>
                                    <p class="mt-1 truncate italic text-pm-muted dark:text-gray-300">
                                        "{{ $item->review->comment }}"
                                    </p>
                                </div>
                                <button type="button"
                                        x-data
                                        x-on:click="$dispatch('open-modal', 'review-modal-{{ $item->id }}')"
                                        class="rounded-xl border border-pm-orange/30 bg-white px-3 py-1 text-xs font-bold text-pm-orange hover:bg-pm-orange hover:text-white transition-colors dark:bg-gray-700">
                                    Ubah Ulasan
                                </button>
                            @else
                                <div class="text-xs text-pm-muted dark:text-gray-400">
                                    Bagaimana kepuasan Anda dengan produk ini?
                                </div>
                                <button type="button"
                                        x-data
                                        x-on:click="$dispatch('open-modal', 'review-modal-{{ $item->id }}')"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-pm-orange px-3.5 py-1 text-xs font-bold text-white shadow-xs hover:bg-pm-orange-dk transition-colors">
                                    <span>⭐</span>
                                    <span>Nilai Produk</span>
                                </button>
                            @endif
                        </div>

                        {{-- Modal Review Per Item --}}
                        <x-modal name="review-modal-{{ $item->id }}" focusable>
                            <form method="POST" action="{{ route('orders.items.reviews.store', ['order' => $order, 'item' => $item]) }}" class="p-6 sm:p-7 text-pm-charcoal dark:text-gray-100">
                                @csrf
                                <div class="flex items-center justify-between pb-4 border-b border-pm-border dark:border-gray-800">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-pm-orange-lt text-pm-orange text-lg">
                                            ⭐
                                        </span>
                                        <div>
                                            <h3 class="font-bold text-base text-pm-charcoal dark:text-gray-100">Beri Ulasan Produk</h3>
                                            <p class="text-xs text-pm-muted truncate max-w-xs">{{ $item->product_name }}</p>
                                        </div>
                                    </div>
                                    <button type="button" @click="$dispatch('close')" class="text-pm-muted hover:text-pm-charcoal">✕</button>
                                </div>

                                <div class="mt-5 space-y-4">
                                    {{-- Star Rating Picker --}}
                                    <div x-data="{ rating: {{ $item->review?->rating ?? 5 }}, hoverRating: 0 }">
                                        <label class="block text-xs font-bold text-pm-charcoal dark:text-gray-200 mb-1">
                                            Kepuasan Anda <span class="text-red-500">*</span>
                                        </label>
                                        <input type="hidden" name="rating" :value="rating">
                                        <div class="flex items-center gap-2 py-1">
                                            <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                                <button type="button"
                                                        @click="rating = star"
                                                        @mouseenter="hoverRating = star"
                                                        @mouseleave="hoverRating = 0"
                                                        class="text-3xl transition-transform hover:scale-125 focus:outline-none cursor-pointer"
                                                        :class="(hoverRating || rating) >= star ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600'"
                                                        aria-label="Pilih bintang">
                                                    ★
                                                </button>
                                            </template>
                                            <span class="ml-2 text-xs font-bold text-pm-orange" x-text="rating + ' dari 5 Bintang'"></span>
                                        </div>
                                    </div>

                                    {{-- Review Comment --}}
                                    <div>
                                        <label class="block text-xs font-bold text-pm-charcoal dark:text-gray-200 mb-1">
                                            Ulasan &amp; Pengalaman <span class="text-red-500">*</span>
                                        </label>
                                        <textarea name="comment"
                                                  rows="4"
                                                  required
                                                  minlength="3"
                                                  maxlength="1000"
                                                  placeholder="Ceritakan pengalaman Anda dan anabul menggunakan produk ini..."
                                                  class="pm-input text-xs">{{ $item->review?->comment ?? '' }}</textarea>
                                        <p class="text-[11px] text-pm-muted mt-1">Ulasan Anda membantu pemilik anabul lainnya berbelanja.</p>
                                    </div>
                                </div>

                                <div class="mt-6 flex justify-end gap-3 border-t border-pm-border pt-4 dark:border-gray-800">
                                    <button type="button" class="pm-secondary !py-2 !px-4 text-xs font-semibold" x-on:click="$dispatch('close')">
                                        Batal
                                    </button>
                                    <button type="submit" class="pm-button !py-2 !px-5 text-xs font-bold shadow-xs">
                                        Simpan Ulasan
                                    </button>
                                </div>
                            </form>
                        </x-modal>
                    @endif
                </div>
            @endforeach
        </div>

        <dl class="mt-6 space-y-3 border-t border-pm-border pt-4 text-sm dark:border-gray-800">
            <div class="flex justify-between text-pm-muted dark:text-gray-400">
                <dt>Subtotal produk</dt>
                <dd class="font-bold text-pm-charcoal dark:text-gray-200">
                    Rp{{ number_format($order->subtotal, 2, ',', '.') }}
                </dd>
            </div>
            <div class="flex justify-between text-pm-muted dark:text-gray-400">
                <dt>Ongkos kirim ({{ $order->courier_name ?? 'Kurir' }})</dt>
                <dd class="font-bold text-pm-charcoal dark:text-gray-200">
                    Rp{{ number_format($order->shipping_cost, 2, ',', '.') }}
                </dd>
            </div>
            <div class="flex flex-wrap justify-between gap-2 border-t border-pm-border pt-3 text-base font-bold text-pm-charcoal dark:border-gray-800 dark:text-gray-100">
                <dt>Total transaksi</dt>
                <dd class="text-xl font-extrabold text-pm-orange dark:text-orange-400">
                    Rp{{ number_format($order->total_amount, 2, ',', '.') }}
                </dd>
            </div>
        </dl>
    </section>

    {{-- Right: Shipping Address & Payment Summary --}}
    <section class="pm-card min-w-0 p-6 sm:p-7 rounded-[2rem]">
        <h2 class="text-lg font-bold text-pm-charcoal dark:text-gray-100 pb-3 border-b border-pm-border/80 dark:border-gray-800">
            Alamat Pengiriman
        </h2>

        <div class="mt-5 rounded-2xl bg-pm-cream/50 p-4 text-sm dark:bg-gray-800/50">
            <p class="break-words font-bold text-pm-charcoal dark:text-gray-100">{{ $order->recipient_name }}</p>
            <p class="mt-0.5 text-xs text-pm-muted dark:text-gray-400">{{ $order->recipient_phone }}</p>
            <p class="mt-3 whitespace-pre-line break-words text-pm-charcoal dark:text-gray-200 leading-relaxed">{{ $order->shipping_address }}</p>
            <p class="mt-1 text-xs font-semibold text-pm-muted dark:text-gray-400">{{ $order->city }}, {{ $order->province }} {{ $order->postal_code }}</p>
            @if($order->notes)
                <div class="mt-3 border-t border-pm-border pt-2 text-xs text-pm-muted dark:border-gray-700 dark:text-gray-400">
                    <span class="font-bold text-pm-charcoal dark:text-gray-200">Catatan:</span> {{ $order->notes }}
                </div>
            @endif
        </div>

        <h3 class="mt-6 border-t border-pm-border pt-5 text-base font-bold text-pm-charcoal dark:border-gray-800 dark:text-gray-100">
            Status Pembayaran
        </h3>

        @if($order->payment)
            <dl class="mt-3 space-y-2.5 rounded-2xl border border-pm-border bg-white p-4 text-xs dark:border-gray-800 dark:bg-gray-850">
                <div class="flex justify-between items-center">
                    <dt class="text-pm-muted dark:text-gray-400">Metode Pembayaran</dt>
                    <dd class="font-bold text-pm-charcoal dark:text-gray-200 uppercase">{{ $order->payment->payment_type ? strtoupper(str_replace('_', ' ', $order->payment->payment_type)) : 'Midtrans' }}</dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-pm-muted dark:text-gray-400">Status Pembayaran</dt>
                    <dd>
                        <x-order-status :status="$order->payment_status" type="payment" />
                    </dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-pm-muted dark:text-gray-400">Waktu Pembayaran</dt>
                    <dd class="font-medium text-pm-charcoal dark:text-gray-200">
                        {{ $order->payment->paid_at ? $order->payment->paid_at->timezone('Asia/Jakarta')->format('d M Y H:i').' WIB' : 'Menunggu verifikasi' }}
                    </dd>
                </div>
            </dl>
            @if($order->payment->review_reason)
                <p class="mt-3 rounded-2xl bg-amber-50 border border-amber-200 p-3 text-xs font-semibold text-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                    Pembayaran memerlukan verifikasi server. Mohon tunggu atau gunakan periksa status.
                </p>
            @endif
        @else
            <div class="mt-3 rounded-2xl border border-dashed border-pm-border p-4 text-xs text-pm-muted dark:border-gray-800">
                Belum ada transaksi pembayaran.
                @if($order->canContinuePayment())
                    <a href="{{ route('payments.show', $order) }}" class="pm-link font-bold ml-1">Bayar sekarang →</a>
                @endif
            </div>
        @endif
    </section>
</div>
