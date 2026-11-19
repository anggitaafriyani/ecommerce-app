<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductReviewController extends Controller
{
    /**
     * Store or update a customer review for an ordered item.
     */
    public function store(Request $request, Order $order, OrderItem $item): RedirectResponse
    {
        $user = $request->user();

        // 1. Verify order ownership
        if ($order->user_id !== $user->id) {
            abort(403, 'Akses ulasan pesanan tidak diizinkan.');
        }

        // 2. Verify order status is completed
        if ($order->order_status !== 'completed') {
            throw ValidationException::withMessages([
                'review' => 'Ulasan hanya dapat diberikan setelah pesanan Anda berstatus Selesai.',
            ]);
        }

        // 3. Verify item belongs to this order
        if ($item->order_id !== $order->id) {
            abort(404, 'Item pesanan tidak ditemukan.');
        }

        // 4. Validate input
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:3', 'max:1000'],
        ], [
            'rating.required' => 'Pilih bintang rating (1 - 5).',
            'rating.between' => 'Rating harus antara 1 sampai 5 bintang.',
            'comment.required' => 'Tuliskan ulasan atau pengalaman Anda menggunakan produk ini.',
            'comment.min' => 'Ulasan minimal 3 karakter.',
            'comment.max' => 'Ulasan maksimal 1.000 karakter.',
        ]);

        // 5. Persist review (1 review per ordered item)
        ProductReview::updateOrCreate(
            ['order_item_id' => $item->id],
            [
                'user_id' => $user->id,
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'rating' => (int) $validated['rating'],
                'comment' => trim($validated['comment']),
            ]
        );

        return back()->with('status', 'Terima kasih! Ulasan Anda untuk produk '.$item->product_name.' berhasil disimpan.');
    }
}
