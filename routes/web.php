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
