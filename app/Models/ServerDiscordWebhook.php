<?php

namespace RoyalPanel\Models;

use Illuminate\Database\Eloquent\Model;

class ServerDiscordWebhook extends Model
{
    protected $table = 'server_discord_webhooks';

    protected $fillable = [
        'server_id',
        'url',
        'events',
    ];

    protected $casts = [
        'events' => 'array',
    ];

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
}
