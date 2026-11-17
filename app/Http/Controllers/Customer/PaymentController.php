<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MidtransClient;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class PaymentController extends Controller
{
    private function owned(Request $request, int $id): Order
    {
        return Order::where('user_id', $request->user()->id)->with(['items', 'payment'])->findOrFail($id);
    }

    public function show(Request $request, int $order, MidtransClient $client): View
    {
        return view('customer.payment', ['order' => $this->owned($request, $order), 'configured' => $client->configured()]);
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
        return view('customer.orders', ['orders' => Order::where('user_id', $request->user()->id)->with(['items', 'payment'])->latest('id')->paginate(10)]);
    }
}
