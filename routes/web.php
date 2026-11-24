<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\CustomerLeadController as AdminCustomerLeadController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InquiryController as AdminInquiryController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentSettingController as AdminPaymentSettingController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ShippingSettingController as AdminShippingSettingController;
use App\Http\Controllers\Admin\StockController as AdminStockController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\InquirySubmissionController;
use App\Http\Controllers\InvoiceDownloadController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RazorpayWebhookController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Storefront Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{path}', [CategoryController::class, 'show'])->where('path', '.*')->name('categories.show');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
Route::post('/inquiries', [InquirySubmissionController::class, 'store'])->name('inquiries.store');

/*
|--------------------------------------------------------------------------
| Shopping Cart & Wishlist Routes
|--------------------------------------------------------------------------
*/

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{id}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/cart/sync', [CartController::class, 'sync'])->name('cart.sync');

Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::get('/wishlist/status', [WishlistController::class, 'status'])->name('wishlist.status');

/*
|--------------------------------------------------------------------------
| Customer Checkout & Razorpay Integration
|--------------------------------------------------------------------------
*/

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::get('/checkout/pincode-lookup', [CheckoutController::class, 'lookupPincode'])->middleware('throttle:60,1')->name('checkout.pincode-lookup');
Route::post('/checkout/validate-coupon', [CheckoutController::class, 'validateCoupon'])->name('checkout.validate-coupon');
Route::post('/checkout/calculate', [CheckoutController::class, 'calculateTotals'])->name('checkout.calculate');
Route::post('/checkout/create-order', [CheckoutController::class, 'createOrder'])->name('checkout.create-order');
Route::post('/checkout/verify-payment', [CheckoutController::class, 'verifyPayment'])->name('checkout.verify-payment');
Route::get('/orders/{orderNumber}/confirmation', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');

// Invoices Download
Route::get('/invoices/download/{token}', [InvoiceDownloadController::class, 'downloadByToken'])->name('invoices.download.token');
Route::get('/account/orders/{orderNumber}/invoice', [InvoiceDownloadController::class, 'downloadForCustomer'])->middleware('auth')->name('account.orders.invoice');

// Razorpay Webhook
Route::post('/api/webhooks/razorpay', [RazorpayWebhookController::class, 'handle'])->name('webhooks.razorpay');

/*
|--------------------------------------------------------------------------
| Customer Authentication & Google OAuth
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/customer/login', [CustomerAuthController::class, 'showLoginForm'])->name('customer.login');
    Route::post('/customer/login', [CustomerAuthController::class, 'login'])->name('customer.login.submit');
    Route::get('/customer/register', [CustomerAuthController::class, 'showRegisterForm'])->name('customer.register');
    Route::post('/customer/register', [CustomerAuthController::class, 'register'])->name('customer.register.submit');
    Route::get('/auth/google', [CustomerAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [CustomerAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::middleware('auth')->group(function () {
    Route::post('/customer/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');
    Route::get('/account', fn () => redirect()->route('account.orders'))->name('account.dashboard');
    Route::get('/account/orders', [CustomerAccountController::class, 'orders'])->name('account.orders');
    Route::get('/account/orders/{orderNumber}', [CustomerAccountController::class, 'showOrder'])->name('account.orders.show');
});

/*
|--------------------------------------------------------------------------
| Admin Authentication Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Protected Admin Panel Routes (RBAC)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['web', 'admin'])->group(function () {
    // Overview Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/notifications/poll', [AdminNotificationController::class, 'poll'])->name('notifications.poll');

    // Products Management
    Route::resource('products', AdminProductController::class)->except(['show']);

    // Categories Management
    Route::resource('categories', AdminCategoryController::class)->except(['show']);
    Route::patch('/categories/{category}/toggle-status', [AdminCategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

    // Module 2: Order Management & Invoice Download
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::get('/orders/{order}/invoice', [AdminOrderController::class, 'downloadInvoice'])->name('orders.invoice');

    // Module 1: Offer & Coupon Management
    Route::resource('coupons', AdminCouponController::class)->except(['show']);
    Route::patch('/coupons/{coupon}/toggle', [AdminCouponController::class, 'toggle'])->name('coupons.toggle');

    // Module 2: Payment Settings & GST Invoice Management
    Route::get('/settings/payment', [AdminPaymentSettingController::class, 'index'])->name('settings.payment');
    Route::post('/settings/payment', [AdminPaymentSettingController::class, 'update'])->name('settings.payment.update');
    Route::get('/invoices', [AdminInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [AdminInvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/download', [AdminInvoiceController::class, 'download'])->name('invoices.download');

    // Module 3: Inventory & Stock Management
    Route::get('/stock', [AdminStockController::class, 'index'])->name('stock.index');
    Route::post('/stock/{product}/adjust', [AdminStockController::class, 'adjust'])->name('stock.adjust');
    Route::get('/stock/movements', [AdminStockController::class, 'movements'])->name('stock.movements');
    Route::get('/stock/analysis', [AdminStockController::class, 'analysis'])->name('stock.analysis');

    // Module 4: Unified Customer Leads Management
    Route::get('/leads', [AdminCustomerLeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/export', [AdminCustomerLeadController::class, 'export'])->name('leads.export');
    Route::get('/leads/{lead}', [AdminCustomerLeadController::class, 'show'])->name('leads.show');

    // Customer Inquiries / Custom Requests & Email Replies
    Route::get('/inquiries', [AdminInquiryController::class, 'index'])->name('inquiries.index');
    Route::get('/inquiries/{inquiry}', [AdminInquiryController::class, 'show'])->name('inquiries.show');
    Route::post('/inquiries/{inquiry}/reply', [AdminInquiryController::class, 'reply'])->name('inquiries.reply');
    Route::patch('/inquiries/{inquiry}/status', [AdminInquiryController::class, 'updateStatus'])->name('inquiries.status');
    Route::delete('/inquiries/{inquiry}', [AdminInquiryController::class, 'destroy'])->name('inquiries.destroy');

    // User & Team Management & Shipping Settings (Restricted to Administrator role only)
    Route::middleware('admin:admin')->group(function () {
        Route::get('/settings/shipping', [AdminShippingSettingController::class, 'index'])->name('settings.shipping');
        Route::post('/settings/shipping', [AdminShippingSettingController::class, 'update'])->name('settings.shipping.update');
        Route::resource('users', AdminUserController::class)->except(['show']);
    });
});

/*
|--------------------------------------------------------------------------
| System Maintenance & Cache Utilities
|--------------------------------------------------------------------------
*/
Route::get('/system/clear-cache', function () {
    Artisan::call('optimize:clear');
    $output = Artisan::output();

    return response()->json([
        'success' => true,
        'message' => 'Cache, config, route, and view caches successfully cleared.',
        'database_connection' => config('database.default'),
        'database_name' => config('database.connections.'.config('database.default').'.database'),
        'output' => $output,
    ]);
})->name('system.clear-cache');
