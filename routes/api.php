<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes defined here will be exposed under the /api prefix and are
| intended to be consumed by external clients such as the Vue frontend
| and the future logistics repository.
|
*/

Route::prefix('v1')
    ->middleware('api')
    ->group(function (): void {
        Route::prefix('auth')->group(function (): void {
            Route::post('login', [\App\Http\Controllers\Api\Auth\AuthController::class, 'login'])
                ->name('api.v1.auth.login');
            Route::post('register', [\App\Http\Controllers\Api\Auth\AuthController::class, 'register'])
                ->name('api.v1.auth.register');

            Route::middleware('auth:sanctum')->group(function (): void {
                Route::post('logout', [\App\Http\Controllers\Api\Auth\AuthController::class, 'logout'])
                    ->name('api.v1.auth.logout');
                Route::post('refresh', [\App\Http\Controllers\Api\Auth\AuthController::class, 'refresh'])
                    ->name('api.v1.auth.refresh');
            });
        });

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::prefix('customers')->group(function (): void {
                Route::get('/', [\App\Http\Controllers\Api\Customers\CustomerController::class, 'index'])
                    ->name('api.v1.customers.index');
                Route::post('/', [\App\Http\Controllers\Api\Customers\CustomerController::class, 'store'])
                    ->name('api.v1.customers.store');
                Route::get('{publicId}', [\App\Http\Controllers\Api\Customers\CustomerController::class, 'show'])
                    ->name('api.v1.customers.show');
                Route::put('{customer}', [\App\Http\Controllers\Api\Customers\CustomerController::class, 'update'])
                    ->name('api.v1.customers.update');
                Route::patch('{customer}', [\App\Http\Controllers\Api\Customers\CustomerController::class, 'update']);
                Route::delete('{customer}', [\App\Http\Controllers\Api\Customers\CustomerController::class, 'destroy'])
                    ->name('api.v1.customers.destroy');

                Route::get('{customer}/addresses', [\App\Http\Controllers\Api\Customers\AddressController::class, 'index'])
                    ->name('api.v1.customers.addresses.index');
                Route::post('{customer}/addresses', [\App\Http\Controllers\Api\Customers\AddressController::class, 'store'])
                    ->name('api.v1.customers.addresses.store');
                Route::get('{customer}/addresses/{publicId}', [\App\Http\Controllers\Api\Customers\AddressController::class, 'show'])
                    ->name('api.v1.customers.addresses.show');
                Route::put('{customer}/addresses/{publicId}', [\App\Http\Controllers\Api\Customers\AddressController::class, 'update'])
                    ->name('api.v1.customers.addresses.update');
                Route::patch('{customer}/addresses/{publicId}', [\App\Http\Controllers\Api\Customers\AddressController::class, 'update']);
                Route::delete('{customer}/addresses/{publicId}', [\App\Http\Controllers\Api\Customers\AddressController::class, 'destroy'])
                    ->name('api.v1.customers.addresses.destroy');
            });
        });

        Route::prefix('catalog')->group(function (): void {
            Route::get('products', [\App\Http\Controllers\Api\Catalog\ProductController::class, 'index'])
                ->name('api.v1.catalog.products.index');
            Route::get('products/featured', [\App\Http\Controllers\Api\Catalog\ProductController::class, 'featured'])
                ->name('api.v1.catalog.products.featured');
            Route::get('products/{publicId}', [\App\Http\Controllers\Api\Catalog\ProductController::class, 'show'])
                ->name('api.v1.catalog.products.show');

            Route::get('categories', [\App\Http\Controllers\Api\Catalog\CategoryController::class, 'index'])
                ->name('api.v1.catalog.categories.index');
            Route::get('brands', [\App\Http\Controllers\Api\Catalog\BrandController::class, 'index'])
                ->name('api.v1.catalog.brands.index');
        });

        Route::prefix('public')->group(function (): void {
            Route::get('products/featured', [\App\Http\Controllers\Api\Catalog\ProductController::class, 'featured'])
                ->name('api.v1.public.products.featured');
        });

        Route::get('shipping-methods', [\App\Http\Controllers\Api\Logistics\ShippingMethodController::class, 'index'])
            ->name('api.v1.logistics.shipping-methods.index');

        Route::post('logistics/quote', [\App\Http\Controllers\Api\Logistics\LogisticsController::class, 'quote'])
            ->name('api.v1.logistics.quote');

        Route::prefix('cart')->group(function (): void {
            Route::post('session', [\App\Http\Controllers\Api\Cart\CartController::class, 'createSession'])
                ->name('api.v1.cart.session');
            Route::get('/', [\App\Http\Controllers\Api\Cart\CartController::class, 'show'])
                ->name('api.v1.cart.show');
            Route::post('items', [\App\Http\Controllers\Api\Cart\CartController::class, 'addItem'])
                ->name('api.v1.cart.items.store');
            Route::put('items/{id}', [\App\Http\Controllers\Api\Cart\CartController::class, 'updateItem'])
                ->name('api.v1.cart.items.update');
            Route::delete('items/{id}', [\App\Http\Controllers\Api\Cart\CartController::class, 'removeItem'])
                ->name('api.v1.cart.items.destroy');
            Route::post('apply-coupon', [\App\Http\Controllers\Api\Cart\CartController::class, 'applyCoupon'])
                ->name('api.v1.cart.coupon.apply');
            Route::delete('coupon', [\App\Http\Controllers\Api\Cart\CartController::class, 'removeCoupon'])
                ->name('api.v1.cart.coupon.remove');
            Route::delete('/', [\App\Http\Controllers\Api\Cart\CartController::class, 'clear'])
                ->name('api.v1.cart.clear');
            Route::get('recover/{token}', [\App\Http\Controllers\Api\Cart\CartRecoveryController::class, 'show'])
                ->name('api.v1.cart.recover.show');
        });

        Route::prefix('checkout')->group(function (): void {
            Route::post('/', [\App\Http\Controllers\Api\Checkout\CheckoutController::class, 'store'])
                ->name('api.v1.checkout.store');
            Route::post('quick', [\App\Http\Controllers\Api\Checkout\CheckoutController::class, 'quick'])
                ->name('api.v1.checkout.quick');
        });

        Route::prefix('orders')->group(function (): void {
            Route::get('/', [\App\Http\Controllers\Api\Orders\OrderController::class, 'index'])
                ->name('api.v1.orders.index');
            Route::get('{publicId}', [\App\Http\Controllers\Api\Orders\OrderController::class, 'show'])
                ->name('api.v1.orders.show');
            Route::get('{order}/tracking', [\App\Http\Controllers\Api\Orders\OrderController::class, 'tracking'])
                ->name('api.v1.orders.tracking');
        });

        Route::prefix('payments')->group(function (): void {
            Route::post('webhooks/{provider}', [\App\Http\Controllers\Api\Payments\PaymentWebhookController::class, 'handle'])
                ->name('api.v1.payments.webhooks.handle');
            Route::post('{id}/capture', [\App\Http\Controllers\Api\Payments\PaymentAdminController::class, 'capture'])
                ->name('api.v1.payments.capture');
            Route::post('{id}/cancel', [\App\Http\Controllers\Api\Payments\PaymentAdminController::class, 'cancel'])
                ->name('api.v1.payments.cancel');
            Route::post('{id}/refund', [\App\Http\Controllers\Api\Payments\PaymentAdminController::class, 'refund'])
                ->name('api.v1.payments.refund');
        });
    });

