<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $categories = DB::table('categories')->get(['id', 'name']);
        $keys = $categories->mapWithKeys(fn (object $category): array => [
            $category->id => hash('sha256', Str::lower(Str::squish($category->name))),
        ]);
        if ($keys->duplicates()->isNotEmpty()) {
            throw new RuntimeException('Nama kategori duplikat setelah normalisasi. Tinjau data sebelum migration; tidak ada kategori yang diubah.');
        }
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_key', 64)->nullable()->unique();
            $table->string('image_path')->nullable()->unique();
            $table->string('image_hash', 64)->nullable()->unique();
        });
        foreach ($keys as $id => $key) {
            DB::table('categories')->where('id', $id)->update(['name_key' => $key]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['name_key']);
            $table->dropUnique(['image_path']);
            $table->dropUnique(['image_hash']);
            $table->dropColumn(['name_key', 'image_path', 'image_hash']);
        });
    }
};
