<?php

namespace RoyalPanel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscordLink extends Model
{
    protected $table = 'discord_links';

    protected $fillable = [
        'discord_id',
        'user_id',
        'code',
        'expires_at',
        'linked_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'linked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
