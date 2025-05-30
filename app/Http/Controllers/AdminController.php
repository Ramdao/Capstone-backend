<?php

namespace App\Http\Controllers;
use App\Models\Client;
use App\Models\Stylist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function __construct()
    {
        
        $this->middleware('can:access-admin-dashboard');
    }

    /**
     * Get all clients.
     * Includes their associated user data.
     */
    public function getAllClients()
    {
        $clients = Client::with('user')->get();

        return response()->json([
            'message' => 'All clients fetched successfully.',
            'clients' => $clients
        ]);
    }

    /**
     * Get a specific client's details.
     */
    public function getClientDetail(Client $client)
    {
        $client->load('user'); // Eager load the associated user

        return response()->json([
            'message' => 'Client details fetched successfully.',
            'client' => $client
        ]);
    }

    /**
     * Update a client's user and/or client profile data.
     */
    public function updateClient(Request $request, Client $client)
    {
        $user = $client->user;

        if (!$user) {
            return response()->json(['message' => 'Associated user not found for client.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'country' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:255',
            'body_type' => 'sometimes|string|max:255',
            'colors' => 'nullable|json',
            'message_to_stylist' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            if ($request->has('name') || $request->has('email') || $request->has('password')) {
                $userUpdateData = [];
                if ($request->has('name')) {
                    $userUpdateData['name'] = $request->name;
                }
                if ($request->has('email')) {
                    $userUpdateData['email'] = $request->email;
                }
                if ($request->filled('password')) {
                    $userUpdateData['password'] = Hash::make($request->password);
                }
                if (!empty($userUpdateData)) {
                    $user->update($userUpdateData);
                }
            }

            $clientUpdateData = [];
            if ($request->has('country')) {
                $clientUpdateData['country'] = $request->country;
            }
            if ($request->has('city')) {
                $clientUpdateData['city'] = $request->city;
            }
            if ($request->has('body_type')) {
                $clientUpdateData['body_type'] = $request->body_type;
            }
            if ($request->has('colors')) {
                $clientUpdateData['colors'] = $request->colors;
            }
            if ($request->has('message_to_stylist')) {
                $clientUpdateData['message_to_stylist'] = $request->message_to_stylist;
            }

            if (!empty($clientUpdateData)) {
                $client->update($clientUpdateData);
            }

            DB::commit();
            return response()->json(['message' => 'Client account updated successfully!'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update client account.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a client and their associated user account.
     */
    public function deleteClient(Client $client)
    {
        DB::beginTransaction();
        try {
            $user = $client->user;

            if ($user) {
                $client->delete();
                $user->delete();
            } else {
                $client->delete();
            }

            DB::commit();
            return response()->json(['message' => 'Client account deleted successfully!'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete client account.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all stylists.
     * Includes their associated user data.
     */
    public function getAllStylists()
    {
        $stylists = Stylist::with('user')->get();

        return response()->json([
            'message' => 'All stylists fetched successfully.',
            'stylists' => $stylists
        ]);
    }

    /**
     * Get a specific stylist's details.
     */
    public function getStylistDetail(Stylist $stylist)
    {
        $stylist->load('user');

        return response()->json([
            'message' => 'Stylist details fetched successfully.',
            'stylist' => $stylist
        ]);
    }

    /**
     * Update a stylist's user and/or stylist profile data.
     */
    public function updateStylist(Request $request, Stylist $stylist)
    {
        $user = $stylist->user;

        if (!$user) {
            return response()->json(['message' => 'Associated user not found for stylist.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            if ($request->has('name') || $request->has('email') || $request->has('password')) {
                $userUpdateData = [];
                if ($request->has('name')) {
                    $userUpdateData['name'] = $request->name;
                }
                if ($request->has('email')) {
                    $userUpdateData['email'] = $request->email;
                }
                if ($request->filled('password')) {
                    $userUpdateData['password'] = Hash::make($request->password);
                }
                if (!empty($userUpdateData)) {
                    $user->update($userUpdateData);
                }
            }

            // Corrected: Removed reference to undefined $stylistUpdateData
            // If your stylist model has other fields, they would be updated here.
            // For a simple stylist model (only user_id), no further update is needed here.

            DB::commit();
            return response()->json(['message' => 'Stylist account updated successfully!'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update stylist account.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a stylist and their associated user account.
     */
    public function deleteStylist(Stylist $stylist)
    {
        DB::beginTransaction();
        try {
            $user = $stylist->user;

            if ($user) {
                $stylist->delete();
                $user->delete();
            } else {
                $stylist->delete();
            }

            DB::commit();
            return response()->json(['message' => 'Stylist account deleted successfully!'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete stylist account.', 'error' => $e->getMessage()], 500);
        }
    }
}