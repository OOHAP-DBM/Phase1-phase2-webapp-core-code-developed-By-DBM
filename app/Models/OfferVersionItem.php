<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Enquiries\Models\EnquiryItem;

class OfferVersionItem extends Model
{
    use HasFactory;

    protected $table = 'offer_version_items';

    protected $fillable = [
        'offer_version_id',
        'enquiry_item_id',
        'hoarding_id',
        'hoarding_type',
        'package_id',
        'package_type',
        'package_label',
        'start_date',
        'end_date',
        'duration_months',
        'unit_price',
        'discount_amount',
        'tax_amount',
        'final_price',
        'services',
        'meta',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'services' => 'array',
        'meta' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Parent offer version.
     */
    public function offerVersion(): BelongsTo
    {
        return $this->belongsTo(
            OfferVersion::class,
            'offer_version_id'
        );
    }

    /**
     * Original enquiry item.
     *
     * This can be NULL when the hoarding was newly added
     * during offer negotiation.
     */
    public function enquiryItem(): BelongsTo
    {
        return $this->belongsTo(
            EnquiryItem::class,
            'enquiry_item_id'
        );
    }

    /**
     * Hoarding.
     */
    public function hoarding(): BelongsTo
    {
        return $this->belongsTo(
            Hoarding::class,
            'hoarding_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function isFromOriginalEnquiry(): bool
    {
        return !is_null($this->enquiry_item_id);
    }

    public function isNewlyAdded(): bool
    {
        return is_null($this->enquiry_item_id);
    }
}
