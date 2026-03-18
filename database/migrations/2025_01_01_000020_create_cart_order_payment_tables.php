<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_total', 10, 2)->default(0);
            $table->decimal('shipping_total', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->timestamp('abandoned_at')->nullable()->index();
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->string('recovery_token')->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['cart_id']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('billing_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->foreignId('shipping_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_total', 10, 2)->default(0);
            $table->decimal('shipping_total', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2);
            $table->decimal('quoted_shipping_cost', 10, 2)->nullable();
            $table->string('currency', 3)->default('BRL');
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('pending');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['customer_id', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->string('product_sku');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['order_id']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->nullable();
            $table->string('method')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('BRL');
            $table->string('status')->default('pending');
            $table->string('external_reference')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['order_id', 'status']);
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_type');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('BRL');
            $table->string('status')->default('pending');
            $table->string('external_reference')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['payment_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};

