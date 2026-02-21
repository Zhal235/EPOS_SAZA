<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\PosTerminal;
use App\Livewire\Products;
use App\Livewire\Dashboard;
use App\Livewire\TransactionHistory;
use App\Livewire\SalesReport;
use App\Livewire\TenantManagement;
use App\Http\Controllers\SimpelsTestController;

// Root route - redirect based on authentication status
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');



// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard and Core Features
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('pos', PosTerminal::class)->name('pos');
    Route::get('products', Products::class)->name('products');
    Route::get('categories', \App\Livewire\Categories::class)->name('categories');
    Route::get('transactions', TransactionHistory::class)->name('transactions');
    
    // Reports routes
    Route::get('sales-report', SalesReport::class)->name('sales.report');
    
    // Financial routes (Admin only)
    Route::get('financial', \App\Livewire\Financial::class)->name('financial')->middleware('can:access-admin');
    
    // Staff Management routes (Admin only)
    Route::get('staff', \App\Livewire\StaffManagement::class)->name('staff')->middleware('can:access-admin');

    // Foodcourt Tenant Management routes (Admin only)
    Route::get('tenants', TenantManagement::class)->name('tenants')->middleware('can:access-admin');
    Route::get('foodcourt-finance', \App\Livewire\FoodcourtFinance::class)->name('foodcourt.finance')->middleware('can:access-admin');
    
    
    // Profile routes
    Route::view('profile', 'profile')->name('profile');
    
    // Download template route
    Route::get('products/download-template', function() {
        $fileName = 'Product_Import_Template_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ProductTemplateExport, 
            $fileName
        );
    })->name('products.download-template');
});

// Auth routes
require __DIR__.'/auth.php';

// SIMPELS Integration Test Routes (Development Only)
Route::prefix('simpels')->group(function () {
    Route::get('test-connection', [SimpelsTestController::class, 'testConnection'])->name('simpels.test.connection');
    Route::get('test-santri/{uid}', [SimpelsTestController::class, 'testSantriLookup'])->name('simpels.test.santri');
    Route::post('test-transaction', [SimpelsTestController::class, 'testTransaction'])->name('simpels.test.transaction');
    Route::get('test-all-santri', [SimpelsTestController::class, 'testAllSantri'])->name('simpels.test.all-santri');
    Route::get('get-sample-santri', [SimpelsTestController::class, 'getSampleSantri'])->name('simpels.get.sample-santri');
    Route::get('dashboard', [SimpelsTestController::class, 'dashboard'])->name('simpels.dashboard');
});
