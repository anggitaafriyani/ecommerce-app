<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CategoryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('name'))) {
            $this->merge(['name' => Str::squish($this->input('name'))]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_ADMIN;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($this->route('category'))],
            'description' => ['nullable', 'string', 'max:3000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4096,max_height=4096'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $category = $this->route('category');
            $others = Category::query()->when($category, fn ($query) => $query->whereKeyNot($category->id));
            if (! $validator->errors()->has('name') && (clone $others)->where('name_key', Category::nameKey($this->input('name')))->exists()) {
                $validator->errors()->add('name', 'Nama kategori sudah digunakan. Pilih nama yang berbeda.');
            }
            if (! $validator->errors()->has('image') && $this->hasFile('image')) {
                $hash = hash_file('sha256', $this->file('image')->getRealPath());
                if ((clone $others)->where('image_hash', $hash)->exists()) {
                    $validator->errors()->add('image', 'Gambar ini sudah digunakan kategori lain. Pilih gambar yang berbeda.');
                }
            }
        }];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah digunakan. Pilih nama yang berbeda.',
            'image.image' => 'Unggah file gambar yang valid.',
            'image.mimes' => 'Gunakan gambar JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran gambar maksimal 2 MB.',
            'image.dimensions' => 'Dimensi gambar maksimal 4096 × 4096 piksel.',
        ];
    }
}
