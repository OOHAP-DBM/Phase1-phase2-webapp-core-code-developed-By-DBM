<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingPaymentInformation extends Model
{
    protected $table = 'pricing_payment_information';

    protected $fillable = [
        'title',
        'content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
