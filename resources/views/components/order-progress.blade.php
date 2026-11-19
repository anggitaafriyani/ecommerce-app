@props(['order'])
<section class="pm-card p-6" aria-label="Perkembangan pesanan">
    <h2 class="text-lg font-bold text-pm-charcoal dark:text-gray-100">Perkembangan pesanan</h2>
    @if(in_array($order->order_status, ['awaiting_payment', 'processing', 'shipped', 'completed'], true))
        @php
            $steps = [
                'awaiting_payment' => 'Menunggu pembayaran',
                'processing' => 'Diproses',
                'shipped' => 'Dikirim',
                'completed' => 'Selesai'
            ];
            $current = array_search($order->order_status, array_keys($steps), true);
        @endphp
        <ol class="mt-6 grid gap-3 sm:grid-cols-4">
            @foreach($steps as $status => $label)
                @php
                    $isDone = $loop->index < $current;
                    $isCurrent = $order->order_status === $status;
                @endphp
                <li @if($isCurrent) aria-current="step" @endif
                    class="relative rounded-2xl border p-4 transition-all duration-200 {{ $isCurrent ? 'border-pm-green bg-pm-green text-white shadow-card' : ($isDone ? 'border-pm-green/30 bg-pm-green-lt text-pm-green dark:border-green-800 dark:bg-green-900/30 dark:text-green-300' : 'border-pm-border bg-white text-pm-muted dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400') }}">
                    <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wider {{ $isCurrent ? 'text-white/80' : ($isDone ? 'text-pm-green dark:text-green-300' : 'text-pm-muted') }}">
                        Langkah {{ $loop->iteration }}{{ $isCurrent ? ' · Saat ini' : '' }}
                    </span>
                    <span class="font-semibold text-sm">
                        {{ $label }}
                    </span>
                </li>
            @endforeach
        </ol>
    @else
        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-200">
            <p>{{ \App\Models\Order::STATUS_LABELS[$order->order_status] ?? 'Status memerlukan pemeriksaan pengelola.' }}. Pesanan tidak melanjutkan alur pengiriman saat ini.</p>
        </div>
    @endif
</section>
