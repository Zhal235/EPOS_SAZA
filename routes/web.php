<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\PosTerminal;
use App\Livewire\Products;
use App\Livewire\Dashboard;
use App\Livewire\TransactionHistory;
use App\Livewire\SalesReport;

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
    
    // Customer routes
    Route::get('customers', \App\Livewire\Customers::class)->name('customers');
    
    // Staff Management routes (Admin only)
    Route::get('staff', \App\Livewire\StaffManagement::class)->name('staff')->middleware('can:access-admin');
    
    
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
