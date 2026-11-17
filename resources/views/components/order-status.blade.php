@props(['status', 'type' => 'order'])
@php
    $labels = $type === 'payment' ? \App\Models\Order::PAYMENT_LABELS : \App\Models\Order::STATUS_LABELS;
    $color = match ($status) {
        'paid', 'success', 'completed' => 'bg-green-100 text-green-900 dark:bg-green-900 dark:text-green-100',
        'processing', 'shipped' => 'bg-blue-100 text-blue-900 dark:bg-blue-900 dark:text-blue-100',
        'failed', 'expired', 'cancelled', 'payment_review' => 'bg-rose-100 text-rose-900 dark:bg-rose-900 dark:text-rose-100',
        default => 'bg-amber-100 text-amber-900 dark:bg-amber-900 dark:text-amber-100',
    };
@endphp
<span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $color }}">{{ $labels[$status] ?? 'Perlu pemeriksaan' }}</span>
