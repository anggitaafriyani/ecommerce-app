<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProductService
{
    public function save(Product $product, array $data, ?UploadedFile $image): Product
    {
        $newPath = null;
        $oldPath = null;
        try {
            $product = DB::transaction(function () use ($product, $data, $image, &$newPath, &$oldPath): Product {
                if ($product->exists) {
                    $product = Product::query()->lockForUpdate()->findOrFail($product->id);
                }
                $oldPath = $product->image_path;
                $product->fill($data);
                if ($image) {
                    $newPath = $image->store('products', 'public');
                    if (! $newPath) {
                        throw ValidationException::withMessages(['image' => 'Gambar gagal disimpan. Silakan coba kembali.']);
                    }
                    $product->image_path = $newPath;
                }
                $product->save();

                return $product;
            });
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }
            throw $exception;
        }
        if ($newPath && $oldPath && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return $product;
    }
}
