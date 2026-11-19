<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\CourierService;
use App\Models\ShippingRate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingController extends Controller
{
    public function index(Request $request): View
    {
        $couriers = Courier::with(['services.rates'])->get();
        $rates = ShippingRate::with(['service.courier'])
            ->when($request->query('courier_id'), function ($q, $id) {
                $q->whereHas('service', fn ($s) => $s->where('courier_id', $id));
            })
            ->when($request->query('province'), function ($q, $prov) {
                $q->where('province', 'like', "%{$prov}%");
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.shipping.index', compact('couriers', 'rates'));
    }

    public function storeCourier(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:couriers,code'],
            'is_active' => ['boolean'],
        ]);

        Courier::create([
            'name' => $validated['name'],
            'code' => strtolower($validated['code']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', 'Ekspedisi kurir baru berhasil ditambahkan.');
    }

    public function updateCourier(Request $request, Courier $courier): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $courier->update($validated);

        return back()->with('status', 'Data kurir berhasil diperbarui.');
    }

    public function storeService(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'courier_id' => ['required', 'exists:couriers,id'],
            'service_name' => ['required', 'string', 'max:100'],
            'service_code' => ['required', 'string', 'max:50', 'alpha_dash'],
            'estimated_days' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        CourierService::create([
            'courier_id' => $validated['courier_id'],
            'service_name' => $validated['service_name'],
            'service_code' => strtolower($validated['service_code']),
            'estimated_days' => $validated['estimated_days'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', 'Layanan kurir berhasil ditambahkan.');
    }

    public function updateService(Request $request, CourierService $service): RedirectResponse
    {
        $validated = $request->validate([
            'service_name' => ['sometimes', 'required', 'string', 'max:100'],
            'estimated_days' => ['nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $service->update($validated);

        return back()->with('status', 'Data layanan kurir berhasil diperbarui.');
    }

    public function storeRate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'courier_service_id' => ['required', 'exists:courier_services,id'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'base_cost' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'cost_per_kg' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'min_weight' => ['nullable', 'integer', 'min:0'],
            'max_weight' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        ShippingRate::create([
            'courier_service_id' => $validated['courier_service_id'],
            'province' => $validated['province'],
            'city' => $validated['city'] ?: null,
            'base_cost' => $validated['base_cost'],
            'cost_per_kg' => $validated['cost_per_kg'],
            'min_weight' => $validated['min_weight'] ?? 0,
            'max_weight' => $validated['max_weight'] ?? 30000,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', 'Tarif pengiriman baru berhasil disimpan.');
    }

    public function updateRate(Request $request, ShippingRate $rate): RedirectResponse
    {
        $validated = $request->validate([
            'base_cost' => ['sometimes', 'required', 'numeric', 'min:0'],
            'cost_per_kg' => ['sometimes', 'required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $rate->update($validated);

        return back()->with('status', 'Tarif pengiriman berhasil diperbarui.');
    }

    public function destroyRate(ShippingRate $rate): RedirectResponse
    {
        $rate->delete();

        return back()->with('status', 'Tarif pengiriman telah dihapus.');
    }
}
