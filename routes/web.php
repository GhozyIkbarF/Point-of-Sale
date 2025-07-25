<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;


Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('Auth/Login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        
        Route::resource('products', ProductController::class);
        
        // Admin Reports Routes
        Route::get('reports/admin', [ReportController::class, 'adminReport'])->name('reports.admin');
        Route::get('reports/admin/export', [ReportController::class, 'exportAdminReport'])->name('reports.admin.export');
        
        // User Management Routes
        Route::get('setting', [UserController::class, 'index'])->name('setting.index');
        Route::post('setting', [UserController::class, 'store'])->name('setting.store');
        Route::put('setting/{user}', [UserController::class, 'update'])->name('setting.update');
        Route::delete('setting/{user}', [UserController::class, 'destroy'])->name('setting.destroy');
    });
    
    Route::middleware(['role:admin,cashier'])->group(function () {
        Route::resource('sales', SaleController::class);
        
        // Additional sales routes
        Route::get('sales-all', [SaleController::class, 'allSales'])->name('sales.all');
        Route::get('sales-my', [SaleController::class, 'mySales'])->name('sales.my');
        
        // Cashier Reports Routes (daily only)
        Route::get('reports', [ReportController::class, 'cashierReport'])->name('reports.index');
        Route::get('reports/export/excel', [ReportController::class, 'exportCashierReport'])->name('reports.export.excel');
        Route::get('reports/export/pdf', [ReportController::class, 'exportCashierReport'])->name('reports.export.pdf');
    });
});

require __DIR__.'/auth.php';
