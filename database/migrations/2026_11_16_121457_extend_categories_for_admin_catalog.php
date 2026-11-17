<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('categories')->select('name')->groupBy('name')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Selesaikan nama kategori duplikat sebelum migration. Data tidak dihapus otomatis.');
        }
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique();
            $table->text('description')->nullable();
            $table->unique('name');
        });
        DB::table('categories')->orderBy('id')->each(function (object $category): void {
            DB::table('categories')->where('id', $category->id)->update([
                'slug' => Str::limit(Str::slug($category->name), 180, '').'-'.$category->id,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'description']);
        });
    }
};
