@props(['order'])
<section class="pm-card p-5" aria-label="Perkembangan pesanan">
    <h2 class="font-semibold">Perkembangan pesanan</h2>
    @if(in_array($order->order_status, ['awaiting_payment', 'processing', 'shipped', 'completed'], true))
        @php($steps = ['awaiting_payment' => 'Menunggu pembayaran', 'processing' => 'Diproses', 'shipped' => 'Dikirim', 'completed' => 'Selesai'])
        @php($current = array_search($order->order_status, array_keys($steps), true))
        <ol class="mt-4 grid gap-3 sm:grid-cols-4">
            @foreach($steps as $status => $label)
                <li @if($order->order_status === $status) aria-current="step" @endif class="rounded-xl border p-3 text-sm {{ $loop->index <= $current ? 'border-[#2F5D50] bg-[#2F5D50] text-white' : 'border-[#E6E4DF] text-gray-500 dark:border-gray-700 dark:text-gray-400' }}">
                    <span class="mb-2 block text-xs">Langkah {{ $loop->iteration }}{{ $order->order_status === $status ? ' · Saat ini' : '' }}</span>{{ $label }}
                </li>
            @endforeach
        </ol>
    @else
        <p class="mt-3 text-sm">{{ \App\Models\Order::STATUS_LABELS[$order->order_status] ?? 'Status memerlukan pemeriksaan pengelola.' }}. Pesanan tidak melanjutkan alur pengiriman saat ini.</p>
    @endif
</section>
