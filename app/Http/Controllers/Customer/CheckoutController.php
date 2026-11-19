<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CheckoutRequest;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(Request $request, CartService $carts): View|RedirectResponse
    {
        $summary = $carts->summary($carts->cart($request->user()));
        if (! $summary['available']) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Periksa isi keranjang dan ketersediaan stok sebelum checkout.']);
        }

        return view('customer.checkout', $summary);
    }

    public function store(CheckoutRequest $request, CheckoutService $checkout): RedirectResponse
    {
        $order = $checkout->place($request->user(), $request->validated());

        return redirect()->route('payments.show', $order)->with('status', 'Pesanan tersimpan. Lanjutkan pembayaran melalui Midtrans Sandbox.');
    }

    public function shippingRates(Request $request, CartService $carts): JsonResponse
    {
        $validated = $request->validate([
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'courier_code' => ['nullable', 'string', 'max:50'],
            'courier_service' => ['nullable', 'string', 'max:50'],
        ]);

        $summary = $carts->summary(
            $carts->cart($request->user()),
            $validated['province'],
            $validated['city'],
            $validated['courier_code'] ?? null,
            $validated['courier_service'] ?? null
        );

        return response()->json([
            'available_options' => $summary['available_options'],
            'chosen_option' => $summary['chosen_option'],
            'shipping_cost' => $summary['shipping'],
            'total_weight' => $summary['total_weight'],
            'subtotal' => $summary['subtotal'],
            'total' => $summary['subtotal'] + $summary['shipping'],
        ]);
    }
}
