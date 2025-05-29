<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\StylistController;
use App\Http\Controllers\AdminController; 

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

// Publicly accessible Stylists route (e.g., for clients to view all stylists)
Route::get('/stylists', [StylistController::class, 'index']);

// Authenticated routes (require a logged-in user)
Route::middleware('auth:sanctum')->group(function () {
    // Logout route
    Route::post('/logout', [AuthController::class, 'logout']);

    // Get the currently authenticated user's general details
    Route::get('/user', [AuthController::class, 'authenticatedUser']);

    // Update the currently authenticated user's general profile (name, email, password)
    Route::put('/user', [AuthController::class, 'updateProfile']);
    Route::patch('/user', [AuthController::class, 'updateProfile']);

    // Delete the currently authenticated user's account
    Route::delete('/user', [AuthController::class, 'deleteAccount']);

    // Client-specific profile updates
    Route::put('/client/profile', [ClientController::class, 'update']);
    Route::patch('/client/profile', [ClientController::class, 'update']);

    // Client-specific actions
    Route::post('/client/choose-stylist', [ClientController::class, 'chooseStylist']);

    // Stylist-specific profile updates
    Route::put('/stylist/profile', [StylistController::class, 'update']);
    Route::patch('/stylist/profile', [StylistController::class, 'update']);

    // Stylist-specific actions
    Route::get('/stylist/clients', [StylistController::class, 'myClients']);

   

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