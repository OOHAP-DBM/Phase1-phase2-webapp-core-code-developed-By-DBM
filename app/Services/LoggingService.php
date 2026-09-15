<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\SessionLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Throwable;

class LoggingService
{
    public function activity(
        string $action,
        ?string $description = null,
        ?string $module = null,
        ?Model $subject = null,
        ?array $metadata = null
    ): ?ActivityLog {
        return $this->safe(function () use ($action, $description, $module, $subject, $metadata) {
            $user = Auth::user();

            return ActivityLog::create([
                'user_id' => $user?->id,
                'user_role' => $this->getUserRole($user),
                'action' => $action,
                'module' => $module,
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id' => $subject?->getKey(),
                'description' => $description,
                'metadata' => $this->sanitizeMetadata($metadata),
                'ip_address' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'request_id' => $this->requestId(),
            ]);
        });
    }

    public function audit(
        string $action,
        ?string $description = null,
        ?string $module = null,
        ?Model $subject = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null,
        ?string $event = null,
        ?string $tags = null
    ): ?AuditLog {
        return $this->safe(function () use ($action, $description, $module, $subject, $oldValues, $newValues, $metadata, $event, $tags) {
            $user = Auth::user();

            $oldValues = $this->sanitizeValues($oldValues);
            $newValues = $this->sanitizeValues($newValues);

            return AuditLog::create([
                'auditable_type' => $subject ? get_class($subject) : null,
                'auditable_id' => $subject?->getKey(),

                'user_id' => $user?->id,
                'user_type' => $this->getUserRole($user),
                'user_name' => $user?->name,
                'user_email' => $user?->email,

                'action' => $action,
                'event' => $event,
                'description' => $description,

                'old_values' => $oldValues,
                'new_values' => $newValues,
                'changed_fields' => $this->getChangedFields(
                    $oldValues,
                    $newValues
                ),

                'ip_address' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'request_method' => $this->requestMethod(),
                'request_url' => $this->requestUrl(),

                'metadata' => $this->sanitizeMetadata($metadata),
                'module' => $module,
                'tags' => $tags,
            ]);
        });
    }

    public function session(
        string $action,
        ?string $sessionId = null,
        ?string $description = null,
        ?array $metadata = null,
        ?Model $user = null
    ): SessionLog {
        $user ??= Auth::user();

        return SessionLog::create([
            'user_id' => $user?->id,
            'user_role' => $this->getUserRole($user),
            'session_id' => $sessionId ?? request()?->session()?->getId(),
            'event' => $action,
            'login_at' => $action === 'login' ? now() : null,
            'logout_at' => $action === 'logout' ? now() : null,
            'last_activity_at' => now(),
            'ip_address' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'metadata' => $metadata,
        ]);
    }
    public function login(
        ?array $metadata = null,
        ?Model $user = null
    ): ?SessionLog {
        return $this->sessionForUser(
            $user,
            'login',
            null,
            'User logged in',
            $metadata
        );
    }
    public function sessionForUser(
        ?Model $user,
        string $action,
        ?string $sessionId = null,
        ?string $description = null,
        ?array $metadata = null
    ): ?SessionLog {
        return $this->safe(function () use ($user, $action, $sessionId, $description, $metadata) {
            $user ??= Auth::user();

            if (!$user) {
                return null;
            }

            return SessionLog::create([
                'user_id' => $user->getKey(),

                'user_role' => $this->getUserRole($user),

                'event' => $action,

                'session_id' => $sessionId ?? $this->sessionId(),

                'login_at' => $action === 'login'
                    ? now()
                    : null,

                'logout_at' => $action === 'logout'
                    ? now()
                    : null,

                'last_activity_at' => now(),

                'ip_address' => $this->ip(),

                'user_agent' => $this->userAgent(),

                'metadata' => $this->sanitizeMetadata(
                    array_merge(
                        $metadata ?? [],
                        [
                            'description' => $description,
                        ]
                    )
                ),
            ]);
        });
    }

    public function logout(?array $metadata = null): ?SessionLog
    {
        return $this->session(
            'logout',
            null,
            'User logged out',
            $metadata
        );
    }

    public function created(
        Model $model,
        string $module,
        ?string $description = null,
        ?array $metadata = null
    ): ?AuditLog {
        return $this->audit(
            'created',
            $description ?? class_basename($model) . ' created',
            $module,
            $model,
            null,
            $model->toArray(),
            $metadata,
            'created'
        );
    }

    public function updated(
        Model $model,
        array $oldValues,
        string $module,
        ?string $description = null,
        ?array $metadata = null
    ): ?AuditLog {
        $newValues = $model->toArray();

        return $this->audit(
            'updated',
            $description ?? class_basename($model) . ' updated',
            $module,
            $model,
            $oldValues,
            $newValues,
            $metadata,
            'updated'
        );
    }

