<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromoCodeController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductBrandController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\BillingAddressController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderLineController;
use App\Http\Controllers\PaymentController;

// Public routes
Route::post('/user/register', [AuthController::class, 'registerUser']);
Route::post('/email/verify', [AuthController::class, 'verifyEmail'])->name('verification.verify');
Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail']);

Route::post('/user/login', [AuthController::class, 'userLogin']);
Route::post('/user/password/email', [AuthController::class, 'userSendResetLinkEmail']);
Route::post('/user/password/reset', [AuthController::class, 'userReset']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/admin/password/email', [AuthController::class, 'adminSendResetLinkEmail']);
Route::post('/admin/password/reset', [AuthController::class, 'adminReset']);

//Category lists
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{id}', [ProductController::class, 'detail']);
Route::get('related-products', [ProductController::class, 'relatedProducts']);
Route::get('category-list', [ProductCategoryController::class, 'categoryList']);
Route::get('brand-list', [ProductBrandController::class, 'brandList']);
Route::get('payment-methods', [PaymentMethodController::class, 'getList']);

Route::get('country-list', [CountryController::class, 'countryList']);
Route::get('state-list', [StateController::class, 'stateList']);
Route::get('check-promo-code/{code}', [PromoCodeController::class, 'checkPromoCode']);

//Guest user orders
Route::post('guest/orders', [OrderController::class, 'storeGuestUserOrder']);
Route::post('checkout', [PaymentController::class, 'store']);

// User routes
Route::prefix('user')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::post('/edit', [UserController::class, 'edit']);
    Route::apiResource('billing-addresses', BillingAddressController::class);

    Route::apiResource('carts', CartController::class);
    Route::apiResource('orders', OrderController::class);

    Route::get('country-list', [CountryController::class, 'index']);
    Route::get('state-list', [StateController::class, 'index']);
    Route::apiResource('payments', PaymentController::class);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Admin routes
Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {
//    Route::get('/dashboard', [AdminController::class, 'dashboard']);

    Route::apiResource('admins', AdminController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('countries', CountryController::class);
    Route::apiResource('product-categories', ProductCategoryController::class);
    Route::apiResource('product-brands', ProductBrandController::class);
    Route::apiResource('colors', ColorController::class);
    Route::apiResource('sizes', SizeController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('payment-methods', PaymentMethodController::class);
    Route::apiResource('states', StateController::class);
    Route::apiResource('billing-addresses', BillingAddressController::class);
    Route::apiResource('product-variants', ProductVariantController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('promo-codes', PromoCodeController::class);
    Route::apiResource('order-lines', OrderLineController::class);
    Route::apiResource('payments', PaymentController::class);
    Route::post('orders/order-change-status/{id}', [OrderController::class, "orderChangeStatus"]);
    Route::get('pending-order-counts', [OrderController::class, 'pendingOrderCount']);
    Route::get('confirm-order-counts', [OrderController::class, 'confirmOrderCount']);
    Route::get('cancel-order-counts', [OrderController::class, 'cancelOrderCount']);
    Route::get('shipped-order-counts', [OrderController::class, 'shippedOrderCount']);
    Route::get('delivered-order-counts', [OrderController::class, 'deliveredOrderCount']);
    Route::get('monthly-best-selling-product-lists', [OrderController::class, 'monthlyBestSellingProductList']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('user-order-details', [OrderController::class, 'orderDetail']);
    Route::get('user-billing-address-details', [BillingAddressController::class, 'userBillingAddressDetail']);
});
