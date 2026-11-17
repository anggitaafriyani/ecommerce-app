<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $products = Product::query()->with('category')
            ->when(isset($filters['q']) && $filters['q'] !== '', fn ($query) => $query->where('name', 'like', '%'.$filters['q'].'%'))
            ->latest('id')->paginate(12)->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('admin.products.form', ['product' => new Product(['is_active' => true]), 'categories' => Category::query()->orderBy('name')->get()]);
    }

    public function store(ProductRequest $request, ProductService $service): RedirectResponse
    {
        $product = $service->save(new Product, $request->safe()->except('image'), $request->file('image'));

        return to_route('admin.products.show', $product)->with('status', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product): View
    {
        return view('admin.products.show', ['product' => $product->load('category')]);
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', ['product' => $product, 'categories' => Category::query()->orderBy('name')->get()]);
    }

    public function update(ProductRequest $request, Product $product, ProductService $service): RedirectResponse
    {
        $service->save($product, $request->safe()->except('image'), $request->file('image'));

        return to_route('admin.products.show', $product)->with('status', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        DB::transaction(function () use ($product): void {
            $product = Product::query()->lockForUpdate()->findOrFail($product->id);
            $product->update(['is_active' => false]);
            $product->delete();
        });

        return to_route('admin.products.index')->with('status', 'Produk dihapus dari daftar. Riwayat dan gambar tetap tersimpan.');
    }
}
