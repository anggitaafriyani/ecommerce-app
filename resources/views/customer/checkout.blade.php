<x-store-layout title="Checkout">
    <div class="mx-auto max-w-7xl px-5 py-8 sm:px-8 sm:py-12"
         x-data="{
             submitting: false,
             loadingRates: false,
             province: '{{ old('province', 'Jawa Barat') }}',
             city: '{{ old('city', 'Bandung') }}',
             courierCode: '{{ old('courier_code', $chosen_option['courier_code'] ?? 'jne') }}',
             serviceCode: '{{ old('courier_service', $chosen_option['service_code'] ?? 'reg') }}',
             shippingCost: {{ $shipping / 100 }},
             subtotal: {{ $subtotal / 100 }},
             availableOptions: @js($available_options ?? []),
             errorMessage: '',
             formatRupiah(val) {
                 return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val);
             },
             async fetchRates() {
                 if (!this.province || !this.city) return;
                 this.loadingRates = true;
                 this.errorMessage = '';
                 try {
                     const res = await fetch('{{ route('checkout.shipping-rates') }}', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                             'Accept': 'application/json'
                         },
                         body: JSON.stringify({
                             province: this.province,
                             city: this.city,
                             courier_code: this.courierCode,
                             courier_service: this.serviceCode
                         })
                     });
                     const data = await res.json();
                     if (res.ok && data.available_options && data.available_options.length > 0) {
                         this.availableOptions = data.available_options;
                         this.shippingCost = data.shipping_cost / 100;
                         if (data.chosen_option) {
                             this.courierCode = data.chosen_option.courier_code;
                             this.serviceCode = data.chosen_option.service_code;
                         }
                     } else {
                         this.availableOptions = [];
                         this.errorMessage = data.message || 'Layanan pengiriman ke wilayah tujuan belum tersedia.';
                     }
                 } catch (e) {
                     console.error(e);
                 } finally {
                     this.loadingRates = false;
                 }
             },
             selectOption(opt) {
                 this.courierCode = opt.courier_code;
                 this.serviceCode = opt.service_code;
                 this.shippingCost = opt.shipping_cost;
             }
         }">
        {{-- Breadcrumb / Back --}}
        <div class="mb-6">
            <a class="pm-link inline-flex items-center gap-1.5 text-sm font-semibold hover:text-pm-orange" href="{{ route('cart.index') }}">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 011 10z" clip-rule="evenodd"/>
                </svg>
                Kembali ke keranjang
            </a>
        </div>

        {{-- Header --}}
        <div class="mb-10">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-pm-orange-lt px-3.5 py-1 text-xs font-bold text-pm-orange">
                Langkah Terakhir Pembelian
            </span>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-pm-charcoal dark:text-gray-50 sm:text-4xl">
                Pengiriman & Checkout
            </h1>
            <p class="mt-2 text-sm text-pm-muted dark:text-gray-400">
                Pilih alamat tujuan, tentukan kurir ekspedisi, dan tinjau total pembayaran. Pembayaran Sandbox terverifikasi otomatis.
            </p>
        </div>

        <form method="POST" action="{{ route('checkout.store') }}" class="grid items-start gap-8 lg:grid-cols-12" x-on:submit="submitting = true">
            @csrf
            <input type="hidden" name="checkout_token" value="{{ $cart->checkout_token }}">

            {{-- Hidden inputs for selected courier --}}
            <input type="hidden" name="courier_code" x-bind:value="courierCode">
            <input type="hidden" name="courier_service" x-bind:value="serviceCode">

            {{-- Left Column: Shipping Address & Courier Selection (7 cols) --}}
            <div class="space-y-6 lg:col-span-7">
                {{-- Card 1: Shipping Address --}}
                <section class="pm-card p-6 sm:p-8 rounded-[2rem] border-pm-border/80">
                    <div class="flex items-center gap-3 border-b border-pm-border/80 pb-5 dark:border-gray-800">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-pm-orange text-xs font-bold text-white shadow-xs">1</span>
                        <h2 class="text-xl font-bold text-pm-charcoal dark:text-gray-100">Alamat Pengiriman</h2>
                    </div>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        {{-- Recipient Name --}}
                        <div>
                            <label class="pm-label" for="recipient_name">Nama penerima <span class="text-red-500">*</span></label>
                            <input class="pm-input @error('recipient_name') pm-input-error @enderror"
                                   id="recipient_name"
                                   name="recipient_name"
                                   type="text"
                                   value="{{ old('recipient_name', auth()->user()->name) }}"
                                   maxlength="100"
                                   required
                                   placeholder="Nama lengkap penerima"
                                   @error('recipient_name') aria-invalid="true" @enderror>
                            <x-input-error :messages="$errors->get('recipient_name')" class="mt-1.5" />
                        </div>

                        {{-- Recipient Phone --}}
                        <div>
                            <label class="pm-label" for="recipient_phone">Nomor telepon <span class="text-red-500">*</span></label>
                            <input class="pm-input @error('recipient_phone') pm-input-error @enderror"
                                   id="recipient_phone"
                                   name="recipient_phone"
                                   type="tel"
                                   value="{{ old('recipient_phone', auth()->user()->phone ?? '') }}"
                                   maxlength="21"
                                   required
                                   placeholder="Contoh: 081234567890"
                                   @error('recipient_phone') aria-invalid="true" @enderror>
                            <x-input-error :messages="$errors->get('recipient_phone')" class="mt-1.5" />
                        </div>

                        {{-- Shipping Address --}}
                        <div class="sm:col-span-2">
                            <label class="pm-label" for="shipping_address">Alamat lengkap <span class="text-red-500">*</span></label>
                            <textarea class="pm-input @error('shipping_address') pm-input-error @enderror"
                                      rows="3"
                                      id="shipping_address"
                                      name="shipping_address"
                                      maxlength="1000"
                                      required
                                      placeholder="Jalan, nomor rumah, RT/RW, kelurahan, kecamatan"
                                      @error('shipping_address') aria-invalid="true" @enderror>{{ old('shipping_address') }}</textarea>
                            <x-input-error :messages="$errors->get('shipping_address')" class="mt-1.5" />
                        </div>

                        {{-- Province --}}
                        <div>
                            <label class="pm-label" for="province">Provinsi <span class="text-red-500">*</span></label>
                            <input class="pm-input @error('province') pm-input-error @enderror"
                                   id="province"
                                   name="province"
                                   type="text"
                                   x-model="province"
                                   x-on:change="fetchRates()"
                                   maxlength="100"
                                   required
                                   placeholder="Contoh: Jawa Barat"
                                   @error('province') aria-invalid="true" @enderror>
                            <x-input-error :messages="$errors->get('province')" class="mt-1.5" />
                        </div>

                        {{-- City --}}
                        <div>
                            <label class="pm-label" for="city">Kota/Kabupaten <span class="text-red-500">*</span></label>
                            <input class="pm-input @error('city') pm-input-error @enderror"
                                   id="city"
                                   name="city"
                                   type="text"
                                   x-model="city"
                                   x-on:change="fetchRates()"
                                   maxlength="100"
                                   required
                                   placeholder="Contoh: Bandung"
                                   @error('city') aria-invalid="true" @enderror>
                            <x-input-error :messages="$errors->get('city')" class="mt-1.5" />
                        </div>

                        {{-- Postal Code --}}
                        <div>
                            <label class="pm-label" for="postal_code">Kode pos <span class="text-red-500">*</span></label>
                            <input class="pm-input @error('postal_code') pm-input-error @enderror"
                                   id="postal_code"
                                   name="postal_code"
                                   type="text"
                                   inputmode="numeric"
                                   pattern="[0-9]{5}"
                                   value="{{ old('postal_code') }}"
                                   maxlength="5"
                                   required
                                   placeholder="5 digit angka"
                                   @error('postal_code') aria-invalid="true" @enderror>
                            <x-input-error :messages="$errors->get('postal_code')" class="mt-1.5" />
                        </div>

                        {{-- Notes --}}
                        <div>
                            <label class="pm-label" for="notes">Catatan pengiriman (opsional)</label>
                            <input class="pm-input @error('notes') pm-input-error @enderror"
                                   id="notes"
                                   name="notes"
                                   type="text"
                                   value="{{ old('notes') }}"
                                   maxlength="1000"
                                   placeholder="Titip di resepsionis / satpam">
                            <x-input-error :messages="$errors->get('notes')" class="mt-1.5" />
                        </div>
                    </div>
                </section>

                {{-- Card 2: Courier & Service Selection --}}
                <section class="pm-card p-6 sm:p-8 rounded-[2rem] border-pm-border/80">
                    <div class="flex items-center justify-between border-b border-pm-border/80 pb-5 dark:border-gray-800">
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-pm-orange text-xs font-bold text-white shadow-xs">2</span>
                            <div>
                                <h2 class="text-xl font-bold text-pm-charcoal dark:text-gray-100">Pilihan Kurir & Ekspedisi</h2>
                                <p class="text-xs text-pm-muted dark:text-gray-400">Ongkir otomatis dihitung berdasarkan tujuan dan berat paket</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-pm-cream px-3 py-1 text-xs font-bold text-pm-charcoal dark:bg-gray-800 dark:text-gray-200">
                            Total: {{ number_format($total_weight, 0, ',', '.') }} g ({{ max(1, (int) ceil($total_weight / 1000)) }} kg)
                        </span>
                    </div>

                    {{-- Loading indicator --}}
                    <div x-show="loadingRates" class="py-4 text-center text-xs font-semibold text-pm-orange">
                        <span class="inline-block animate-spin mr-1.5">⏳</span> Menghitung tarif pengiriman...
                    </div>

                    {{-- Error message if region unsupported --}}
                    <div x-show="errorMessage" class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-xs font-medium text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300" x-text="errorMessage"></div>

                    {{-- Options list --}}
                    <div class="mt-5 space-y-3">
                        <template x-for="opt in availableOptions" :key="opt.courier_code + '_' + opt.service_code">
                            <label class="flex cursor-pointer items-center justify-between rounded-2xl border p-4 transition-all duration-200 hover:border-pm-orange/60"
                                   :class="(courierCode === opt.courier_code && serviceCode === opt.service_code) ? 'border-pm-orange bg-pm-orange-lt/40 shadow-xs' : 'border-pm-border bg-white dark:border-gray-800 dark:bg-gray-850'">
                                <div class="flex items-center gap-3.5">
                                    <input type="radio"
                                           name="shipping_service_radio"
                                           class="h-4 w-4 text-pm-orange focus:ring-pm-orange"
                                           :checked="courierCode === opt.courier_code && serviceCode === opt.service_code"
                                           x-on:change="selectOption(opt)">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-extrabold text-sm text-pm-charcoal dark:text-gray-100" x-text="opt.courier_name"></span>
                                            <span class="rounded-md bg-pm-orange-lt px-2 py-0.5 text-[10px] font-bold text-pm-orange uppercase tracking-wider" x-text="opt.service_name"></span>
                                        </div>
                                        <p class="mt-0.5 text-xs text-pm-muted dark:text-gray-400" x-show="opt.estimated_days">
                                            Estimasi tiba: <span x-text="opt.estimated_days"></span>
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-pm-muted dark:text-gray-400">Ongkos Kirim</span>
                                    <p class="text-sm font-extrabold text-pm-charcoal dark:text-gray-100" x-text="'Rp' + formatRupiah(opt.shipping_cost)"></p>
                                </div>
                            </label>
                        </template>
                    </div>
                </section>
            </div>

            {{-- Right Column: Order Summary Aside (5 cols) --}}
            <aside class="pm-card sticky top-24 p-6 sm:p-8 lg:col-span-5 rounded-[2rem] border-pm-border/80 shadow-card">
                <div class="flex items-center gap-3 border-b border-pm-border/80 pb-5 dark:border-gray-800">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-pm-orange text-xs font-bold text-white shadow-xs">3</span>
                    <h2 class="text-xl font-bold text-pm-charcoal dark:text-gray-100">Ringkasan Pesanan</h2>
                </div>

                {{-- Cart Items --}}
                <div class="mt-4 divide-y divide-pm-border/60 dark:divide-gray-800">
                    @foreach($cart->items as $item)
                        <div class="flex items-start justify-between gap-4 py-3.5 text-sm">
                            <div class="min-w-0">
                                <p class="break-words font-bold text-pm-charcoal dark:text-gray-200">
                                    {{ $item->product->name }}
                                </p>
                                <p class="mt-0.5 text-xs text-pm-muted dark:text-gray-400">
                                    {{ $item->quantity }} × Rp{{ number_format($item->product->price, 2, ',', '.') }}
                                    · <span class="text-pm-muted">{{ ($item->product->weight ?? 500) * $item->quantity }} g</span>
                                </p>
                            </div>
                            <span class="shrink-0 font-bold text-pm-charcoal dark:text-gray-200">
                                Rp{{ number_format($item->subtotal / 100, 2, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>

                {{-- Calculation Totals --}}
                <dl class="mt-5 space-y-3 border-t border-pm-border/80 pt-4 text-sm dark:border-gray-800">
                    <div class="flex justify-between text-pm-muted dark:text-gray-400">
                        <dt>Subtotal Produk</dt>
                        <dd class="font-bold text-pm-charcoal dark:text-gray-200">
                            Rp{{ number_format($subtotal / 100, 2, ',', '.') }}
                        </dd>
                    </div>
                    <div class="flex justify-between text-pm-muted dark:text-gray-400">
                        <dt>Ongkir (<span x-text="courierCode.toUpperCase() + ' ' + serviceCode"></span>)</dt>
                        <dd class="font-bold text-pm-charcoal dark:text-gray-200" x-text="'Rp' + formatRupiah(shippingCost)">
                            Rp{{ number_format($shipping / 100, 2, ',', '.') }}
                        </dd>
                    </div>
                    <div class="flex justify-between border-t border-pm-border/80 pt-4 text-base font-bold text-pm-charcoal dark:text-gray-100 dark:border-gray-800">
                        <dt>Total Pembayaran</dt>
                        <dd class="text-xl font-extrabold text-pm-orange dark:text-orange-400" x-text="'Rp' + formatRupiah(subtotal + shippingCost)">
                            Rp{{ number_format(($subtotal + $shipping) / 100, 2, ',', '.') }}
                        </dd>
                    </div>
                </dl>

                {{-- Reservation notice banner --}}
                <div class="mt-6 rounded-2xl bg-pm-orange-lt/80 p-4 text-xs leading-relaxed text-pm-charcoal dark:bg-orange-950/30 dark:text-orange-200">
                    <div class="flex gap-2.5">
                        <svg class="h-4 w-4 shrink-0 text-pm-orange dark:text-orange-300" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        <p>
                            Stok direservasi selama {{ config('pawmart.reservation_minutes') }} menit setelah pesanan dibuat. Belum ada pembayaran yang ditagihkan.
                        </p>
                    </div>
                </div>

                {{-- Submit button --}}
                <button class="pm-button-lg !rounded-full mt-6 w-full justify-center font-bold shadow-card hover:shadow-hover"
                        type="submit"
                        x-bind:disabled="submitting || availableOptions.length === 0"
                        x-text="submitting ? 'Menyimpan pesanan…' : 'Lanjut ke Pembayaran'">
                    Lanjut ke Pembayaran
                </button>
            </aside>
        </form>
    </div>
</x-store-layout>
