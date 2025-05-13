<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Example test route
Route::get('/users', function () {
    return \App\Models\User::all();
});
