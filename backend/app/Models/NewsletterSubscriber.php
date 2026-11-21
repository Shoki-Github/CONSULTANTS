<?php

namespace App\Models;

use Database\Factories\NewsletterSubscriberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    /** @use HasFactory<NewsletterSubscriberFactory> */
    use HasFactory;

    protected $fillable = [
        'email',
        'name',
        'organisation',
        'source',
        'consented_at',
        'unsubscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'consented_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }
}
