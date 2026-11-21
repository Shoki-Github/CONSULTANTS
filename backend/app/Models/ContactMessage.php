<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    protected $fillable = [
        'reference',
        'name',
        'organisation',
        'organisation_type',
        'email',
        'phone',
        'service_of_interest',
        'preferred_language',
        'subject',
        'message',
        'status',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
