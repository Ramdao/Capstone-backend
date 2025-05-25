<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController; // <-- ADD THIS LINE
use App\Http\Controllers\StylistController; // <-- ADD THIS LINE

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

// Publicly accessible Stylists route (as discussed, generally preferred)
Route::get('/stylists', [StylistController::class, 'index']); // <-- New route for fetching stylists

// Authenticated routes (require a logged-in user)
Route::middleware('auth:sanctum')->group(function () {
    // Logout route
    Route::post('/logout', [AuthController::class, 'logout']);

    // Get all users (accessible to any authenticated user, you might add role-based middleware later)
    Route::get('/users', function (Request $request) {
        return \App\Models\User::all();
    });

    // Get the currently authenticated user's details (can stay in AuthController if it's general user info)
    Route::get('/user', [AuthController::class, 'authenticatedUser']); // <-- Moving to a dedicated method in AuthController
    // Note: If this just returns $request->user(), it's fine as an anonymous function too.
    // But if it loads profiles, a dedicated method is cleaner.

    // Update the currently authenticated user's profile
    // Now handled by ClientController or StylistController based on role, OR
    // AuthController for common user fields, and then specialized controllers for profile fields.
    Route::put('/user', [AuthController::class, 'updateProfile']); // Keep general user update here
    Route::patch('/user', [AuthController::class, 'updateProfile']);

    // Route for client-specific profile update (e.g., if client details are separate from user's name/email)
    Route::put('/client/profile', [ClientController::class, 'update']);
    Route::patch('/client/profile', [ClientController::class, 'update']);

    // Route for stylist-specific profile update (if stylist details are separate)
    Route::put('/stylist/profile', [StylistController::class, 'update']);
    Route::patch('/stylist/profile', [StylistController::class, 'update']);


    // Client-specific actions
    // Route for a client to choose a stylist
    Route::post('/client/choose-stylist', [ClientController::class, 'chooseStylist']); // <-- New route

    // Stylist-specific actions
    // Route for a stylist to view their clients
    Route::get('/stylist/clients', [StylistController::class, 'myClients']); // <-- New route


    // Delete the currently authenticated user's account
    Route::delete('/user', [AuthController::class, 'deleteAccount']);
});