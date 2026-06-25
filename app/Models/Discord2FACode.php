<?php

namespace RoyalPanel\Models;

use Illuminate\Database\Eloquent\Model;

class Discord2FACode extends Model
{
    protected $table = 'discord_2fa_codes';

    protected $fillable = [
        'user_id',
        'discord_id',
        'code',
        'expires_at',
        'sent',
        'used',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'expires_at' => 'datetime',
        'sent' => 'boolean',
        'used' => 'boolean',
    ];
}
