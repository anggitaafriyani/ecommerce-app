<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add weight to products table
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'weight')) {
                $table->unsignedInteger('weight')->default(500)->after('stock');
            }
        });

        // 2. Add shipping and fulfillment columns to orders table
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'courier_code')) {
                $table->string('courier_code', 50)->nullable()->after('shipping_cost');
                $table->string('courier_name', 100)->nullable()->after('courier_code');
                $table->string('courier_service', 100)->nullable()->after('courier_name');
                $table->unsignedInteger('total_weight')->default(0)->after('courier_service');
                $table->string('tracking_number', 100)->nullable()->after('total_weight');
                $table->timestamp('shipped_at')->nullable()->after('tracking_number');
                $table->timestamp('completed_at')->nullable()->after('shipped_at');
                $table->text('shipping_notes')->nullable()->after('completed_at');
            }
        });

        // 3. Couriers table
        if (! Schema::hasTable('couriers')) {
            Schema::create('couriers', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name', 100);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 4. Courier services table
        if (! Schema::hasTable('courier_services')) {
            Schema::create('courier_services', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('courier_id')->constrained()->cascadeOnDelete();
                $table->string('service_code', 50);
                $table->string('service_name', 100);
                $table->string('estimated_days', 50)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['courier_id', 'service_code']);
            });
        }

        // 5. Shipping rates table
        if (! Schema::hasTable('shipping_rates')) {
            Schema::create('shipping_rates', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('courier_service_id')->constrained()->cascadeOnDelete();
                $table->string('province', 100);
                $table->string('city', 100)->nullable();
                $table->unsignedInteger('min_weight')->default(0);
                $table->unsignedInteger('max_weight')->default(30000);
                $table->decimal('base_cost', 12, 2);
                $table->decimal('cost_per_kg', 12, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index(['province', 'city', 'is_active']);
            });
        }

        // 6. Order status history table
        if (! Schema::hasTable('order_histories')) {
            Schema::create('order_histories', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->string('status', 50);
                $table->string('actor_type', 50)->default('system');
                $table->string('actor_name', 100)->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 7. Order issues reporting table
        if (! Schema::hasTable('order_issues')) {
            Schema::create('order_issues', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('category', 100);
                $table->text('description');
                $table->string('status', 50)->default('open');
                $table->text('admin_notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_issues');
        Schema::dropIfExists('order_histories');
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('courier_services');
        Schema::dropIfExists('couriers');

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'courier_code',
                'courier_name',
                'courier_service',
                'total_weight',
                'tracking_number',
                'shipped_at',
                'completed_at',
                'shipping_notes',
            ]);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('weight');
        });
    }
};
