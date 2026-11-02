<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Throwable;

class AuditLog extends Model
{
    /**
     * Audit logs are immutable.
     */
    const UPDATED_AT = null;

    protected $table = 'audit_logs';

    protected $fillable = [
        'auditable_type',
        'auditable_id',

        'user_id',
        'user_type',
        'user_name',
        'user_email',

        'action',
        'event',
        'description',

        'old_values',
        'new_values',
        'changed_fields',

        'ip_address',
        'user_agent',
        'request_method',
        'request_url',
        'request_id',

        'metadata',
        'module',
        'tags',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_fields' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        /**
         * Audit logs must never be modified.
         */
        static::updating(function () {
            throw new \RuntimeException(
                'Audit logs are immutable and cannot be updated.'
            );
        });

        /**
         * Audit logs must never be deleted.
         */
        static::deleting(function () {
            throw new \RuntimeException(
                'Audit logs are immutable and cannot be deleted.'
            );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Model on which the action was performed.
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * User who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOfAction(
        Builder $query,
        string $action
    ): Builder {
        return $query->where('action', $action);
    }

    public function scopeOfModule(
        Builder $query,
        string $module
    ): Builder {
        return $query->where('module', $module);
    }

    public function scopeByUser(
        Builder $query,
        int $userId
    ): Builder {
        return $query->where('user_id', $userId);
    }

    public function scopeByUserType(
        Builder $query,
        string $userType
    ): Builder {
        return $query->where('user_type', $userType);
    }

    public function scopeForModel(
        Builder $query,
        string $type,
        int $id
    ): Builder {
        return $query
            ->where('auditable_type', $type)
            ->where('auditable_id', $id);
    }

    public function scopeDateRange(
        Builder $query,
        $from = null,
        $to = null
    ): Builder {
        if ($from) {
            $query->where('created_at', '>=', $from);
        }

        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        return $query;
    }

    public function scopeRecent(
        Builder $query,
        int $limit = 100
    ): Builder {
        return $query
            ->latest('created_at')
            ->limit($limit);
    }

    /*
    |--------------------------------------------------------------------------
    | Central Audit Logger
    |--------------------------------------------------------------------------
    */

    /**
     * Create an audit log entry.
     *
     * This method should be used throughout the application
     * whenever an important system/user action occurs.
     */
    public static function record(
        string $action,
        string $description,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null,
        ?string $module = null,
        ?string $event = null,
        ?string $tags = null
    ): ?self {
        try {
            $user = Auth::user();

            $userType = self::resolveUserType($user);

            $changedFields = self::getChangedFields(
                $oldValues,
                $newValues
            );

            return static::create([
                'auditable_type' => $auditable
                    ? get_class($auditable)
                    : null,

                'auditable_id' => $auditable?->getKey(),

                'user_id' => $user?->id,

                'user_type' => $userType,

                'user_name' => $user?->name,

                'user_email' => $user?->email,

                'action' => $action,

                'event' => $event,

                'description' => $description,

                'old_values' => $oldValues,

                'new_values' => $newValues,

                'changed_fields' => $changedFields,

                'ip_address' => self::getIpAddress(),

                'user_agent' => self::getUserAgent(),

                'request_method' => self::getRequestMethod(),

                'request_url' => self::getRequestUrl(),

                'request_id' => self::getRequestId(),

                'metadata' => $metadata,

                'module' => $module,

                'tags' => $tags,
            ]);
        } catch (Throwable $e) {

            /**
             * Audit logging should never break the main
             * business operation.
             */
            logger()->error(
                'AuditLog recording failed',
                [
                    'action' => $action,
                    'description' => $description,
                    'error' => $e->getMessage(),
                ]
            );

            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | User / Role Detection
    |--------------------------------------------------------------------------
    */

    /**
     * Resolve the actor type.
     *
     * admin / customer / vendor / system
     */
    protected static function resolveUserType($user): string
    {
        if (!$user) {
            return 'system';
        }

        try {

            if (
                method_exists($user, 'hasAnyRole') &&
                $user->hasAnyRole([
                    'admin',
                    'superadmin',
                    'super_admin',
                ])
            ) {
                return 'admin';
            }

            if (
                method_exists($user, 'hasRole') &&
                $user->hasRole('vendor')
            ) {
                return 'vendor';
            }

            if (
                method_exists($user, 'hasRole') &&
                $user->hasRole('customer')
            ) {
                return 'customer';
            }

        } catch (Throwable $e) {
            // Fall back to system below.
        }

        return 'system';
    }

    /*
    |--------------------------------------------------------------------------
    | Request Information
    |--------------------------------------------------------------------------
    */

    protected static function getIpAddress(): ?string
    {
        try {
            return Request::ip();
        } catch (Throwable $e) {
            return null;
        }
    }

    protected static function getUserAgent(): ?string
    {
        try {
            return Request::userAgent();
        } catch (Throwable $e) {
            return null;
        }
    }

    protected static function getRequestMethod(): ?string
    {
        try {
            return Request::method();
        } catch (Throwable $e) {
            return null;
        }
    }

    protected static function getRequestUrl(): ?string
    {
        try {
            return Request::fullUrl();
        } catch (Throwable $e) {
            return null;
        }
    }

    protected static function getRequestId(): ?string
    {
        try {
            return Request::header('X-Request-ID')
                ?? Request::header('X-Correlation-ID');
        } catch (Throwable $e) {
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Changed Fields
    |--------------------------------------------------------------------------
    */

    protected static function getChangedFields(
        ?array $oldValues,
        ?array $newValues
    ): ?array {

        if (!$oldValues || !$newValues) {
            return null;
        }

        $fields = [];

        $allFields = array_unique(
            array_merge(
                array_keys($oldValues),
                array_keys($newValues)
            )
        );

        foreach ($allFields as $field) {

            $old = $oldValues[$field] ?? null;
            $new = $newValues[$field] ?? null;

            if ($old != $new) {
                $fields[] = $field;
            }
        }

        return empty($fields) ? null : $fields;
    }

    /*
    |--------------------------------------------------------------------------
    | Display Helpers
    |--------------------------------------------------------------------------
    */

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {

            'created' => 'Created',

            'updated' => 'Updated',

            'deleted' => 'Deleted',

            'restored' => 'Restored',

            'status_changed' => 'Status Changed',

            'price_changed' => 'Price Changed',

            'other' => ucfirst(
                str_replace(
                    '_',
                    ' ',
                    $this->event ?? 'Other'
                )
            ),

            default => ucfirst(
                str_replace(
                    '_',
                    ' ',
                    $this->action ?? 'Unknown'
                )
            ),
        };
    }

    public function getModelNameAttribute(): string
    {
        if (!$this->auditable_type) {
            return 'System';
        }

        $parts = explode('\\', $this->auditable_type);

        $className = end($parts);

        return preg_replace(
            '/(?<!^)[A-Z]/',
            ' $0',
            $className
        );
    }

    public function getChangesSummaryAttribute(): array
    {
        $summary = [];

        if (
            is_array($this->changed_fields) &&
            !empty($this->changed_fields)
        ) {

            foreach ($this->changed_fields as $field) {

                $oldValue =
                    $this->old_values[$field] ?? null;

                $newValue =
                    $this->new_values[$field] ?? null;

                $summary[] = [
                    'field' => $this->formatFieldName($field),

                    'old' => $this->formatValue($oldValue),

                    'new' => $this->formatValue($newValue),
                ];
            }
        }

        return $summary;
    }

    protected function formatFieldName(string $field): string
    {
        return ucwords(
            str_replace('_', ' ', $field)
        );
    }

    protected function formatValue($value): string
    {
        if (is_null($value)) {
            return '(empty)';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }

    public function getTagsArrayAttribute(): array
    {
        if (empty($this->tags)) {
            return [];
        }

        return array_map(
            'trim',
            explode(',', $this->tags)
        );
    }

    public function getIsTodayAttribute(): bool
    {
        return $this->created_at?->isToday() ?? false;
    }

    public function getIsThisWeekAttribute(): bool
    {
        return $this->created_at?->isCurrentWeek() ?? false;
    }

    public function getRelativeTimeAttribute(): string
    {
        return $this->created_at
            ? $this->created_at->diffForHumans()
            : '';
    }

    /*
    |--------------------------------------------------------------------------
    | Static Queries
    |--------------------------------------------------------------------------
    */

    public static function recentActivity(int $limit = 50)
    {
        return static::with([
            'user',
            'auditable',
        ])
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public static function getHistory(
        Model $model,
        ?int $limit = null
    ) {
        $query = static::forModel(
            get_class($model),
            $model->getKey()
        )
            ->with('user')
            ->latest('created_at');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public static function getUserActivity(
        int $userId,
        int $limit = 100
    ) {
        return static::byUser($userId)
            ->with('auditable')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public static function getStatistics(
        array $filters = []
    ): array {

        $query = static::query();

        if (!empty($filters['from'])) {
            $query->where(
                'created_at',
                '>=',
                $filters['from']
            );
        }

        if (!empty($filters['to'])) {
            $query->where(
                'created_at',
                '<=',
                $filters['to']
            );
        }

        if (!empty($filters['module'])) {
            $query->where(
                'module',
                $filters['module']
            );
        }

        if (!empty($filters['user_type'])) {
            $query->where(
                'user_type',
                $filters['user_type']
            );
        }

        $total = $query->count();

        $byAction = static::query()
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action');

        $byModule = static::query()
            ->selectRaw('module, COUNT(*) as count')
            ->whereNotNull('module')
            ->groupBy('module')
            ->pluck('count', 'module');

        $byUserType = static::query()
            ->selectRaw('user_type, COUNT(*) as count')
            ->groupBy('user_type')
            ->pluck('count', 'user_type');

        $byUser = static::query()
            ->selectRaw(
                'user_id, user_name, user_email, user_type, COUNT(*) as count'
            )
            ->whereNotNull('user_id')
            ->groupBy(
                'user_id',
                'user_name',
                'user_email',
                'user_type'
            )
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [

            'total' => $total,

            'by_action' => $byAction,

            'by_module' => $byModule,

            'by_user_type' => $byUserType,

            'top_users' => $byUser,

            'today' => static::whereDate(
                'created_at',
                today()
            )->count(),

            'this_week' => static::whereBetween(
                'created_at',
                [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]
            )->count(),

            'this_month' => static::whereMonth(
                'created_at',
                now()->month
            )
                ->whereYear(
                    'created_at',
                    now()->year
                )
                ->count(),
        ];
    }
}