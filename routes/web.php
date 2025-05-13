<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('api')
    ->prefix('api')
    ->group(base_path('routes/api.php'));