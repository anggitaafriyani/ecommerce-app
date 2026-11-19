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
        static::saving(function (Category $category): void {
            $category->name = Str::squish($category->name);
            $category->name_key = self::nameKey($category->name);
        });
        static::creating(function (Category $category): void {
            $category->slug = Str::limit(Str::slug($category->name), 180, '').'-'.Str::lower((string) Str::ulid());
        });
    }

    public static function nameKey(string $name): string
    {
        return hash('sha256', Str::lower(Str::squish($name)));
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
