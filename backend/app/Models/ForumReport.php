<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumReport extends Model
{
    protected $fillable = ['topic_id', 'post_id', 'reported_by', 'reason', 'status'];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
