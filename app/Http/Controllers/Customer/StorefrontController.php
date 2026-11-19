<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CatalogRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function home(): View
    {
        return view('customer.home', [
            'categories' => Category::query()->withCount(['products' => fn ($query) => $query->where('is_active', true)])->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', true)->with('category')->latest('id')->limit(8)->get(),
        ]);
    }

    public function index(CatalogRequest $request): View
    {
        $filters = $request->validated();
        $products = Product::query()->where('is_active', true)->with('category')
            ->when(isset($filters['q']) && $filters['q'] !== '', fn ($query) => $query->where('name', 'like', '%'.$filters['q'].'%'))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category_id', $category))
            ->latest('id')->paginate(12)->withQueryString();

        return view('customer.products.index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function show(string $slug): View
    {
        $product = Product::query()
            ->where('is_active', true)
            ->where('slug', $slug)
            ->with(['category', 'reviews.user'])
            ->firstOrFail();

        return view('customer.products.show', compact('product'));
    }
}
