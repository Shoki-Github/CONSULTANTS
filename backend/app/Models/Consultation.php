<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Consultation extends Model
{
    protected $fillable = [
        'reference', 'first_name', 'last_name', 'email', 'phone', 'message', 'status', 'assigned_to',
    ];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
