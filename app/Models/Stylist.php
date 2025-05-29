<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stylist extends Model
{
    use HasFactory;

    protected $fillable = ['user_id']; 

    /**
     * Get the user that owns the stylist profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the clients for the stylist.
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
}