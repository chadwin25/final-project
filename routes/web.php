<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffCustomerOrderController;
use App\Http\Middleware\EnsureCustomerIsAuthenticated;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('landing');
})->name('landing');

Route::get('/customer/signup', [CustomerPortalController::class, 'showSignup'])
    ->name('customer.signup');
Route::post('/customer/signup', [CustomerPortalController::class, 'signup'])
    ->middleware('throttle:5,1')
    ->name('customer.signup.submit');

Route::get('/customer/login', [CustomerPortalController::class, 'showLogin'])
    ->name('customer.login');
Route::post('/customer/login', [CustomerPortalController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('customer.login.submit');

Route::middleware(EnsureCustomerIsAuthenticated::class)->prefix('customer')->name('customer.')->group(function () {
    Route::get('/', [CustomerPortalController::class, 'home'])->name('home');
    Route::get('/orders', [CustomerPortalController::class, 'orders'])->name('orders');
    Route::post('/cart/{product}', [CustomerPortalController::class, 'addToCart'])->name('cart.add');
    Route::put('/cart/{product}', [CustomerPortalController::class, 'updateCart'])->name('cart.update');
    Route::post('/checkout', [CustomerPortalController::class, 'checkout'])->name('checkout');
    Route::post('/logout', [CustomerPortalController::class, 'logout'])->name('logout');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.submit');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/sales/create', [SaleController::class, 'create'])
        ->name('sales.create');

    Route::get('/sales/history', [SaleController::class, 'index'])
        ->name('sales.history');

    Route::post('/sales', [SaleController::class, 'store'])
        ->name('sales.store');

    Route::get('/reports/sales', [ReportController::class, 'sales'])
        ->name('reports.sales');

    Route::get('/reports/inventory', [ReportController::class, 'inventory'])
        ->name('reports.inventory');

    Route::get('/customer-orders', [StaffCustomerOrderController::class, 'index'])
        ->name('customer-orders.index');

    Route::patch('/customer-orders/{customerOrder}', [StaffCustomerOrderController::class, 'update'])
        ->name('customer-orders.update');

    Route::patch('/products/{product}/restore', [ProductController::class, 'restore'])
        ->name('products.restore');

    Route::resource('products', ProductController::class)->except('show');
    Route::resource('customers', CustomerController::class)->except('show');

    Route::patch('/staff/{staff}/restore', [StaffController::class, 'restore'])
        ->name('staff.restore');

    Route::resource('staff', StaffController::class)->except('show');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});