<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// class OfferActivityLog extends Model
// {
//     use HasFactory;

//     protected $table = 'offer_activity_logs';

//     protected $fillable = [
//         'offer_id',
//         'offer_version_id',
//         'actor_id',
//         'actor_type',
//         'action',
//         'description',
//         'metadata',
//     ];

//     protected $casts = [
//         'metadata' => 'array',
//     ];

//     /*
//     |--------------------------------------------------------------------------
//     | Relationships
//     |--------------------------------------------------------------------------
//     */

//     /**
//      * Parent offer.
//      */
//     public function offer(): BelongsTo
//     {
//         return $this->belongsTo(
//             Offer::class,
//             'offer_id'
//         );
//     }

//     /**
//      * Related offer version.
//      */
//     public function offerVersion(): BelongsTo
//     {
//         return $this->belongsTo(
//             OfferVersion::class,
//             'offer_version_id'
//         );
//     }

//     /**
//      * User who performed the action.
//      */
//     public function actor(): BelongsTo
//     {
//         return $this->belongsTo(
//             User::class,
//             'actor_id'
//         );
//     }
// }
class OfferActivityLog extends Model
{
    use HasFactory;

    protected $table = 'offer_activity_logs';

    protected $fillable = [
        'offer_id', 'offer_version_id', 'actor_id', 'actor_type',
        'action', 'description', 'metadata',
    ];

    protected $casts = ['metadata' => 'array'];

    public function offer(): BelongsTo { return $this->belongsTo(Offer::class, 'offer_id'); }
    public function offerVersion(): BelongsTo { return $this->belongsTo(OfferVersion::class, 'offer_version_id'); }
    public function actor(): BelongsTo { return $this->belongsTo(User::class, 'actor_id'); }

    public static function record(Offer $offer, string $action, string $description, ?array $metadata = null): self
    {
        $user = auth()->user();

        $actorType = 'system';
        if ($user) {
            if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'superadmin', 'super_admin'])) {
                $actorType = 'admin';
            } elseif (method_exists($user, 'hasRole') && $user->hasRole('vendor')) {
                $actorType = 'vendor';
            } else {
                $actorType = 'customer';
            }
        }

        return self::create([
            'offer_id'         => $offer->id,
            'offer_version_id' => $offer->current_version_id,
            'actor_id'         => $user?->id,
            'actor_type'       => $actorType,
            'action'           => $action,
            'description'      => $description,
            'metadata'         => $metadata,
        ]);
    }
}
