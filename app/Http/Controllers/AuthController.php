<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client; // Make sure to import Client model
use App\Models\Stylist; // Make sure to import Stylist model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email|max:255',
            'password' => 'required|string|confirmed|min:8',
            'role' => 'required|in:client,stylist,admin', // Role is now required
            // Client specific fields (nullable, only if role is 'client')
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'body_type' => 'nullable|string|max:255',
            'colors' => 'nullable|json', // Expect JSON string from frontend
            // stylist_id for clients is chosen later, not at registration
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
            'role' => $fields['role'],
        ]);

        // Create the specific profile based on the role
        if ($user->role === 'client') {
            $user->client()->create([
                'country' => $fields['country'] ?? null,
                'city' => $fields['city'] ?? null,
                'body_type' => $fields['body_type'] ?? null,
                'colors' => $fields['colors'] ?? null // Store as JSON string, Laravel's cast will handle
            ]);
        } elseif ($user->role === 'stylist') {
            $user->stylist()->create([]); // No specific fields for stylist profile yet
        }
        // Admin role doesn't need a specific profile table based on your description

        // Eager load the profile for the response
        if ($user->role === 'client') {
            $user->load('client');
        } elseif ($user->role === 'stylist') {
            $user->load('stylist');
        }

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
        // Eager load the profile for the response after login
        if ($user->role === 'client') {
            $user->load('client.stylist.user'); // Load stylist's user for chosen stylist's name
        } elseif ($user->role === 'stylist') {
            $user->load('stylist.clients.user'); // Load clients' users for their names
        }

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
     * Get the currently authenticated user's details and their specific profile.
     * This method is called by the frontend's fetchAuthenticatedUser.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticatedUser(Request $request)
    {
        $user = $request->user();
        // Load the specific profile based on role, eager load nested relationships
        if ($user->role === 'client') {
            $user->load('client.stylist.user'); // Load client profile, and if a stylist is chosen, load their user details
        } elseif ($user->role === 'stylist') {
            $user->load('stylist.clients.user'); // Load stylist profile, and their clients' user details
        }
        return response()->json($user); // Return the user object directly
    }

    /**
     * Update the authenticated user's profile (general user fields and client profile fields).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validationRules = [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
        ];

        // Add client-specific validation rules based on role
        if ($user->role === 'client') {
            $validationRules['country'] = 'nullable|string|max:255';
            $validationRules['city'] = 'nullable|string|max:255';
            $validationRules['body_type'] = 'nullable|string|max:255';
            $validationRules['colors'] = 'nullable|json'; // CHANGED: Expect JSON string
        }
        // Stylist currently has no specific updatable fields, so no extra rules needed

        $fields = $request->validate($validationRules);

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
        $user->save();

        // Update specific profile fields
        if ($user->role === 'client' && $user->client) {
            $clientProfile = $user->client;
            if (isset($fields['country'])) $clientProfile->country = $fields['country'];
            if (isset($fields['city'])) $clientProfile->city = $fields['city'];
            if (isset($fields['body_type'])) $clientProfile->body_type = $fields['body_type'];
            if (isset($fields['colors'])) $clientProfile->colors = $fields['colors']; // Laravel's cast will handle JSON string to array
            $clientProfile->save();
            // Eager load client profile for response, including stylist user
            $user->load('client.stylist.user');
        } elseif ($user->role === 'stylist' && $user->stylist) {
            // If stylist had updatable fields, they would be handled here
            // For now, just ensure the stylist profile is loaded if needed for response
            $user->load('stylist.clients.user');
        }

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

        // Optional: Delete associated profiles (e.g., Client, Stylist) first
        if ($user->role === 'client' && $user->client) {
            $user->client->delete();
        } elseif ($user->role === 'stylist' && $user->stylist) {
            $user->stylist->delete();
        }

        $user->tokens()->delete(); // Delete all Sanctum tokens for the user
        Auth::guard('web')->logout(); // Log out the user before deleting their account
        // $request->session()->invalidate(); // These are for web guard, not typically needed for API token logout
        // $request->session()->regenerateToken();

        $user->delete(); // Delete the user record

        return response()->json(['message' => 'Account deleted successfully']);
    }
}