<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client; 
use App\Models\User; 
use App\Models\Stylist; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    /**
     * Update the authenticated client's profile.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Ensure the authenticated user is a client
        if ($user->role !== 'client') {
            return response()->json(['message' => 'Unauthorized. Only clients can update their client profile.'], 403);
        }

        $rules = [
            'country' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'body_type' => ['nullable', 'string', 'max:255'],
            'colors' => ['nullable', 'string', 'max:255'], 
            'message_to_stylist' => ['nullable', 'string', 'max:255']
        ];

        $validatedData = $request->validate($rules);

        $client = $user->client; 

        if (!$client) {
            return response()->json(['message' => 'Client profile not found.'], 404);
        }

        // Update client profile fields
        if (isset($validatedData['country'])) {
            $client->country = $validatedData['country'];
        }
        if (isset($validatedData['city'])) {
            $client->city = $validatedData['city'];
        }
        if (isset($validatedData['body_type'])) {
            $client->body_type = $validatedData['body_type'];
        }
        if (isset($validatedData['colors'])) {
            $client->colors = $validatedData['colors'];
        }
        if (isset($validatedData['message_to_stylist'])) {
            $client->message_to_stylist = $validatedData['message_to_stylist'];
        }
           
        
        $client->save();

        return response()->json(['message' => 'Client profile updated successfully!', 'client' => $client], 200);
    }


    /**
     * Allows an authenticated client to choose a stylist.
     */
    public function chooseStylist(Request $request)
    {
        $user = $request->user();

        // Ensure the authenticated user is a client
        if ($user->role !== 'client') {
            return response()->json(['message' => 'Only clients can choose a stylist.'], 403);
        }

        $validated = $request->validate([
            'stylist_id' => ['required', 'exists:stylists,id'], // Validate that stylist_id exists in the stylists table
        ]);

        $client = $user->client; // Get the client profile associated with the authenticated user

        if (!$client) {
            return response()->json(['message' => 'Client profile not found.'], 404);
        }

        $client->stylist_id = $validated['stylist_id'];
        $client->save();

        return response()->json(['message' => 'Stylist chosen successfully!'], 200);
    }
}