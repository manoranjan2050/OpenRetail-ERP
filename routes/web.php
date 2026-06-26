<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Redirect root to dashboard or login
Route::get('/', fn() => redirect()->route('dashboard'));

// Web installer
Route::prefix('install')->name('install.')->middleware('install.guard')->group(function () {
    Route::get('/', [InstallController::class, 'index'])->name('index');
    Route::post('/check', [InstallController::class, 'check'])->name('check');
    Route::post('/database', [InstallController::class, 'database'])->name('database');
    Route::post('/business', [InstallController::class, 'business'])->name('business');
    Route::post('/admin', [InstallController::class, 'admin'])->name('admin');
    Route::post('/finish', [InstallController::class, 'finish'])->name('finish');
});

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Products
    Route::get('/products/export', [ProductController::class, 'exportCsv'])->name('products.export');
    Route::post('/products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('products.adjust-stock');
    Route::resource('products', ProductController::class)->except(['show']);

    // Categories & Units (simple admin)
    Route::resource('categories', \App\Http\Controllers\CategoryController::class)->except(['show']);
    Route::resource('units', \App\Http\Controllers\UnitController::class)->except(['show']);

    // Customers
    Route::post('/customers/{customer}/payment', [CustomerController::class, 'recordPayment'])->name('customers.payment');
    Route::resource('customers', CustomerController::class);

    // Invoices / POS
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::post('/invoices/{invoice}/void', [InvoiceController::class, 'void'])->name('invoices.void');
    Route::resource('invoices', InvoiceController::class)->except(['edit', 'update', 'destroy']);

    // Settings (admin only)
    Route::middleware('permission:settings.view')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    // Audit log
    Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
});

require __DIR__.'/auth.php';
