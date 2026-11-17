<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'products' => Product::count(), 'categories' => Category::count(),
                'customers' => User::query()->where('role', User::ROLE_CUSTOMER)->count(),
                'orders' => Order::count(),
                'pending' => Order::query()->where('payment_status', 'pending')->count(),
                'paid' => Order::query()->whereIn('payment_status', ['paid', 'success'])->count(),
            ],
            'recentProducts' => Product::query()->with('category')->latest('id')->limit(5)->get(),
            'lowStockProducts' => Product::query()->where('is_active', true)->where('stock', '<=', 5)->orderBy('stock')->limit(5)->get(),
        ]);
    }
}
