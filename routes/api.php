<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\StylistController;
use App\Http\Controllers\AdminController; // Make sure this is present

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

Route::get('/stylists', [StylistController::class, 'index']); // Public Stylists route

// Authenticated routes (require a logged-in user)
Route::middleware('auth:sanctum')->group(function () {
    // Basic User Routes (Logout, User Profile)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'authenticatedUser']);
    Route::put('/user', [AuthController::class, 'updateProfile']);
    Route::patch('/user', [AuthController::class, 'updateProfile']);
    Route::delete('/user', [AuthController::class, 'deleteAccount']);

    // Client-specific routes
    Route::put('/client/profile', [ClientController::class, 'update']);
    Route::patch('/client/profile', [ClientController::class, 'update']);
    Route::post('/client/choose-stylist', [ClientController::class, 'chooseStylist']);

    // Stylist-specific routes
    Route::put('/stylist/profile', [StylistController::class, 'update']);
    Route::patch('/stylist/profile', [StylistController::class, 'update']);
    Route::get('/stylist/clients', [StylistController::class, 'myClients']);

    // --- ADMIN SPECIFIC ROUTES 
    Route::middleware('can:access-admin-dashboard')->group(function () {
        // Clients Management
        Route::get('/admin/clients', [AdminController::class, 'getAllClients']);
        Route::get('/admin/clients/{client}', [AdminController::class, 'getClientDetail']);
        Route::put('/admin/clients/{client}', [AdminController::class, 'updateClient']);
        Route::delete('/admin/clients/{client}', [AdminController::class, 'deleteClient']);

        // Stylists Management
        Route::get('/admin/stylists', [AdminController::class, 'getAllStylists']);
        Route::get('/admin/stylists/{stylist}', [AdminController::class, 'getStylistDetail']);
        Route::put('/admin/stylists/{stylist}', [AdminController::class, 'updateStylist']);
        Route::delete('/admin/stylists/{stylist}', [AdminController::class, 'deleteStylist']);
    });
});