<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function show(Request $request, int $order): View
    {
        return view('customer.order', ['order' => Order::where('user_id', $request->user()->id)->with(['items', 'payment'])->findOrFail($order)]);
    }

    public function invoice(Request $request, int $order): View
    {
        return view('customer.invoice', ['order' => Order::where('user_id', $request->user()->id)->with(['items', 'user'])->findOrFail($order)]);
    }

    public function cancel(Request $request, int $order, PaymentService $reservations): RedirectResponse
    {
        $order = Order::where('user_id', $request->user()->id)->findOrFail($order);
        try {
            $reservations->cancel($order);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages(['payment' => $exception->getMessage()]);
        }

        return back()->with('status', 'Status pembatalan telah diperiksa. Lihat status pesanan dan pembayaran terbaru.');
    }
}
