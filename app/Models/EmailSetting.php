<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailSetting extends Model
{
    protected $fillable = [
        'mailer',
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'from_name',
        'from_email',
        'reply_to_name',
        'reply_to_email',
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'is_active' => 'boolean',
        'port' => 'integer',
    ];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}