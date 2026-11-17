<x-store-layout title="Keranjang belanja">
 <div class="mx-auto max-w-7xl px-5 py-10 sm:px-8">
  <p class="text-sm font-semibold uppercase tracking-widest text-[#2F5D50] dark:text-green-300">Pilihan untuk sahabat kecil</p><h1 class="mt-3 text-3xl font-semibold">Keranjang belanja</h1>
  @if($cart->items->isEmpty())
   <div class="pm-card mt-8 p-10 text-center"><h2 class="text-xl font-semibold">Keranjang masih kosong</h2><p class="mt-3 text-gray-500">Temukan kebutuhan favorit hewan kesayangan Anda.</p><a class="pm-button mt-6" href="{{ route('products.index') }}">Jelajahi produk</a></div>
  @else
   <div class="mt-8 grid items-start gap-6 lg:grid-cols-3"><div class="space-y-4 lg:col-span-2">
    @foreach($cart->items as $item)
     <article class="pm-card flex flex-wrap gap-5 p-5">
      @if($item->product)<x-product-image :product="$item->product" class="w-24 shrink-0 rounded-xl" />@endif
      <div class="min-w-0 flex-1"><h2 class="break-words font-semibold">{{ $item->product?->name ?? 'Produk tidak tersedia' }}</h2><p class="mt-2 text-sm">Rp{{ number_format($item->product?->price ?? 0,2,',','.') }}</p>
       @if(!$item->product || $item->product->trashed() || !$item->product->is_active)<p class="mt-2 text-sm text-red-700 dark:text-red-300">Produk tidak tersedia. Hapus dari keranjang.</p>
       @elseif($item->quantity > $item->product->stock)<p class="mt-2 text-sm text-red-700 dark:text-red-300">Stok tidak mencukupi. Tersedia {{ $item->product->stock }} unit.</p>@endif
       <div class="mt-4 flex flex-wrap items-end gap-3"><form method="POST" action="{{ route('cart.update',$item->product_id) }}" class="flex flex-wrap items-end gap-2">@csrf @method('PATCH')<div><label class="pm-label" for="qty-{{ $item->id }}">Jumlah</label><input class="pm-input w-24" id="qty-{{ $item->id }}" name="quantity" type="number" min="1" max="{{ max(1,$item->product?->stock ?? 1) }}" value="{{ $item->quantity }}" required></div><button class="pm-secondary">Perbarui</button></form>
       <form method="POST" action="{{ route('cart.destroy',$item->product_id) }}">@csrf @method('DELETE')<button class="pm-link text-red-700 dark:text-red-300">Hapus<span class="sr-only"> {{ $item->product?->name }}</span></button></form></div>
      </div><p class="w-full text-right font-semibold sm:w-auto">Rp{{ number_format($item->subtotal/100,2,',','.') }}</p>
     </article>
    @endforeach
   </div><aside class="pm-card p-6"><h2 class="text-xl font-semibold">Ringkasan keranjang</h2><div class="mt-5 flex justify-between gap-3"><span>Subtotal</span><strong>Rp{{ number_format($subtotal/100,2,',','.') }}</strong></div><p class="mt-3 text-sm text-gray-500">Ongkir simulasi Rp{{ number_format($shipping/100,0,',','.') }} ditambahkan saat checkout. Harga dan stok diperiksa kembali.</p>
    @if($available)<a class="pm-button mt-6 w-full" href="{{ route('checkout.create') }}">Lanjut checkout</a>@else<p class="mt-6 text-sm text-red-700 dark:text-red-300">Perbarui atau hapus produk yang tidak tersedia untuk melanjutkan.</p>@endif
    <a class="pm-link mt-4 block text-center" href="{{ route('products.index') }}">Lanjut belanja</a>
   </aside></div>
  @endif
 </div>
</x-store-layout>