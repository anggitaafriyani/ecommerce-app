<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderFilterRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderFulfillmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(OrderFilterRequest $request): View
    {
        $filters = $request->validated();
        $orders = Order::query()->with('user')->withSum('items', 'quantity');
        $search = $filters['q'] ?? null;
        if ($search !== null && $search !== '') {
            $orders->where(function (Builder $query) use ($search): void {
                $query->where('order_number', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn (Builder $users) => $users->where('name', 'like', '%'.$search.'%'));
            });
        }
        foreach (['payment_status', 'order_status'] as $field) {
            if ($value = $filters[$field] ?? null) {
                $orders->where($field, $value);
            }
        }

        return view('admin.orders.index', ['orders' => $orders->latest('id')->paginate(15)->withQueryString(), 'filters' => $filters]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load(['user', 'items', 'payment', 'histories', 'issues.user']),
        ]);
    }

    public function update(UpdateOrderStatusRequest $request, Order $order, OrderFulfillmentService $fulfillment): RedirectResponse
    {
        $fulfillment->update(
            $order,
            $request->validated('order_status'),
            $request->validated('tracking_number'),
            $request->validated('shipping_notes'),
            $request->user()?->name ?? 'Admin PawMart'
        );

        return to_route('admin.orders.show', $order)->with('status', 'Status pemrosesan pesanan berhasil diperbarui.');
    }
}
