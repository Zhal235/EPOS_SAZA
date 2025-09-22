<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\PosTerminal;
use App\Livewire\Products;
use App\Livewire\Dashboard;

// Public routes
Route::view('/', 'welcome')->name('home');

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('pos', PosTerminal::class)->name('pos');
    Route::get('products', Products::class)->name('products');
    
    // Download template route
    Route::get('products/download-template', function() {
        $fileName = 'Product_Import_Template_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ProductTemplateExport, 
            $fileName
        );
    })->name('products.download-template');
});

// Profile routes
Route::middleware(['auth'])->group(function () {
    Route::view('profile', 'profile')->name('profile');
});

// Auth routes
require __DIR__.'/auth.php';
