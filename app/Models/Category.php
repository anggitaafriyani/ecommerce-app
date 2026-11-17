<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'description'])]
class Category extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Category $category): void {
            $category->slug = Str::limit(Str::slug($category->name), 180, '').'-'.Str::lower((string) Str::ulid());
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
