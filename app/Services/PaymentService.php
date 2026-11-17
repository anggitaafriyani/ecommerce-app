<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PaymentService
{
    public function __construct(private MidtransClient $client, private StockReservationService $stock) {}

    public function start(Order $order): string
    {
        if (! $this->client->configured()) {
            throw ValidationException::withMessages(['payment' => 'Konfigurasi Midtrans Sandbox belum tersedia. Hubungi pengelola.']);
        }
        $token = DB::transaction(function () use ($order) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->payment_status !== 'pending' || ! $order->stock_reserved || ! $order->expires_at || $order->expires_at->lessThanOrEqualTo(now()->addMinute())) {
                throw ValidationException::withMessages(['payment' => 'Pesanan tidak dapat dibayar atau reservasi hampir kedaluwarsa.']);
            }
            $payment = $order->payment;
            if ($payment?->review_reason) {
                throw ValidationException::withMessages(['payment' => 'Pembayaran memerlukan pemeriksaan. Gunakan Periksa Status atau hubungi pengelola.']);
            }
            if ($payment?->snap_token) {
                return $payment->snap_token;
            }
            if ($payment?->attempted_at) {
                throw ValidationException::withMessages(['payment' => 'Permintaan pembayaran sudah dikirim. Periksa status; jangan membuat transaksi baru.']);
            }
            $payload = $this->client->payload($order);
            Payment::create(['order_id' => $order->id, 'midtrans_order_id' => $order->order_number, 'gross_amount' => $order->total_amount, 'attempted_at' => now()]);

            return $payload;
        });
        if (is_string($token)) {
            return $token;
        }
        // Persist the attempt before the remote call: timeout/crash must never cause a second create request.
        try {
            $token = $this->client->create($token);
        } catch (RuntimeException $e) {
            $this->review($order, 'creation_uncertain');
            throw ValidationException::withMessages(['payment' => $e->getMessage()]);
        }
        DB::transaction(function () use ($order, $token) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $order->payment()->update(['snap_token' => $token]);
            $order->update(['snap_token' => $token]);
        });

        return $token;
    }

    public function sync(Order $order, string $source = 'status'): void
    {
        try {
            DB::transaction(function () use ($order, $source) {
                $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                $payment = $order->payment;
                if (! $payment) {
                    return;
                }
                $data = $this->client->status($payment->midtrans_order_id);
                if ($data === null) {
                    if ($source === 'webhook') {
                        throw new RuntimeException('Transaksi belum ditemukan saat verifikasi notifikasi.');
                    }
                    $this->event($payment, $source, 'not_found', 'reservation_held');

                    return;
                }
                $this->apply($order, $payment, $data, $source);
            });
        } catch (RuntimeException $e) {
            $this->review($order, 'verification_unavailable');
            throw $e;
        }
    }

    public function cancel(Order $order, bool $expired = false): void
    {
        try {
            DB::transaction(function () use ($order, $expired) {
                $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                if ($expired && (! $order->expires_at || $order->expires_at->isFuture())) {
                    return;
                }
                if (in_array($order->payment_status, ['paid', 'success'], true)) {
                    if (! $expired) {
                        throw ValidationException::withMessages(['order' => 'Pesanan terbayar tidak dapat dibatalkan.']);
                    }

                    return;
                }
                if (! $order->stock_reserved) {
                    return;
                }
                $payment = $order->payment;
                if (! $payment) {
                    $this->stock->release($order, $expired);

                    return;
                }
                $data = $this->client->status($payment->midtrans_order_id);
                if ($data !== null) {
                    $this->apply($order, $payment, $data, 'reconcile');
                    $order->refresh();
                    if ($order->payment_status !== 'pending') {
                        if (! $expired) {
                            $this->cancellationConflict($order, $payment);
                        }

                        return;
                    }
                    if (($data['transaction_status'] ?? '') !== 'pending') {
                        throw new RuntimeException('Status pembayaran memerlukan pemeriksaan sebelum stok dilepas.');
                    }
                    $this->client->terminate($payment->midtrans_order_id, $expired);
                    $data = $this->client->status($payment->midtrans_order_id);
                    if ($data === null) {
                        throw new RuntimeException('Pembatalan belum dapat diverifikasi.');
                    }
                    $this->apply($order, $payment, $data, 'cancel');
                    $this->cancellationConflict($order->refresh(), $payment);
                    if ($order->fresh()->payment_status === 'pending') {
                        throw new RuntimeException('Pembayaran masih pending; stok tetap direservasi.');
                    }

                    return;
                }
                if (! $payment->snap_token) {
                    throw new RuntimeException('Permintaan token belum dapat dipastikan. Stok ditahan untuk pemeriksaan.');
                }
                $this->client->cancelSession($payment->snap_token);
                $data = $this->client->status($payment->midtrans_order_id);
                if ($data !== null) {
                    $this->apply($order, $payment, $data, 'cancel_session');
                    $this->cancellationConflict($order->refresh(), $payment);
                    if ($order->fresh()->payment_status === 'pending') {
                        throw new RuntimeException('Transaksi masih pending.');
                    }

                    return;
                }
                $this->release($order, $expired ? 'expired' : 'cancelled');
                $payment->update(['transaction_status' => $expired ? 'expire' : 'cancel', 'review_reason' => null, 'checked_at' => now()]);
                $this->event($payment, 'cancel_session', 'not_found', 'session_closed');
            });
        } catch (RuntimeException $e) {
            $this->review($order, 'cancellation_uncertain');
            throw $e;
        }
    }

    public function verifyNotification(array $data): Order
    {
        abort_unless($this->client->configured(), 503, 'Midtrans belum dikonfigurasi.');
        $signatureSecret = config('midtrans.signature_secret_key');
        abort_unless(is_string($signatureSecret) && $signatureSecret !== '' && ! preg_match('/[\s\x00-\x1F\x7F]/', $signatureSecret), 503, 'Verifikasi notifikasi Sandbox belum dikonfigurasi.');
        $expected = hash('sha512', $data['order_id'].$data['status_code'].$data['gross_amount'].$signatureSecret);
        abort_unless(hash_equals($expected, $data['signature_key']), 403, 'Signature tidak valid.');
        $order = Order::where('order_number', $data['order_id'])->whereHas('payment')->firstOrFail();
        $this->assertAmount($order, $data);

        return $order;
    }

    private function assertAmount(Order $order, array $data): void
    {
        $amount = $data['gross_amount'] ?? null;
        if (! is_string($amount) || ! preg_match('/^\d{1,10}(\.\d{1,2})?$/', $amount) || ($data['order_id'] ?? null) !== $order->order_number || Money::cents($amount) !== Money::cents($order->total_amount) || (isset($data['currency']) && $data['currency'] !== 'IDR')) {
            throw ValidationException::withMessages(['payment' => 'Identitas atau nominal pembayaran tidak sesuai pesanan.']);
        }
    }

    private function apply(Order $order, Payment $payment, array $data, string $source): void
    {
        $this->assertAmount($order, $data);
        $status = $data['transaction_status'] ?? '';
        if (! is_string($status) || ! is_string($data['status_code'] ?? null)) {
            throw new RuntimeException('Respons status Midtrans tidak valid.');
        }
        $code = (string) ($data['status_code'] ?? '');
        $fraud = $data['fraud_status'] ?? null;
        $transaction = $data['transaction_id'] ?? null;
        if (! is_string($transaction) || $transaction === '' || strlen($transaction) > 255 || ($payment->transaction_id && $payment->transaction_id !== $transaction)) {
            throw new RuntimeException('Identitas transaksi memerlukan pemeriksaan.');
        }
        $paid = in_array($status, ['settlement', 'capture'], true) && $code === '200' && ($fraud === null || $fraud === 'accept') && ($status !== 'capture' || $fraud === 'accept');
        $terminal = ['deny' => 'failed', 'failure' => 'failed', 'expire' => 'expired', 'cancel' => 'cancelled'];
        $next = $paid ? 'paid' : ($terminal[$status] ?? null);
        if ($next && $next !== 'paid' && ! in_array($code, ['200', '202'], true)) {
            $next = null;
        }
        $payment->update(['transaction_id' => $transaction, 'payment_type' => substr((string) ($data['payment_type'] ?? ''), 0, 100), 'transaction_status' => substr((string) $status, 0, 100), 'checked_at' => now()]);
        if (in_array($order->payment_status, ['paid', 'success'], true)) {
            if (($next && $next !== 'paid') || in_array($status, ['refund', 'partial_refund', 'chargeback', 'partial_chargeback'], true)) {
                $payment->update(['review_reason' => 'paid_status_conflict']);
                $order->update(['order_status' => 'payment_review']);
            }
            $this->event($payment, $source, $status, 'paid_preserved');

            return;
        }
        if ($paid) {
            $conflict = ! $order->stock_reserved || $order->stock_released_at || $order->payment_status !== 'pending';
            $order->update(['payment_status' => 'paid', 'order_status' => $conflict ? 'payment_review' : 'processing', 'stock_reserved' => false]);
            $payment->update(['paid_at' => $payment->paid_at ?? now(), 'review_reason' => $conflict ? 'paid_after_stock_release' : null]);
            $this->event($payment, $source, $status, $conflict ? 'manual_review' : 'paid');

            return;
        }
        if ($order->payment_status !== 'pending') {
            $this->event($payment, $source, $status, 'terminal_preserved');

            return;
        }
        if ($next) {
            $this->release($order, $next);
            $payment->update(['review_reason' => null]);
            $this->event($payment, $source, $status, $next);

            return;
        }
        $normal = $status === 'pending' && in_array($code, ['200', '201'], true) && ($fraud === null || $fraud === 'accept');
        $payment->update(['review_reason' => $normal ? null : 'gateway_status_review']);
        $this->event($payment, $source, $status, $normal ? 'pending' : 'manual_review');
    }

    private function release(Order $order, string $status): void
    {
        $this->stock->release($order, false, true);
        $order->refresh()->update(['payment_status' => $status, 'order_status' => 'cancelled']);
    }

    private function cancellationConflict(Order $order, Payment $payment): void
    {
        if (in_array($order->payment_status, ['paid', 'success'], true)) {
            $order->update(['order_status' => 'payment_review']);
            $payment->update(['review_reason' => 'payment_won_cancellation']);
            $this->event($payment, 'cancel', $payment->transaction_status, 'manual_review');
        }
    }

    private function review(Order $order, string $reason): void
    {
        DB::transaction(function () use ($order, $reason) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $payment = $order->payment;
            if ($payment && ! in_array($order->payment_status, ['paid', 'success'], true)) {
                $payment->update(['review_reason' => $reason]);
                $this->event($payment, 'error', 'unknown', $reason);
            }
        });
    }

    private function event(Payment $payment, string $source, string $status, string $result): void
    {
        DB::table('payment_events')->insert(['payment_id' => $payment->id, 'source' => $source, 'gateway_status' => substr($status, 0, 100), 'result' => $result, 'created_at' => now()]);
    }
}
