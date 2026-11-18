<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

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

    Route::post('/sales', [SaleController::class, 'store'])
        ->name('sales.store');

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