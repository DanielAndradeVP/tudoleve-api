<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('code')->unique();
            $table->enum('discount_type', ['fixed', 'percent']);
            $table->decimal('discount_value', 10, 2);
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            $table->datetime('starts_at')->nullable();
            $table->datetime('ends_at')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_per_customer')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['coupon_id', 'customer_id']);
        });

        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->decimal('base_cost', 10, 2)->default(0);
            $table->unsignedInteger('estimated_min_days')->nullable();
            $table->unsignedInteger('estimated_max_days')->nullable();
            $table->json('configuration')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipping_method_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('shipping_cost', 10, 2)->nullable();
            $table->string('tracking_code')->nullable()->index();
            $table->string('logistics_status')->default('pending');
            $table->datetime('estimated_delivery_at')->nullable();
            $table->datetime('delivered_at')->nullable();
            $table->string('external_reference')->nullable()->index();
            $table->string('label_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['product_id', 'is_approved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};

