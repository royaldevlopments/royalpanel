<?php

namespace RoyalPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OAuthLink extends Model
{
    protected $table = 'oauth_links';

    protected $fillable = [
        'user_id', 'provider', 'provider_id', 'avatar', 'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
