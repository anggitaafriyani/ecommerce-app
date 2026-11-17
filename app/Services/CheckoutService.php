<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(private CartService $carts) {}

    public function place(User $user, array $data): Order
    {
        $cart = $this->carts->cart($user);

        return DB::transaction(function () use ($user, $data, $cart) {
            // All cart writes lock this row first; product locks follow in ascending ID order.
            $cart = Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();
            $existing = Order::where('user_id', $user->id)->where('checkout_token', $data['checkout_token'])->first();
            if ($existing) {
                return $existing;
            }
            if (! hash_equals($cart->checkout_token, $data['checkout_token'])) {
                throw ValidationException::withMessages(['cart' => 'Keranjang berubah. Buka kembali checkout untuk meninjau pesanan.']);
            }
            $items = $cart->items()->orderBy('product_id')->get();
            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Keranjang masih kosong.']);
            }
            $products = Product::whereIn('id', $items->pluck('product_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $subtotal = 0;
            $snapshots = [];
            foreach ($items as $item) {
                $product = $products->get($item->product_id);
                $this->carts->validateProduct($product, $item->quantity);
                if (Money::cents($product->price) > intdiv(999999999999, $item->quantity)) {
                    throw ValidationException::withMessages(['cart' => 'Total pesanan melebihi batas transaksi.']);
                }
                $line = Money::cents($product->price) * $item->quantity;
                $subtotal += $line;
                if ($subtotal > 999999999999) {
                    throw ValidationException::withMessages(['cart' => 'Total pesanan melebihi batas transaksi.']);
                }
                $snapshots[] = ['product_id' => $product->id, 'product_name' => $product->name, 'product_price' => $product->price, 'quantity' => $item->quantity, 'subtotal' => Money::decimal($line)];
            }
            $shipping = (int) config('pawmart.shipping_cost', 15000) * 100;
            $total = $subtotal + $shipping;
            if ($shipping < 0 || $total > 999999999999) {
                throw ValidationException::withMessages(['cart' => 'Total pesanan melebihi batas transaksi.']);
            }
            $order = new Order;
            $order->fill(collect($data)->only(['recipient_name', 'recipient_phone', 'shipping_address', 'city', 'province', 'postal_code', 'notes'])->all());
            $order->forceFill([
                'user_id' => $user->id,
                'order_number' => 'PWM-'.Str::ulid(),
                'checkout_token' => $data['checkout_token'],
                'subtotal' => Money::decimal($subtotal),
                'shipping_cost' => Money::decimal($shipping),
                'total_amount' => Money::decimal($total),
                'total_price' => Money::decimal($total),
                'payment_status' => 'pending',
                'order_status' => 'awaiting_payment',
                'stock_reserved' => true,
                'expires_at' => now()->addMinutes((int) config('pawmart.reservation_minutes', 60)),
            ])->save();
            $order->items()->createMany($snapshots);
            foreach ($items as $item) {
                $products[$item->product_id]->decrement('stock', $item->quantity);
            }
            $cart->items()->delete();
            $cart->update(['checkout_token' => (string) Str::uuid()]);

            return $order;
        }, 3);
    }
}
