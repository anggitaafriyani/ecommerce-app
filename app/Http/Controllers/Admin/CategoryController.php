<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', ['categories' => Category::query()->withCount('products')->orderBy('name')->paginate(12)]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category]);
    }

    public function store(CategoryRequest $request, CategoryService $service): RedirectResponse
    {
        $service->save(new Category, $request->safe()->except('image'), $request->file('image'));

        return to_route('admin.categories.index')->with('status', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category, CategoryService $service): RedirectResponse
    {
        $service->save($category, $request->safe()->except('image'), $request->file('image'));

        return to_route('admin.categories.index')->with('status', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        try {
            DB::transaction(function () use ($category): void {
                $category = Category::query()->lockForUpdate()->findOrFail($category->id);
                if ($category->products()->withTrashed()->exists()) {
                    throw ValidationException::withMessages(['category' => 'Kategori masih digunakan produk, termasuk arsip. Pindahkan produk ke kategori lain terlebih dahulu.']);
                }
                $category->delete();
            });
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }
            throw ValidationException::withMessages(['category' => 'Kategori masih digunakan produk dan tidak dapat dihapus.']);
        }

        return to_route('admin.categories.index')->with('status', 'Kategori berhasil dihapus.');
    }
}
