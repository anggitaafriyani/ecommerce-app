<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CheckoutRequest;
use App\Services\CartService;
use App\Services\CheckoutService;
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
}
