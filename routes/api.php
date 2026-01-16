<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SimpelsTestController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API for SIMPels integration
Route::prefix('simpels')->group(function () {
    // Withdrawal status updates from SIMPels
    Route::put('/withdrawal/{withdrawal_number}/status', [\App\Http\Controllers\Api\WithdrawalStatusController::class, 'updateStatus']);
    Route::get('/withdrawal/{withdrawal_number}', [\App\Http\Controllers\Api\WithdrawalStatusController::class, 'getWithdrawal']);
    
    // Testing endpoints (can be used by frontend AJAX)
    Route::get('/test-connection', [SimpelsTestController::class, 'testConnection']);
    Route::get('/test-santri/{uid}', [SimpelsTestController::class, 'testSantriLookup']);
    Route::post('/test-transaction', [SimpelsTestController::class, 'testTransaction']);
    Route::get('/test-all-santri', [SimpelsTestController::class, 'testAllSantri']);
});