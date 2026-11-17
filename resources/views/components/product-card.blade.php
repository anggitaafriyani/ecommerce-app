@props(['product'])
<article class="group flex min-w-0 flex-col overflow-hidden rounded-2xl border border-[#E6E4DF] bg-white transition hover:shadow-md dark:border-gray-700 dark:bg-gray-900">
    <a href="{{ route('products.show', $product->slug) }}" aria-label="Lihat {{ $product->name }}" class="relative block">
        <x-product-image :product="$product" />
        @if($product->stock === 0)<span class="absolute left-3 top-3 rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-700 shadow-sm">Stok habis</span>@endif
    </a>
    <div class="flex flex-1 flex-col p-4 sm:p-5">
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $product->category->name }}</p>
        <h3 class="mt-2 break-words text-base font-semibold leading-6"><a href="{{ route('products.show', $product->slug) }}" class="hover:underline">{{ $product->name }}</a></h3>
        <p class="mt-3 font-semibold text-[#2F5D50] dark:text-green-300">Rp{{ number_format($product->price, 2, ',', '.') }}</p>
        <a href="{{ route('products.show', $product->slug) }}" class="pm-secondary mt-5 w-full">Lihat detail<span class="sr-only"> {{ $product->name }}</span></a>
        @if($product->stock > 0 && (!auth()->check() || auth()->user()->role === 'customer'))
            @guest<a class="pm-button mt-2" href="{{ route('shop.login',$product->slug) }}">Tambah ke Keranjang</a>@else
            <form class="mt-2" method="POST" action="{{ route('cart.store',$product) }}">@csrf<input type="hidden" name="quantity" value="1"><button class="pm-button w-full">Tambah ke Keranjang</button></form>
            @endguest
        @endif
    </div>
</article>
