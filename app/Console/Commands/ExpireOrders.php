<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ExpireOrders extends Command
{
    protected $signature = 'orders:expire';

    protected $description = 'Rekonsiliasi pembayaran sebelum melepaskan reservasi kedaluwarsa';

    public function handle(PaymentService $payments): int
    {
        $held = 0;
        Order::where('payment_status', 'pending')->where('stock_reserved', true)->where('expires_at', '<=', now())->eachById(function (Order $order) use ($payments, &$held) {
            try {
                $payments->cancel($order, true);
            } catch (RuntimeException|ValidationException $e) {
                $held++;
                $this->warn('Pesanan '.$order->order_number.': reservasi ditahan untuk verifikasi.');
            }
        });
        $this->info('Rekonsiliasi selesai. Pesanan memerlukan pemeriksaan: '.$held);

        return self::SUCCESS;
    }
}
