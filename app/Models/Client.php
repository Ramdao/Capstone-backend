<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Client extends Model
{
    use HasFactory;

    // Ensure 'colors' is cast to array/json
    protected $casts = [
        'colors' => 'array',
    ];

    protected $fillable = [
        'user_id',
        'stylist_id',
        'country',
        'city',
        'body_type',
        'colors',
        'message_to_stylist',
    ];

    /**
     * Get the user that owns the client profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the stylist that the client has chosen.
     */
    public function stylist(): BelongsTo
    {
        return $this->belongsTo(Stylist::class);
    }
}