<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CartQuantityRequest;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $carts) {}

    public function index(Request $request): View
    {
        return view('customer.cart', $this->carts->summary($this->carts->cart($request->user())));
    }

    public function store(CartQuantityRequest $request, int $product): RedirectResponse
    {
        $this->carts->change($request->user(), $product, (int) $request->validated('quantity'), true);

        return redirect()->route('cart.index')->with('status', 'Produk ditambahkan ke keranjang.');
    }

    public function update(CartQuantityRequest $request, int $product): RedirectResponse
    {
        $this->carts->change($request->user(), $product, (int) $request->validated('quantity'));

        return back()->with('status', 'Jumlah produk diperbarui.');
    }

    public function destroy(Request $request, int $product): RedirectResponse
    {
        $this->carts->change($request->user(), $product, 0);

        return back()->with('status', 'Produk dihapus dari keranjang.');
    }

    public function login(Request $request, string $slug): RedirectResponse
    {
        $request->session()->put('url.intended', route('products.show', $slug));

        return redirect()->route('login');
    }
}
