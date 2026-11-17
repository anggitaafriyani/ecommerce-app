<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderFulfillmentService
{
    public function update(Order $order, string $status): void
    {
        DB::transaction(function () use ($order, $status): void {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $order->setRelation('payment', $order->payment()->lockForUpdate()->first());
            if ($order->nextFulfillmentStatus() !== $status) {
                throw ValidationException::withMessages(['order_status' => 'Perubahan status tidak diizinkan. Pembayaran harus terverifikasi dan urutan status harus Diproses → Dikirim → Selesai. Muat ulang halaman untuk melihat status terbaru.']);
            }
            $order->update(['order_status' => $status]);
        }, 3);
    }
}
