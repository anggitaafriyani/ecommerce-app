@props(['status', 'type' => 'order'])
@php
    $labels = $type === 'payment' ? \App\Models\Order::PAYMENT_LABELS : \App\Models\Order::STATUS_LABELS;
    $color = match ($status) {
        'paid', 'success', 'completed' => 'bg-pm-green-lt text-pm-green border border-pm-green/20 dark:bg-green-900/40 dark:text-green-300 dark:border-green-800',
        'processing', 'shipped' => 'bg-blue-50 text-blue-800 border border-blue-200 dark:bg-blue-900/40 dark:text-blue-200 dark:border-blue-800',
        'failed', 'expired', 'cancelled', 'payment_review' => 'bg-rose-50 text-rose-800 border border-rose-200 dark:bg-rose-900/40 dark:text-rose-200 dark:border-rose-800',
        default => 'bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-900/40 dark:text-amber-200 dark:border-amber-800',
    };
    $dotColor = match ($status) {
        'paid', 'success', 'completed' => 'bg-pm-green',
        'processing', 'shipped' => 'bg-blue-600',
        'failed', 'expired', 'cancelled', 'payment_review' => 'bg-rose-600',
        default => 'bg-amber-500',
    };
@endphp
<span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $color }}">
    <span class="h-1.5 w-1.5 rounded-full {{ $dotColor }}" aria-hidden="true"></span>
    {{ $labels[$status] ?? 'Perlu pemeriksaan' }}
</span>
