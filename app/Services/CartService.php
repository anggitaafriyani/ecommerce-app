<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function cart(User $user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id], ['checkout_token' => (string) Str::uuid()]);
    }

    public function change(User $user, int $productId, int $quantity, bool $add = false): void
    {
        $cart = $this->cart($user);
        DB::transaction(function () use ($cart, $productId, $quantity, $add) {
            $cart = Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();
            $item = $cart->items()->where('product_id', $productId)->first();
            if (! $add) {
                abort_unless($item, 404);
            }
            if ($quantity === 0) {
                $item->delete();
            } else {
                $product = Product::whereKey($productId)->lockForUpdate()->first();
                $quantity += $add ? ($item?->quantity ?? 0) : 0;
                $this->validateProduct($product, $quantity);
                $cart->items()->updateOrCreate(['product_id' => $productId], ['quantity' => $quantity]);
            }
            $cart->update(['checkout_token' => (string) Str::uuid()]);
        }, 3);
    }

    public function validateProduct(?Product $product, int $quantity): void
    {
        if (! $product || $product->trashed() || ! $product->is_active) {
            throw ValidationException::withMessages(['cart' => 'Produk tidak tersedia. Hapus produk tersebut dari keranjang.']);
        }
        if ($quantity < 1 || $quantity > $product->stock) {
            throw ValidationException::withMessages(['cart' => 'Stok '.$product->name.' tidak mencukupi (tersedia '.$product->stock.').']);
        }
    }

    public function summary(Cart $cart): array
    {
        $cart->load('items.product');
        $subtotal = 0;
        $available = $cart->items->isNotEmpty();
        foreach ($cart->items as $item) {
            $item->subtotal = Money::cents((string) ($item->product?->price ?? '0')) * $item->quantity;
            $subtotal += $item->subtotal;
            if (! $item->product || $item->product->trashed() || ! $item->product->is_active || $item->quantity > $item->product->stock) {
                $available = false;
            }
        }
        $shipping = (int) config('pawmart.shipping_cost', 15000) * 100;

        return compact('cart', 'subtotal', 'shipping', 'available');
    }
}
