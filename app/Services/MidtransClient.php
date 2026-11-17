<?php

namespace App\Services;

use App\Models\Order;
use App\Support\Money;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class MidtransClient
{
    public const SNAP = 'https://app.sandbox.midtrans.com/snap/v1/transactions';

    public const API = 'https://api.sandbox.midtrans.com/v2/';

    public function configured(): bool
    {
        return config('midtrans.environment') === 'sandbox'
            && $this->hasCredential(config('midtrans.server_key'))
            && $this->hasCredential(config('midtrans.client_key'));
    }

    /** Credential presence is not authentication; Midtrans validates keys at the fixed Sandbox endpoints. */
    private function hasCredential(mixed $key): bool
    {
        return is_string($key) && $key !== '' && ! preg_match('/[\s\x00-\x1F\x7F:]/', $key);
    }

    private function http(): PendingRequest
    {
        if (! $this->configured()) {
            throw new RuntimeException('Konfigurasi Midtrans Sandbox belum tersedia.');
        }

        return Http::withBasicAuth(config('midtrans.server_key'), '')->acceptJson()->asJson()->connectTimeout(3)->timeout(10);
    }

    public function payload(Order $order): array
    {
        $duration = (int) $order->created_at->diffInMinutes($order->expires_at);
        if ($duration < 5 || $duration > 10080 || Money::cents($order->total_amount) <= 0) {
            throw ValidationException::withMessages(['payment' => 'Batas waktu pembayaran harus 5 menit sampai 7 hari dan total harus positif.']);
        }
        $order->load('items');
        $items = [];
        foreach ($order->items as $item) {
            $items[] = ['id' => (string) $item->id, 'name' => Str::limit(str_replace('|', ' ', $item->product_name), 50, ''), 'price' => $this->rupiah($item->product_price), 'quantity' => $item->quantity];
        }
        $items[] = ['id' => 'shipping', 'name' => 'Ongkir simulasi', 'price' => $this->rupiah($order->shipping_cost), 'quantity' => 1];

        return ['transaction_details' => ['order_id' => $order->order_number, 'gross_amount' => $this->rupiah($order->total_amount)], 'item_details' => $items,
            'customer_details' => ['first_name' => $order->recipient_name, 'phone' => $order->recipient_phone],
            'expiry' => ['start_time' => $order->created_at->copy()->timezone('Asia/Jakarta')->format('Y-m-d H:i:s O'), 'unit' => 'minutes', 'duration' => $duration],
            'callbacks' => ['finish' => route('payments.show', $order)]];
    }

    private function rupiah(string $amount): int
    {
        $cents = Money::cents($amount);
        if ($cents % 100 !== 0) {
            throw ValidationException::withMessages(['payment' => 'Midtrans memerlukan harga rupiah bulat. Pesanan ini memiliki pecahan rupiah; hubungi Admin atau buat pesanan baru setelah harga diperbaiki.']);
        }

        return intdiv($cents, 100);
    }

    public function create(array $payload): string
    {
        try {
            $http = $this->http();
            if (config('midtrans.notification_url')) {
                $http = $http->withHeaders(['X-Override-Notification' => config('midtrans.notification_url')]);
            }
            $response = $http->post(self::SNAP, $payload);
            $token = $response->json('token');
            if (! $response->successful() || ! is_string($token) || strlen($token) > 200 || ! preg_match('/^[a-zA-Z0-9-]+$/', $token)) {
                throw new RuntimeException;
            }

            return $token;
        } catch (Throwable $e) {
            throw new RuntimeException('Pembuatan token belum dapat dipastikan. Periksa status atau hubungi pengelola.');
        }
    }

    public function status(string $orderNumber): ?array
    {
        try {
            $response = $this->http()->get(self::API.rawurlencode($orderNumber).'/status');
            if ((string) $response->json('status_code') === '404' && in_array($response->status(), [200, 404], true)) {
                return null;
            }
            if (! $response->successful() || ! is_array($response->json())) {
                throw new RuntimeException;
            }

            return $response->json();
        } catch (Throwable $e) {
            throw new RuntimeException('Status Midtrans belum dapat diverifikasi. Reservasi stok tetap dipertahankan.');
        }
    }

    public function terminate(string $orderNumber, bool $expired): void
    {
        try {
            $response = $this->http()->post(self::API.rawurlencode($orderNumber).($expired ? '/expire' : '/cancel'));
            if (! $response->successful()) {
                throw new RuntimeException;
            }
        } catch (Throwable $e) {
            throw new RuntimeException('Pembatalan Midtrans belum dapat dipastikan. Periksa status kembali.');
        }
    }

    public function cancelSession(string $token): void
    {
        try {
            $response = $this->http()->post(self::SNAP.'/'.rawurlencode($token).'/cancel');
            if (! $response->successful() || ! $response->json('canceled_at')) {
                throw new RuntimeException;
            }
        } catch (Throwable $e) {
            throw new RuntimeException('Sesi Snap belum terkonfirmasi ditutup. Reservasi stok dipertahankan.');
        }
    }
}
