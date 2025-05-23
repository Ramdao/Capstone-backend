<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use App\Http\Controllers\AuthController; 

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

// Authenticated routes (require a logged-in user)
Route::middleware('auth:sanctum')->group(function () {
    // Logout route
    Route::post('/logout', [AuthController::class, 'logout']);

    // Get all users (accessible to any authenticated user, you might add role-based middleware later)
    Route::get('/users', function (Request $request) {
        return \App\Models\User::all();
    });

    // Get the currently authenticated user's details
    Route::get('/user', function (Request $request) {
        return $request->user(); // Returns the authenticated user object
    });

    // Update the currently authenticated user's profile
    Route::put('/user', [AuthController::class, 'updateProfile']);
    Route::patch('/user', [AuthController::class, 'updateProfile']); // PATCH is also common for partial updates

    // Delete the currently authenticated user's account
    Route::delete('/user', [AuthController::class, 'deleteAccount']);
});