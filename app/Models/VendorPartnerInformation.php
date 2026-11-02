<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorPartnerInformation extends Model
{
    protected $table = 'vendor_partner_information';

    protected $fillable = [
        'title',
        'content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
