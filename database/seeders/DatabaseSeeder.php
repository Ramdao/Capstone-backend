<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; 

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       
        // It's good practice to use firstOrCreate to prevent duplicates if you run the seeder multiple times
        User::firstOrCreate(
            ['email' => 'admin@example.com'], 
            [
                'name' => 'Admin User',
                'password' => Hash::make('test1234'), 
                'role' => 'admin', 
                'email_verified_at' => now(), 
            ]
        );

       

        
    }
}