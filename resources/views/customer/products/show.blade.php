<x-store-layout :title="$product->name">
    <div class="mx-auto max-w-7xl px-5 py-8 sm:px-8 sm:py-12">
        <nav aria-label="Jejak navigasi" class="mb-8 flex flex-wrap gap-2 text-sm text-gray-500"><a class="hover:underline" href="{{ route('home') }}">Beranda</a><span aria-hidden="true">/</span><a class="hover:underline" href="{{ route('products.index') }}">Produk</a><span aria-hidden="true">/</span><span class="break-words">{{ $product->name }}</span></nav>
        <article class="grid items-start gap-8 lg:grid-cols-2 lg:gap-14">
            <x-product-image :product="$product" class="rounded-3xl border border-[#E6E4DF] dark:border-gray-700" />
            <div>
                <a href="{{ route('products.index', ['category' => $product->category_id]) }}" class="pm-link text-xs uppercase tracking-widest">{{ $product->category->name }}</a>
                <h1 class="mt-4 break-words text-3xl font-semibold leading-tight tracking-tight sm:text-4xl">{{ $product->name }}</h1>
                <p class="mt-5 text-3xl font-semibold text-[#2F5D50] dark:text-green-300">Rp{{ number_format($product->price, 2, ',', '.') }}</p>
                <p class="mt-5"><span class="inline-flex rounded-full px-3 py-2 text-xs font-semibold {{ $product->stock > 0 ? 'bg-green-50 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ $product->stock > 0 ? 'Stok tersedia: '.number_format($product->stock, 0, ',', '.').' unit' : 'Stok habis' }}</span></p>
                <div class="mt-7 border-t border-[#E6E4DF] pt-6 dark:border-gray-700"><h2 class="font-semibold">Tentang produk</h2><p class="mt-3 whitespace-pre-line break-words text-sm leading-7 text-gray-600 dark:text-gray-300">{{ $product->description }}</p></div>
                <form class="pm-card mt-8 p-5" method="POST" action="{{ route('cart.store',$product) }}">@csrf
                    <label class="pm-label" for="quantity">Jumlah</label>
                    <input id="quantity" name="quantity" x-data x-on:change="$el.value = Math.min(Number($el.max), Math.max(1, Math.trunc(Number($el.value) || 1)))" type="number" class="pm-input max-w-28" min="1" max="{{ max(1, $product->stock) }}" step="1" value="{{ $product->stock > 0 ? 1 : '' }}" @disabled($product->stock === 0) aria-describedby="quantity-help">
                    <p id="quantity-help" class="mt-2 text-xs text-gray-500">{{ $product->stock > 0 ? 'Pilih 1 hingga '.$product->stock.' unit. ' : 'Jumlah tidak dapat dipilih karena stok habis.' }}</p>
                    @if($product->stock === 0 || (auth()->check() && auth()->user()->role === 'admin'))<button type="button" disabled class="pm-button mt-5 w-full">Tambah ke Keranjang</button><p class="mt-3 text-sm">Pembelian diperuntukkan bagi Pelanggan dengan stok tersedia.</p>
@elseif(auth()->guest())<a class="pm-button mt-5 w-full" href="{{ route('shop.login',$product->slug) }}">Tambah ke Keranjang</a><p class="mt-3 text-sm">Masuk sebagai Pelanggan untuk menambahkan produk.</p>
@else<button class="pm-button mt-5 w-full">Tambah ke Keranjang</button>@endif
                </form>
                <a class="pm-link mt-6 inline-block" href="{{ route('products.index') }}">← Kembali ke katalog</a>
            </div>
        </article>
    </div>
</x-store-layout>
