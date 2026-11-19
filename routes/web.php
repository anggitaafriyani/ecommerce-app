<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ShippingController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\ProductReviewController;
use App\Http\Controllers\Customer\StorefrontController;
use App\Http\Controllers\MidtransNotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::pattern('product', '[0-9]+');
Route::pattern('order', '[0-9]+');
Route::pattern('item', '[0-9]+');
Route::post('/midtrans/notification', MidtransNotificationController::class)->name('midtrans.notification');

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/products', [StorefrontController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [StorefrontController::class, 'show'])->name('products.show');
Route::get('/shop-login/{slug}', [CartController::class, 'login'])->middleware('guest')->name('shop.login');

Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/dashboard', CustomerDashboardController::class)->name('dashboard');
    Route::get('/orders', [PaymentController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}/payment', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/orders/{order}/payment', [PaymentController::class, 'start'])->middleware('throttle:10,1')->name('payments.start');
    Route::post('/orders/{order}/payment/status', [PaymentController::class, 'refresh'])->middleware('throttle:20,1')->name('payments.refresh');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{product}', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{product}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::post('/checkout/shipping-rates', [CheckoutController::class, 'shippingRates'])->name('checkout.shipping-rates');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/confirm-received', [OrderController::class, 'confirmReceived'])->name('orders.confirm-received');
    Route::post('/orders/{order}/issues', [OrderController::class, 'reportIssue'])->name('orders.issues.store');
    Route::post('/orders/{order}/items/{item}/reviews', [ProductReviewController::class, 'store'])->name('orders.items.reviews.store');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('products', ProductController::class);
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update']);

    // Shipping Management
    Route::get('/shipping', [ShippingController::class, 'index'])->name('shipping.index');
    Route::post('/shipping/couriers', [ShippingController::class, 'storeCourier'])->name('shipping.couriers.store');
    Route::patch('/shipping/couriers/{courier}', [ShippingController::class, 'updateCourier'])->name('shipping.couriers.update');
    Route::post('/shipping/services', [ShippingController::class, 'storeService'])->name('shipping.services.store');
    Route::patch('/shipping/services/{service}', [ShippingController::class, 'updateService'])->name('shipping.services.update');
    Route::post('/shipping/rates', [ShippingController::class, 'storeRate'])->name('shipping.rates.store');
    Route::patch('/shipping/rates/{rate}', [ShippingController::class, 'updateRate'])->name('shipping.rates.update');
    Route::delete('/shipping/rates/{rate}', [ShippingController::class, 'destroyRate'])->name('shipping.rates.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
