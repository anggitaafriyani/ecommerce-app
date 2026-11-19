@props(['product'])
<article class="group relative flex min-w-0 flex-col justify-between overflow-hidden rounded-2xl border border-pm-border/80 bg-white p-4 shadow-xs transition-all duration-250 hover:-translate-y-1 hover:border-pm-orange/40 hover:shadow-hover dark:border-gray-700 dark:bg-gray-900">
    {{-- Top Section: Wishlist Heart & Image --}}
    <div class="relative">
        {{-- Wishlist / Heart decorative indicator --}}
        <button type="button"
                aria-label="Simpan ke favorit"
                class="absolute right-2 top-2 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-pm-orange-lt text-pm-orange transition-transform duration-200 hover:scale-110 active:scale-95 dark:bg-orange-950/60 dark:text-orange-300">
            <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
        </button>

        {{-- Product image --}}
        <a href="{{ route('products.show', $product->slug) }}"
           aria-label="Lihat detail produk {{ $product->name }}"
           class="relative block aspect-square w-full overflow-hidden rounded-xl bg-pm-cream/70 p-3 dark:bg-gray-800/80">
            <x-product-image :product="$product"
                class="h-full w-full object-contain transition-transform duration-500 group-hover:scale-105" />

            @if($product->stock === 0)
                <div class="absolute inset-0 flex items-center justify-center bg-white/70 backdrop-blur-[2px] dark:bg-gray-900/70">
                    <span class="rounded-full bg-pm-charcoal px-3.5 py-1 text-[11px] font-bold text-white shadow-xs">
                        Stok habis
                    </span>
                </div>
            @elseif($product->stock <= 5)
                <span class="absolute bottom-2 left-2 rounded-full bg-orange-100 px-2.5 py-0.5 text-[10px] font-bold text-orange-800 dark:bg-orange-900/60 dark:text-orange-200">
                    Sisa {{ $product->stock }}
                </span>
            @endif
        </a>
    </div>

    {{-- Product info --}}
    <div class="mt-3 flex flex-1 flex-col justify-between">
        <div>
            <p class="text-[11px] font-medium text-pm-muted">{{ $product->category->name }}</p>

            <h3 class="mt-1 line-clamp-2 text-sm font-bold text-pm-charcoal transition-colors group-hover:text-pm-orange dark:text-gray-100 dark:group-hover:text-orange-400">
                <a href="{{ route('products.show', $product->slug) }}">
                    {{ $product->name }}
                </a>
            </h3>

            {{-- Star Rating (matching reference image style) --}}
            <div class="mt-1.5 flex items-center gap-1.5 text-xs text-pm-muted">
                <div class="flex text-amber-400" aria-label="Rating 5 dari 5 bintang">
                    @for($i = 0; $i < 5; $i++)
                        <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
                <span class="text-[11px] font-medium text-pm-muted">(4.9)</span>
            </div>

            {{-- Price --}}
            <div class="mt-2.5 flex items-baseline gap-1">
                <span class="text-xs font-semibold text-pm-orange">Rp</span>
                <span class="text-lg font-bold text-pm-charcoal dark:text-gray-100">{{ number_format($product->price, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Actions --}}
        <div class="mt-4 flex flex-col gap-2">
            @if($product->stock > 0 && (!auth()->check() || auth()->user()->role === 'customer'))
                @guest
                    <a class="pm-button w-full justify-center !rounded-xl !py-2.5 text-xs font-bold" href="{{ route('shop.login', $product->slug) }}">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M1 1.75A.75.75 0 011.75 1h1.628a1.75 1.75 0 011.734 1.51L5.33 3h9.56a1.75 1.75 0 011.728 2.02l-.82 5a1.75 1.75 0 01-1.728 1.48H6.0l.294 1.5H15a.75.75 0 010 1.5H5.5a.75.75 0 01-.737-.616L3.001 3.49A.25.25 0 002.753 3H1.75A.75.75 0 011 2.25zM6 15.5a1.5 1.5 0 100 3 1.5 1.5 0 000-3zM14.5 15.5a1.5 1.5 0 100 3 1.5 1.5 0 000-3z"/>
                        </svg>
                        Tambah ke Keranjang
                    </a>
                @else
                    <form method="POST" action="{{ route('cart.store', $product) }}"
                          x-data="{ loading: false }"
                          @submit.prevent="
                              loading = true;
                              fetch($el.action, {
                                  method: 'POST',
                                  headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                                  body: new FormData($el)
                              }).then(r => r.json().catch(() => ({}))).then(data => {
                                  loading = false;
                                  $dispatch('cart-added', {message: data.message || 'Produk ditambahkan ke keranjang!'});
                              }).catch(() => { loading = false; $el.submit(); })
                          ">
                        @csrf
                        <input type="hidden" name="quantity" value="1">
                        <button class="pm-button w-full justify-center !rounded-xl !py-2.5 text-xs font-bold" type="submit" :disabled="loading" x-bind:class="{ 'opacity-70': loading }">
                            <span x-show="!loading" class="flex items-center gap-1.5">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M1 1.75A.75.75 0 011.75 1h1.628a1.75 1.75 0 011.734 1.51L5.33 3h9.56a1.75 1.75 0 011.728 2.02l-.82 5a1.75 1.75 0 01-1.728 1.48H6.0l.294 1.5H15a.75.75 0 010 1.5H5.5a.75.75 0 01-.737-.616L3.001 3.49A.25.25 0 002.753 3H1.75A.75.75 0 011 2.25z"/>
                                    <circle cx="6" cy="17" r="1.5"/><circle cx="14.5" cy="17" r="1.5"/>
                                </svg>
                                Tambah ke Keranjang
                            </span>
                            <span x-show="loading" class="flex items-center gap-1.5">
                                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                Menambahkan…
                            </span>
                        </button>
                    </form>
                @endguest
            @elseif($product->stock === 0)
                <button disabled class="pm-secondary w-full cursor-not-allowed justify-center !rounded-xl !py-2.5 text-xs opacity-60">
                    Stok habis
                </button>
            @endif
        </div>
    </div>
</article>
