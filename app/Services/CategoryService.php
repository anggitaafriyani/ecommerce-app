<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class CategoryService
{
    /** @param array{name: string, description?: ?string} $data */
    public function save(Category $category, array $data, ?UploadedFile $image): Category
    {
        $newPath = null;
        try {
            return DB::transaction(function () use ($category, $data, $image, &$newPath): Category {
                if ($category->exists) {
                    $category = Category::query()->lockForUpdate()->findOrFail($category->id);
                }
                $category->fill($data);
                if ($image) {
                    $hash = hash_file('sha256', $image->getRealPath());
                    if ($hash !== $category->image_hash) {
                        $newPath = $image->store('categories', 'public');
                        if (! $newPath) {
                            throw ValidationException::withMessages(['image' => 'Gambar gagal disimpan. Silakan coba kembali.']);
                        }
                        $category->image_path = $newPath;
                        $category->image_hash = $hash;
                    }
                }
                $category->save();

                return $category;
            });
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }
            if ($exception instanceof UniqueConstraintViolationException) {
                if (str_contains($exception->getMessage(), 'image_hash') || str_contains($exception->getMessage(), 'image_path')) {
                    throw ValidationException::withMessages(['image' => 'Gambar ini sudah digunakan kategori lain. Pilih gambar yang berbeda.']);
                }
                if (str_contains($exception->getMessage(), 'name')) {
                    throw ValidationException::withMessages(['name' => 'Nama kategori sudah digunakan. Pilih nama yang berbeda.']);
                }
            }
            throw $exception;
        }
    }
}
