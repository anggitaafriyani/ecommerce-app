<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderIssue;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function show(Request $request, int $order, PaymentService $payments): View
    {
        $orderModel = Order::where('user_id', $request->user()->id)
            ->with(['items.review', 'payment', 'histories', 'issues'])
            ->findOrFail($order);

        // Auto expire if past expiration time
        if ($orderModel->payment_status === 'pending' && $orderModel->stock_reserved && $orderModel->expires_at && $orderModel->expires_at->isPast()) {
            try {
                $payments->cancel($orderModel, true);
                $orderModel->refresh();
            } catch (\Throwable $e) {
                // Keep showing current state
            }
        }

        return view('customer.order', ['order' => $orderModel]);
    }

    public function invoice(Request $request, int $order): View
    {
        return view('customer.invoice', [
            'order' => Order::where('user_id', $request->user()->id)
                ->with(['items', 'user'])
                ->findOrFail($order),
        ]);
    }

    public function cancel(Request $request, int $order, PaymentService $reservations): RedirectResponse
    {
        $order = Order::where('user_id', $request->user()->id)->findOrFail($order);

        if (in_array($order->payment_status, ['paid', 'success'], true) || in_array($order->order_status, ['processing', 'shipped', 'completed'], true)) {
            throw ValidationException::withMessages([
                'order' => 'Pesanan yang sudah dibayar atau diproses tidak dapat dibatalkan.',
            ]);
        }

        if ($order->order_status === 'cancelled') {
            return back()->with('status', 'Pesanan ini sudah dibatalkan.');
        }

        try {
            $reservations->cancel($order);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages(['payment' => $exception->getMessage()]);
        }

        return back()->with('status', 'Pesanan berhasil dibatalkan dan reservasi stok produk telah dikembalikan.');
    }

    /**
     * Customer confirms package received.
     */
    public function confirmReceived(Request $request, int $order): RedirectResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($user, $order): void {
            $orderModel = Order::where('user_id', $user->id)
                ->lockForUpdate()
                ->findOrFail($order);

            // Idempotent: if already completed, simply return without error
            if ($orderModel->order_status === 'completed') {
                return;
            }

            if ($orderModel->payment_status !== 'paid' || $orderModel->order_status !== 'shipped') {
                throw ValidationException::withMessages([
                    'order' => 'Konfirmasi penerimaan hanya dapat dilakukan pada pesanan yang sudah berstatus Dikirim.',
                ]);
            }

            $orderModel->update([
                'order_status' => 'completed',
                'completed_at' => now(),
            ]);

            $orderModel->recordHistory(
                'completed',
                'customer',
                $user->name,
                'Pelanggan telah mengonfirmasi bahwa pesanan diterima dengan baik.'
            );
        }, 3);

        return back()->with('status', 'Terima kasih! Pesanan telah Anda konfirmasi diterima.');
    }

    /**
     * Customer reports an issue with the order.
     */
    public function reportIssue(Request $request, int $order): RedirectResponse
    {
        $user = $request->user();
        $orderModel = Order::where('user_id', $user->id)->findOrFail($order);

        if (! in_array($orderModel->order_status, ['shipped', 'completed'], true)) {
            throw ValidationException::withMessages([
                'order' => 'Laporan masalah hanya dapat diajukan setelah pesanan dikirim atau diterima.',
            ]);
        }

        $validated = $request->validate([
            'category' => ['required', 'string', Rule::in(array_keys(OrderIssue::CATEGORY_LABELS))],
            'description' => ['required', 'string', 'min:5', 'max:2000'],
        ], [
            'category.required' => 'Pilih kategori kendala.',
            'category.in' => 'Kategori kendala tidak valid.',
            'description.required' => 'Tuliskan penjelasan kendala yang dialami.',
            'description.min' => 'Penjelasan kendala minimal 5 karakter.',
            'description.max' => 'Penjelasan kendala maksimal 2.000 karakter.',
        ]);

        $orderModel->issues()->create([
            'user_id' => $user->id,
            'category' => $validated['category'],
            'description' => $validated['description'],
            'status' => 'open',
        ]);

        $orderModel->recordHistory(
            'issue_reported',
            'customer',
            $user->name,
            'Pelanggan melaporkan kendala: '.OrderIssue::CATEGORY_LABELS[$validated['category']]
        );

        return back()->with('status', 'Laporan kendala berhasil dikirim. Pengelola toko akan segera memeriksa kendala Anda.');
    }
}