    public function deleted(
        Model $model,
        string $module,
        ?string $description = null,
        ?array $metadata = null
    ): ?AuditLog {
        return $this->audit(
            'deleted',
            $description ?? class_basename($model) . ' deleted',
            $module,
            $model,
            $model->toArray(),
            null,
            $metadata,
            'deleted'
        );
    }

    public function statusChanged(
        Model $model,
        string $oldStatus,
        string $newStatus,
        string $module,
        ?string $description = null,
        ?array $metadata = null
    ): ?AuditLog {
        return $this->audit(
            'status_changed',
            $description ?? class_basename($model) . ' status changed',
            $module,
            $model,
            ['status' => $oldStatus],
            ['status' => $newStatus],
            $metadata,
            'status_changed'
        );
    }

    public function priceChanged(
        Model $model,
        $oldPrice,
        $newPrice,
        string $module,
        ?string $description = null,
        ?array $metadata = null
    ): ?AuditLog {
        return $this->audit(
            'price_changed',
            $description ?? class_basename($model) . ' price changed',
            $module,
            $model,
            ['price' => $oldPrice],
            ['price' => $newPrice],
            $metadata,
            'price_changed'
        );
    }

    protected function getUserRole($user): string
    {
        if (!$user) {
            return 'system';
        }

        if (
            method_exists($user, 'hasAnyRole') &&
            $user->hasAnyRole([
                'superadmin',
                'super_admin',
                'admin',
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

        return 'user';
    }

    protected function getChangedFields(
        ?array $oldValues,
        ?array $newValues
    ): array {
        if ($oldValues === null || $newValues === null) {
            return [];
        }

        $fields = [];

        $allFields = array_unique(array_merge(
            array_keys($oldValues),
            array_keys($newValues)
        ));

        foreach ($allFields as $field) {
            $old = $oldValues[$field] ?? null;
            $new = $newValues[$field] ?? null;

            if ($old != $new) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    protected function sanitizeValues(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        $sensitiveFields = [
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'access_token',
            'refresh_token',
            'remember_token',
            'api_token',
            'secret',
            'client_secret',
            'razorpay_key_secret',
            'razorpay_secret',
            'otp',
            'otp_code',
        ];

        foreach ($sensitiveFields as $field) {
            if (array_key_exists($field, $values)) {
                $values[$field] = '[REDACTED]';
            }
        }

        return $values;
    }

    protected function sanitizeMetadata(?array $metadata): ?array
    {
        if ($metadata === null) {
            return null;
        }

        return $this->sanitizeValues($metadata);
    }

    protected function ip(): ?string
    {
        return request()?->ip();
    }

    protected function userAgent(): ?string
    {
        // Prefer explicit device name sent by mobile clients (e.g. Flutter/Dart)
        // Look for common payload keys and headers before falling back to the raw User-Agent.
        try {
            $req = request();

            if (!$req) {
                return null;
            }

            // 1) Check nested 'device' payload (device._name or device.name)
            if ($req->has('device')) {
                $device = $req->input('device');

                if (is_array($device)) {
                    if (!empty($device['_name'])) {
                        return $device['_name'];
                    }

                    if (!empty($device['name'])) {
                        return $device['name'];
                    }
                }

                if (is_object($device)) {
                    if (!empty($device->_name)) {
                        return $device->_name;
                    }

                    if (!empty($device->name)) {
                        return $device->name;
                    }
                }
            }

            // 2) Check flat fields: device_name, deviceName or _device_name
            foreach (['device_name', 'deviceName', '_device_name'] as $field) {
                if ($req->has($field) && $value = $req->input($field)) {
                    return $value;
                }
            }

            // 3) Check common headers: X-Device-Name or Device-Name
            $headerDevice = $req->header('X-Device-Name') ?? $req->header('Device-Name');
            if (!empty($headerDevice)) {
                return $headerDevice;
            }

            // 4) Finally fall back to the standard User-Agent string
            return $req->userAgent();
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    protected function requestId(): ?string
    {
        return request()?->header('X-Request-ID');
    }

    protected function requestMethod(): ?string
    {
        return request()?->method();
    }

    protected function requestUrl(): ?string
    {
        return request()?->fullUrl();
    }

    protected function sessionId(): ?string
    {
        try {
            return request()?->hasSession()
                ? request()->session()->getId()
                : null;
        } catch (Throwable) {
            return null;
        }
    }

    protected function safe(callable $callback)
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    public function activityForUser(int $userId, int $limit = 100)
    {
        return ActivityLog::where('user_id', $userId)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }
}
