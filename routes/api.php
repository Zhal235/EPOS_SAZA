<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API for SIMPels integration
Route::prefix('simpels')->group(function () {
    // Withdrawal status updates from SIMPels
    Route::put('/withdrawal/{withdrawal_number}/status', [\App\Http\Controllers\Api\WithdrawalStatusController::class, 'updateStatus']);
    Route::get('/withdrawal/{withdrawal_number}', [\App\Http\Controllers\Api\WithdrawalStatusController::class, 'getWithdrawal']);
});