<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderFulfillmentService
{
    public function update(
        Order $order,
        string $status,
        ?string $trackingNumber = null,
        ?string $shippingNotes = null,
        ?string $actorName = null
    ): void {
        DB::transaction(function () use ($order, $status, $trackingNumber, $shippingNotes, $actorName): void {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $order->setRelation('payment', $order->payment()->lockForUpdate()->first());
            if ($order->nextFulfillmentStatus() !== $status) {
                throw ValidationException::withMessages([
                    'order_status' => 'Perubahan status tidak diizinkan. Pembayaran harus terverifikasi dan urutan status harus Diproses → Dikirim → Selesai. Muat ulang halaman untuk melihat status terbaru.',
                ]);
            }

            $updateData = ['order_status' => $status];
            if ($status === 'shipped') {
                $updateData['shipped_at'] = now();
                if ($trackingNumber !== null) {
                    $updateData['tracking_number'] = $trackingNumber;
                }
                if ($shippingNotes !== null) {
                    $updateData['shipping_notes'] = $shippingNotes;
                }
            } elseif ($status === 'completed') {
                $updateData['completed_at'] = now();
            }

            $order->update($updateData);

            $notes = match ($status) {
                'shipped' => $trackingNumber ? "Paket dikirim dengan nomor resi: {$trackingNumber}" : 'Paket telah diserahkan ke kurir pengiriman.',
                'completed' => 'Pesanan selesai.',
                default => "Status diperbarui menjadi {$status}."
            };

            $order->recordHistory($status, 'admin', $actorName, $notes);
        }, 3);
    }
}
