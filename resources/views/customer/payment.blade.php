<x-store-layout title="Pembayaran pesanan">
 <div class="mx-auto max-w-5xl px-5 py-10 sm:px-8">
  <a class="pm-link" href="{{ route('orders.index') }}">← Kembali ke Riwayat Pesanan</a>
  <p class="mt-8 text-sm uppercase tracking-widest text-[#2F5D50] dark:text-green-300">Midtrans Sandbox</p>
  <h1 class="mt-3 text-3xl font-semibold">Pembayaran pesanan</h1>
  <p class="mt-3 break-all font-medium">{{ $order->order_number }}</p><p class="mt-2 text-sm text-gray-500">{{ $order->created_at->format('d M Y H:i') }}</p>
  @php($labels = ['pending'=>'Menunggu pembayaran','paid'=>'Pembayaran berhasil','success'=>'Pembayaran berhasil','failed'=>'Pembayaran gagal','expired'=>'Pembayaran kedaluwarsa','cancelled'=>'Pembayaran dibatalkan'])
  <section class="mt-6 rounded-2xl bg-[#F2D6C9]/50 p-5" aria-live="polite">
   <h2 class="font-semibold">{{ $labels[$order->payment_status] ?? $order->payment_status }}</h2>
   <p class="mt-2 text-sm">Status proses: {{ \App\Models\Order::STATUS_LABELS[$order->order_status] ?? $order->order_status }}.</p>
   @if($order->payment?->review_reason || $order->order_status === 'payment_review')<p class="mt-3 font-semibold">Pembayaran memerlukan pemeriksaan pengelola. Jangan membayar ulang. Pemrosesan pesanan menunggu rekonsiliasi.</p>@endif
   @if($order->stock_reserved)<p class="mt-2 text-sm">Batas reservasi: {{ $order->expires_at?->format('d M Y H:i') }}. Stok hanya dilepas setelah pembayaran dipastikan tidak dapat dilanjutkan.</p>@endif
   <p class="mt-2 text-sm">Mode pengujian Sandbox, bukan transaksi uang sungguhan. Status diperbarui dari verifikasi server.</p>
  </section>
  <section class="pm-card mt-6 p-6"><h2 class="text-xl font-semibold">Ringkasan pembayaran</h2>
   @foreach($order->items as $item)<div class="flex flex-wrap justify-between gap-3 border-b py-4 dark:border-gray-700"><div><p class="break-words font-medium">{{ $item->product_name }}</p><p class="mt-1 text-sm text-gray-500">{{ $item->quantity }} × Rp{{ number_format($item->product_price,2,',','.') }}</p></div><span>Rp{{ number_format($item->subtotal,2,',','.') }}</span></div>@endforeach
   <div class="mt-5 flex justify-between gap-4"><span>Ongkir simulasi</span><span>Rp{{ number_format($order->shipping_cost,2,',','.') }}</span></div>
   <div class="mt-5 flex flex-wrap justify-between gap-4 text-xl font-semibold"><span>Total pembayaran</span><span>Rp{{ number_format($order->total_amount,2,',','.') }}</span></div>
  </section>
  <p id="payment-message" class="mt-5 text-sm text-red-700 dark:text-red-300" role="alert"></p>
  @if(!$configured)<p class="mt-5 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">Pembayaran belum tersedia: kredensial Midtrans Sandbox belum dikonfigurasi. Hubungi pengelola. Pesanan belum dibayar.</p>@endif
  @if($order->payment_status === 'pending' && $order->expires_at?->lessThanOrEqualTo(now()->addMinute()))<p class="mt-4 text-sm">Batas reservasi hampir habis atau sudah terlewati. Pembayaran baru ditutup; periksa status untuk pembayaran yang sudah dilakukan.</p>@endif
  <div class="mt-6 flex flex-wrap gap-3">
   @if($configured && $order->payment_status === 'pending' && $order->stock_reserved && $order->expires_at?->greaterThan(now()->addMinute()) && !$order->payment?->review_reason)
    <button type="button" class="pm-button" id="pay-now" data-url="{{ route('payments.start',$order) }}">Bayar Sekarang</button>
   @endif
   @if($configured)
    <form id="payment-status-form" method="POST" action="{{ route('payments.refresh',$order) }}">@csrf<button class="pm-secondary">Periksa Status Pembayaran</button></form>

   @endif
   <a class="pm-secondary" href="{{ route('orders.show',$order) }}">Detail pesanan &amp; alamat</a>
   @if($order->payment_status === 'pending' && $order->stock_reserved)<form method="POST" action="{{ route('orders.cancel',$order) }}">@csrf<button class="pm-secondary">Batalkan pesanan</button></form>@endif
  </div>
  @if($configured)
   <form id="payment-callback-form" method="POST" action="{{ route('payments.refresh',$order) }}" hidden>@csrf</form>
   <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}" defer></script>
  @endif
 </div>
</x-store-layout>
