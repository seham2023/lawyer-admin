<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view('profile');
    return redirect()->route('filament.admin.auth.login');
});
