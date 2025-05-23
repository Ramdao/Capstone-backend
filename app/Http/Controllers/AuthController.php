<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; 

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|confirmed',
            'role' => 'in:client,stylist,admin'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
            'role' => $fields['role'] ?? 'client',
        ]);

        // You might want to log the user in immediately after registration
        // Auth::login($user); // This would set the session for the newly registered user

        return response()->json(['user' => $user, 'message' => 'Registration successful'], 201);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($fields)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $request->session()->regenerate();

        $user = Auth::user(); // Get the authenticated user
        return response()->json(['message' => 'Logged in', 'user' => $user]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        // Get the currently authenticated user
        $user = Auth::user();

        // Validate the incoming request data
        $fields = $request->validate([
            'name' => 'sometimes|string|max:255', // 'sometimes' means field is optional
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                // Ensure email is unique, but ignore the current user's email
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed', // 'nullable' means password can be empty
            // Do NOT allow 'role' to be updated via this endpoint for security reasons.
            // Role changes should typically be handled by an admin interface.
        ]);

        // Update user fields
        if (isset($fields['name'])) {
            $user->name = $fields['name'];
        }
        if (isset($fields['email'])) {
            $user->email = $fields['email'];
        }
        if (isset($fields['password'])) {
            $user->password = Hash::make($fields['password']);
        }

        $user->save(); // Save the changes to the database

        return response()->json(['user' => $user, 'message' => 'Profile updated successfully']);
    }

    /**
     * Delete the authenticated user's account.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAccount()
    {
        $user = Auth::user(); // Get the currently authenticated user

        // Log out the user before deleting their account to invalidate their session
        Auth::guard('web')->logout();

        // Delete the user record from the database
        $user->delete();

        return response()->json(['message' => 'Account deleted successfully']);
    }
}