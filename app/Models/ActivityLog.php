<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'user_role',
        'action',
        'module',
        'subject_type',
        'subject_id',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
        'request_id',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * User who performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Record a new activity.
     */
    public static function record(
        string $action,
        string $description,
        ?string $module = null,
        ?Model $subject = null,
        ?array $metadata = null
    ): self {
        $user = auth()->user();

        return self::create([
            'user_id' => $user?->id,

            'user_role' => self::resolveUserRole($user),

            'action' => $action,

            'module' => $module,

            'subject_type' => $subject
                ? get_class($subject)
                : null,

            'subject_id' => $subject?->getKey(),

            'description' => $description,

            'metadata' => $metadata,

            'ip_address' => request()->ip(),

            'user_agent' => request()->userAgent(),

            'request_id' => request()->header('X-Request-ID')
                ?? request()->header('X-Request-Id'),
        ]);
    }

    /**
     * Resolve the role of the current user.
     */
    protected static function resolveUserRole($user): string
    {
        if (!$user) {
            return 'system';
        }

        if (
            method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole([
                'admin',
                'superadmin',
                'super_admin',
            ])
        ) {
            return 'admin';
        }

        if (
            method_exists($user, 'hasRole')
            && $user->hasRole('vendor')
        ) {
            return 'vendor';
        }

        if (
            method_exists($user, 'hasRole')
            && $user->hasRole('customer')
        ) {
            return 'customer';
        }

        return 'system';
    }
}