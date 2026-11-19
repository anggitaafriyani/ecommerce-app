<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MidtransClient;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class PaymentController extends Controller
{
    private function owned(Request $request, int $id): Order
    {
        return Order::where('user_id', $request->user()->id)->with(['items', 'payment'])->findOrFail($id);
    }

    public function show(Request $request, int $order, MidtransClient $client, PaymentService $payments): View
    {
        $orderModel = $this->owned($request, $order);

        // Auto expire if past expiration time
        if ($orderModel->payment_status === 'pending' && $orderModel->stock_reserved && $orderModel->expires_at && $orderModel->expires_at->isPast()) {
            try {
                $payments->cancel($orderModel, true);
                $orderModel->refresh();
            } catch (\Throwable $e) {
                Log::error('Expiry failed: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
            }
        }

        return view('customer.payment', ['order' => $orderModel, 'configured' => $client->configured()]);
    }

    public function start(Request $request, int $order, PaymentService $payments): JsonResponse
    {
        return response()->json(['token' => $payments->start($this->owned($request, $order))]);
    }

    public function refresh(Request $request, int $order, PaymentService $payments): RedirectResponse
    {
        $order = $this->owned($request, $order);
        if (! $order->payment) {
            return redirect()->route('payments.show', $order)->with('status', 'Belum ada permintaan pembayaran. Gunakan Bayar Sekarang jika pesanan masih valid.');
        }
        try {
            $payments->sync($order);
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['payment' => $e->getMessage()]);
        }

        return redirect()->route('payments.show', $order)->with('status', 'Status pembayaran diperiksa melalui server.');
    }

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $query = Order::where('user_id', $request->user()->id)->with(['items', 'payment']);

        if ($status === 'unpaid') {
            $query->where('payment_status', 'pending')->where('order_status', 'awaiting_payment');
        } elseif ($status === 'processing') {
            $query->where('payment_status', 'paid')->where('order_status', 'processing');
        } elseif ($status === 'shipped') {
            $query->where('order_status', 'shipped');
        } elseif ($status === 'completed') {
            $query->where('order_status', 'completed');
        } elseif ($status === 'cancelled') {
            $query->whereIn('order_status', ['cancelled', 'expired']);
        }

        return view('customer.orders', [
            'orders' => $query->latest('id')->paginate(10)->withQueryString(),
            'currentStatus' => $status ?? 'all',
        ]);
    }
}
