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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->change();
            $table->string('slug')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
        });
        DB::table('products')->orderBy('id')->each(function (object $product): void {
            DB::table('products')->where('id', $product->id)->update([
                'slug' => Str::limit(Str::slug($product->name), 180, '').'-'.$product->id,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'is_active', 'deleted_at']);
        });
        // Harga tetap decimal agar rollback tidak memotong pecahan atau nilai besar.
    }
};
