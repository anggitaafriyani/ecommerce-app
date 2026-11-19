<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $ordersQuery = $user->orders();

        $unpaid = (clone $ordersQuery)->where('payment_status', 'pending')->where('order_status', 'awaiting_payment')->count();
        $processing = (clone $ordersQuery)->where('payment_status', 'paid')->where('order_status', 'processing')->count();
        $shipped = (clone $ordersQuery)->where('order_status', 'shipped')->count();
        $completed = (clone $ordersQuery)->where('order_status', 'completed')->count();
        $total = (clone $ordersQuery)->count();

        $stats = [
            'unpaid' => $unpaid,
            'processing' => $processing,
            'shipped' => $shipped,
            'completed' => $completed,
            'total' => $total,
            'pending' => $unpaid,
            'in_progress' => $processing + $shipped,
        ];

        $recentOrders = (clone $ordersQuery)
            ->with('items')
            ->latest()
            ->take(5)
            ->get();

        $recommendedProducts = Product::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->with('category')
            ->latest()
            ->take(4)
            ->get();

        $cartCount = $user->cart?->items()->sum('quantity') ?? 0;

        $categories = Category::withCount('products')->orderBy('name')->get();

        return view('customer.dashboard', compact(
            'stats',
            'recentOrders',
            'recommendedProducts',
            'categories',
            'cartCount'
        ));
    }
}
