<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Throwable;

class SessionLog extends Model
{
    protected $table = 'session_logs';

    protected $fillable = [
        'user_id',
        'user_role',
        'session_id',
        'event',
        'login_at',
        'logout_at',
        'last_activity_at',
        'ip_address',
        'user_agent',
        'device',
        'platform',
        'browser',
        'metadata',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'metadata' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeByUser(
        Builder $query,
        int $userId
    ): Builder {
        return $query->where('user_id', $userId);
    }

    public function scopeByRole(
        Builder $query,
        string $role
    ): Builder {
        return $query->where('user_role', $role);
    }

    public function scopeRecent(
        Builder $query,
        int $limit = 100
    ): Builder {
        return $query
            ->latest('login_at')
            ->limit($limit);
    }

    /*
    |--------------------------------------------------------------------------
    | Start Session
    |--------------------------------------------------------------------------
    */

    public static function start(
        ?User $user = null,
        ?string $sessionId = null,
        ?array $metadata = null
    ): ?self {
        try {
            $user ??= Auth::user();

            if (!$user) {
                logger()->warning('SessionLog start skipped: no authenticated user.');

                return null;
            }

            $sessionId ??= session()->getId();

            return static::create([
                'user_id' => $user->id,

                'user_role' => self::resolveUserRole($user),

                'session_id' => $sessionId,

                'event' => 'login',

                'login_at' => now(),

                'last_activity_at' => now(),

                'ip_address' => Request::ip(),

                'user_agent' => Request::userAgent(),

                'device' => self::detectDevice(),

                'platform' => self::detectPlatform(),

                'browser' => self::detectBrowser(),

                'metadata' => $metadata,
            ]);
        } catch (Throwable $e) {
            logger()->error('SessionLog start failed', [
                'user_id' => $user?->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | End Session
    |--------------------------------------------------------------------------
    */

    public function end(): bool
    {
        try {
            return $this->update([
                'event' => 'logout',
                'logout_at' => now(),
                'last_activity_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('SessionLog end failed', [
                'session_log_id' => $this->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Touch Activity
    |--------------------------------------------------------------------------
    */

    public function touchActivity(): bool
    {
        try {
            return $this->update([
                'last_activity_at' => now(),
            ]);
        } catch (Throwable $e) {
            logger()->error('SessionLog activity update failed', [
                'session_log_id' => $this->id,
                'user_id' => $this->user_id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Find Active Session
    |--------------------------------------------------------------------------
    |
    | We don't use "status" because session_logs table does not have
    | a status column.
    |
    | logout_at IS NULL = currently active session
    |
    */

    public static function activeForUser(
        int $userId
    ): ?self {
        try {
            return static::query()
                ->where('user_id', $userId)
                ->whereNull('logout_at')
                ->latest('login_at')
                ->first();
        } catch (Throwable $e) {
            logger()->error('SessionLog activeForUser failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve User Role
    |--------------------------------------------------------------------------
    */

    public static function resolveUserRole($user): string
    {
        if (!$user) {
            return 'system';
        }

        try {
            /*
             * First priority:
             * active_role
             *
             * This is important for your OOHAPP login flow because
             * the selected role is already stored on the user.
             */

            if (
                !empty($user->active_role)
                && in_array(
                    $user->active_role,
                    [
                        'admin',
                        'superadmin',
                        'super_admin',
                        'vendor',
                        'customer',
                    ],
                    true
                )
            ) {
                if (
                    in_array(
                        $user->active_role,
                        [
                            'superadmin',
                            'super_admin',
                        ],
                        true
                    )
                ) {
                    return 'admin';
                }

                return $user->active_role;
            }

            /*
             * Fallback to Spatie/role methods.
             */

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
        } catch (Throwable $e) {
            logger()->warning(
                'SessionLog role resolution failed',
                [
                    'user_id' => $user->id ?? null,
                    'active_role' => $user->active_role ?? null,
                    'error' => $e->getMessage(),
                ]
            );
        }

        return 'user';
    }

    /*
    |--------------------------------------------------------------------------
    | Device Detection
    |--------------------------------------------------------------------------
    */

    protected static function detectDevice(): ?string
    {
        $agent = Request::userAgent();

        if (!$agent) {
            return null;
        }

        $agent = strtolower($agent);

        if (
            Str::contains($agent, [
                'mobile',
                'android',
                'iphone',
                'ipad',
            ])
        ) {
            return 'mobile';
        }

        return 'desktop';
    }

    /*
    |--------------------------------------------------------------------------
    | Platform Detection
    |--------------------------------------------------------------------------
    */

    protected static function detectPlatform(): ?string
    {
        $agent = strtolower(
            Request::userAgent() ?? ''
        );

        return match (true) {
            Str::contains($agent, 'windows') => 'Windows',

            Str::contains($agent, 'macintosh') => 'macOS',

            Str::contains($agent, 'android') => 'Android',

            Str::contains($agent, 'iphone') => 'iOS',

            Str::contains($agent, 'ipad') => 'iOS',

            Str::contains($agent, 'linux') => 'Linux',

            default => 'Unknown',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Browser Detection
    |--------------------------------------------------------------------------
    */

    protected static function detectBrowser(): ?string
    {
        $agent = strtolower(
            Request::userAgent() ?? ''
        );

        return match (true) {
            Str::contains($agent, 'edg') => 'Edge',

            Str::contains($agent, 'opr') => 'Opera',

            Str::contains($agent, 'opera') => 'Opera',

            Str::contains($agent, 'chrome') => 'Chrome',

            Str::contains($agent, 'firefox') => 'Firefox',

            Str::contains($agent, 'safari') => 'Safari',

            default => 'Unknown',
        };
    }
}