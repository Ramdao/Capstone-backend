<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stylist; 
use App\Models\User; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StylistController extends Controller
{
    /**
     * Display a listing of all stylists.
     */
    public function index()
    {
        // Fetch all stylists and eager load their associated user record
        // This ensures you have access to the user's name/email when fetching stylists
        $stylists = Stylist::with('user')->get();

        // Return them as a JSON response
        return response()->json(['stylists' => $stylists]);
    }

    /**
     * Update the authenticated stylist's profile.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Ensure the authenticated user is a stylist
        if ($user->role !== 'stylist') {
            return response()->json(['message' => 'Unauthorized. Only stylists can update their stylist profile.'], 403);
        }

        // Add validation rules specific to stylist profile if any
        $validated = $request->validate([
            
        ]);

        $stylist = $user->stylist; // Assuming a 'stylist' relationship on the User model

        if (!$stylist) {
            return response()->json(['message' => 'Stylist profile not found.'], 404);
        }

        

        return response()->json(['message' => 'Stylist profile updated successfully!', 'stylist' => $stylist], 200);
    }

    /**
     * Display a listing of clients associated with the authenticated stylist.
     */
    public function myClients(Request $request)
    {
        $user = $request->user();

        // Ensure the authenticated user is a stylist
        if ($user->role !== 'stylist') {
            return response()->json(['message' => 'Unauthorized. Only stylists can view their clients.'], 403);
        }

        // Load the stylist's profile and their associated clients
       
        $stylist = $user->load(['stylist.clients.user']); // user->stylist->clients->user

        // Access the clients through the stylist relationship
        $clients = $stylist->stylist->clients ?? collect(); // Use collect() for safety if no clients

        return response()->json(['clients' => $clients]);
    }
}