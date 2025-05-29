<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
// No need for 'use Illuminate\Database\Eloquent\Relations\HasOne;' here if you remove the type hint
// But keep if you use it elsewhere. For these methods, it's safer to omit.

use App\Models\Client; // Make sure this is present
use App\Models\Stylist; // Make sure this is present


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', 
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the client profile associated with the user.
     */
    public function client() 
    {
        return $this->hasOne(Client::class);
    }

    /**
     * Get the stylist profile associated with the user.
     */
    public function stylist() 
    {
        return $this->hasOne(Stylist::class);
    }
}