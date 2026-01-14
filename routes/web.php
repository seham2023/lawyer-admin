<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TabbyCallbackController;

Route::get('/', function () {
    // return view('profile');
    return redirect()->route('filament.admin.auth.login');
});

// Tabby Payment Callback Routes
Route::prefix('tabby/payment')->name('tabby.payment.')->group(function () {
    Route::get('/success', [TabbyCallbackController::class, 'success'])->name('success');
    Route::get('/cancel', [TabbyCallbackController::class, 'cancel'])->name('cancel');
    Route::get('/failure', [TabbyCallbackController::class, 'failure'])->name('failure');
});

// Payment After-Pay Routes
Route::prefix('payment')->name('payment.')->group(function () {
    Route::get('/{paymentId}/success', [\App\Http\Controllers\PaymentController::class, 'success'])->name('success');
    Route::get('/{paymentId}/pending', [\App\Http\Controllers\PaymentController::class, 'pending'])->name('pending');
    Route::get('/{paymentId}/failed', [\App\Http\Controllers\PaymentController::class, 'failed'])->name('failed');
    Route::get('/error', [\App\Http\Controllers\PaymentController::class, 'error'])->name('error');
    Route::get('/{paymentId}/status', [\App\Http\Controllers\PaymentController::class, 'checkStatus'])->name('status');
});
