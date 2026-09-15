<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OauthProvider extends Model
{
    protected $table = 'oauth_providers';

    protected $fillable = [
        'name',
        'provider',
        'client_id',
        'client_secret',
        'redirect',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
