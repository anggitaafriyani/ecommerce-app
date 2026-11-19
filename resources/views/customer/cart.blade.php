<x-store-layout title="Keranjang belanja">
    <div class="mx-auto max-w-7xl px-5 py-10 sm:px-8 sm:py-14">
        {{-- Header --}}
        <div class="mb-8">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-pm-orange-lt px-3.5 py-1 text-xs font-bold text-pm-orange">
                Keranjang Belanja Sahabat Bulu
            </span>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-pm-charcoal dark:text-gray-50 sm:text-4xl">
                Keranjang Belanja
            </h1>
        </div>

        @if($cart->items->isEmpty())
            {{-- Empty State --}}
            <div class="pm-card flex flex-col items-center px-6 py-16 text-center sm:py-24 rounded-[2.5rem]">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-pm-orange-lt text-pm-orange">
                    <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.04 0 .75.75 0 011.04 0zm12.75 0a.75.75 0 11-1.04 0 .75.75 0 011.04 0z" />
                    </svg>
                </div>
                <h2 class="mt-6 text-2xl font-bold text-pm-charcoal dark:text-gray-100">Keranjang masih kosong</h2>
                <p class="mt-2.5 max-w-sm text-sm text-pm-muted dark:text-gray-400">
                    Temukan kebutuhan favorit hewan kesayangan Anda di katalog kami.
                </p>
                <a class="pm-button !rounded-full mt-8 !px-8 font-bold" href="{{ route('products.index') }}">
                    Jelajahi Produk
                </a>
            </div>
        @else
            {{-- Cart Layout --}}
            <div class="grid items-start gap-8 lg:grid-cols-3">
                {{-- Cart Items List (2 cols) --}}
                <div class="space-y-4 lg:col-span-2">
                    @foreach($cart->items as $item)
                        <article class="pm-card flex flex-col gap-5 p-5 transition-all duration-250 sm:flex-row sm:items-center sm:p-6 rounded-3xl border-pm-border/80">
                            {{-- Product Thumbnail --}}
                            @if($item->product)
                                <a href="{{ route('products.show', $item->product->slug) }}" class="h-24 w-24 shrink-0 overflow-hidden rounded-2xl bg-pm-cream/70 p-2 dark:bg-gray-800">
                                    <x-product-image :product="$item->product" class="h-full w-full object-contain" />
                                </a>
                            @endif

                            {{-- Product Information --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <h2 class="break-words font-bold text-pm-charcoal dark:text-gray-100">
                                        @if($item->product)
                                            <a href="{{ route('products.show', $item->product->slug) }}" class="hover:text-pm-orange transition-colors">
                                                {{ $item->product->name }}
                                            </a>
                                        @else
                                            Produk tidak tersedia
                                        @endif
                                    </h2>
                                    <p class="font-extrabold text-pm-orange sm:hidden">
                                        Rp{{ number_format($item->subtotal / 100, 2, ',', '.') }}
                                    </p>
                                </div>

                                <p class="mt-1 text-xs text-pm-muted dark:text-gray-400">
                                    Harga satuan: Rp{{ number_format($item->product?->price ?? 0, 2, ',', '.') }}
                                </p>

                                {{-- Warnings --}}
                                @if(!$item->product || $item->product->trashed() || !$item->product->is_active)
                                    <p class="mt-2 text-xs font-bold text-red-700 dark:text-red-300">
                                        Produk tidak tersedia. Hapus dari keranjang.
                                    </p>
                                @elseif($item->quantity > $item->product->stock)
                                    <p class="mt-2 text-xs font-bold text-red-700 dark:text-red-300">
                                        Stok tidak mencukupi. Tersedia {{ $item->product->stock }} unit.
                                    </p>
                                @endif

                                {{-- Quantity & Delete Actions --}}
                                <div class="mt-3 flex flex-wrap items-center gap-3">
                                    <form method="POST" action="{{ route('cart.update', $item->product_id) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <div class="flex items-center gap-1.5">
                                            <label class="sr-only" for="qty-{{ $item->id }}">Jumlah</label>
                                            <input class="pm-input !rounded-full !w-16 !py-1 text-center font-bold text-xs"
                                                   id="qty-{{ $item->id }}"
                                                   name="quantity"
                                                   type="number"
                                                   min="1"
                                                   max="{{ max(1, $item->product?->stock ?? 1) }}"
                                                   value="{{ $item->quantity }}"
                                                   required>
                                        </div>
                                        <button class="pm-secondary !rounded-full !py-1.5 !px-3 text-xs font-bold" type="submit">
                                            Perbarui
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('cart.destroy', $item->product_id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="pm-link text-xs text-red-600 hover:text-red-800 font-semibold" type="submit">
                                            Hapus<span class="sr-only"> {{ $item->product?->name }}</span>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Desktop Subtotal --}}
                            <div class="hidden shrink-0 text-right sm:block">
                                <span class="text-xs text-pm-muted">Subtotal</span>
                                <p class="text-lg font-extrabold text-pm-charcoal dark:text-gray-100">
                                    Rp{{ number_format($item->subtotal / 100, 2, ',', '.') }}
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{-- Summary Aside --}}
                <aside class="pm-card sticky top-24 p-6 rounded-[2rem] border-pm-border/80 dark:border-gray-800 dark:bg-gray-900 shadow-card">
                    <h2 class="text-xl font-extrabold text-pm-charcoal dark:text-gray-100">Ringkasan keranjang</h2>

                    <div class="mt-6 space-y-3.5 border-b border-pm-border/80 pb-5 dark:border-gray-800">
                        <div class="flex justify-between text-sm">
                            <span class="text-pm-muted dark:text-gray-400">Subtotal</span>
                            <strong class="font-extrabold text-pm-charcoal dark:text-gray-100 text-base">
                                Rp{{ number_format($subtotal / 100, 2, ',', '.') }}
                            </strong>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl bg-pm-orange-lt/60 p-3.5 text-xs text-pm-muted dark:bg-gray-800 dark:text-gray-400">
                        <div class="flex gap-2">
                            <svg class="h-4 w-4 shrink-0 text-pm-orange" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/>
                            </svg>
                            <p>Ongkir simulasi Rp{{ number_format($shipping / 100, 0, ',', '.') }} ditambahkan saat checkout. Harga dan stok diperiksa kembali.</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        @if($available)
                            <a class="pm-button !rounded-full w-full justify-center !py-3.5 text-sm font-bold shadow-card hover:shadow-hover" href="{{ route('checkout.create') }}">
                                Lanjut Checkout
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                        @else
                            <p class="text-center text-xs font-bold text-red-700 dark:text-red-300">
                                Perbarui atau hapus produk yang tidak tersedia untuk melanjutkan.
                            </p>
                        @endif

                        <a class="pm-link mt-4 block text-center text-xs font-bold" href="{{ route('products.index') }}">
                            Lanjut belanja
                        </a>
                    </div>
                </aside>
            </div>
        @endif
    </div>
</x-store-layout>
