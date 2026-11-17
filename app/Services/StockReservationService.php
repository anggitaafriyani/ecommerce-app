<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockReservationService
{
    public function release(Order $order, bool $expired = false, bool $gatewayVerified = false): void
    {
        DB::transaction(function () use ($order, $expired, $gatewayVerified) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (! $gatewayVerified && $order->payment()->exists()) {
                throw ValidationException::withMessages(['order' => 'Rekonsiliasi Midtrans diperlukan sebelum melepas stok.']);
            }
            if (! $order->stock_reserved || $order->stock_released_at) {
                return;
            }
            if ($order->payment_status !== 'pending') {
                if ($expired) {
                    return;
                }
                throw ValidationException::withMessages(['order' => 'Pesanan ini tidak dapat dibatalkan.']);
            }
            if ($expired && (! $order->expires_at || $order->expires_at->isFuture())) {
                return;
            }
            $items = $order->items()->orderBy('product_id')->get();
            $products = Product::withTrashed()->whereIn('id', $items->pluck('product_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach ($items as $item) {
                $products->get($item->product_id)?->increment('stock', $item->quantity);
            }
            $order->update(['stock_reserved' => false, 'stock_released_at' => now(), 'payment_status' => $expired ? 'expired' : 'failed', 'order_status' => 'cancelled']);
        }, 3);
    }
}
