<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public const PAYMENT_LABELS = ['pending' => 'Menunggu pembayaran', 'paid' => 'Berhasil', 'success' => 'Berhasil', 'expired' => 'Kedaluwarsa', 'failed' => 'Gagal', 'cancelled' => 'Dibatalkan'];

    public const STATUS_LABELS = ['awaiting_payment' => 'Menunggu pembayaran', 'processing' => 'Diproses', 'shipped' => 'Dikirim', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan', 'payment_review' => 'Memerlukan pemeriksaan'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['subtotal' => 'decimal:2', 'shipping_cost' => 'decimal:2', 'total_amount' => 'decimal:2', 'stock_reserved' => 'boolean', 'stock_released_at' => 'datetime', 'expires_at' => 'datetime'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function nextFulfillmentStatus(): ?string
    {
        $payment = $this->payment;
        if ($this->payment_status !== 'paid' || $this->stock_reserved || $this->stock_released_at || ! $payment?->paid_at || $payment->review_reason || $payment->gross_amount !== $this->total_amount || ! in_array($payment->transaction_status, ['settlement', 'capture'], true)) {
            return null;
        }

        return match ($this->order_status) {
            'processing' => 'shipped',
            'shipped' => 'completed',
            default => null,
        };
    }

    public function canContinuePayment(): bool
    {
        return $this->payment_status === 'pending' && $this->stock_reserved && $this->expires_at?->greaterThan(now()->addMinute()) && ! $this->payment?->review_reason;
    }
}
