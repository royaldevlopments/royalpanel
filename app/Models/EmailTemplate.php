<?php

namespace RoyalPanel\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $table = 'email_templates';

    protected $fillable = [
        'template_key',
        'subject',
        'greeting',
        'body',
        'action_text',
        'action_url',
        'level',
        'outro',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];
}
