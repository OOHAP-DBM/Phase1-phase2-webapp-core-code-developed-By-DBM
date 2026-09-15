<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfferVersion extends Model
{
    use HasFactory;

    protected $table = 'offer_versions';

    protected $fillable = [
        'offer_id',
        'version_number',
        'created_by',
        'created_by_type',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'note',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Status Constants
    |--------------------------------------------------------------------------
    */

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SENT = 'sent';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Parent offer.
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(
            Offer::class,
            'offer_id'
        );
    }

    /**
     * User who created this version.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /**
     * Items/hoardings included in this version.
     */
    public function items(): HasMany
    {
        return $this->hasMany(
            OfferVersionItem::class,
            'offer_version_id'
        );
    }

    /**
     * Activity logs related to this version.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(
            OfferActivityLog::class,
            'offer_version_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function calculateTotal(): float
    {
        return (float) $this->items()->sum('final_price');
    }
}
