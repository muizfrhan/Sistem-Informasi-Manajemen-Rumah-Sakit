<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    public const CREATED = 'created';

    public const UPDATED = 'updated';

    public const DELETED = 'deleted';

    public const VIEWED = 'viewed';

    public const LOGIN = 'login';

    public const LOGIN_FAILED = 'login_failed';

    public const LOGOUT = 'logout';

    public function created(Model $model): void
    {
        $this->write(self::CREATED, $model, [], $this->safeAttributes($model));
    }

    public function updated(Model $model, array $old): void
    {
        $this->write(self::UPDATED, $model, $old, $this->safeAttributes($model));
    }

    public function deleted(Model $model): void
    {
        $this->write(self::DELETED, $model, $this->safeAttributes($model), []);
    }

    public function viewed(string $modelClass, int|string $id): void
    {
        $this->write(self::VIEWED, null, [], ['id' => $id], $modelClass, $id);
    }

    public function login(string $username, bool $success, ?string $reason = null): void
    {
        $this->write($success ? self::LOGIN : self::LOGIN_FAILED, null, [], [
            'username' => $username,
            'reason' => $reason,
        ]);
    }

    public function logout(): void
    {
        $this->write(self::LOGOUT, null, [], []);
    }

    public function write(
        string $event,
        ?Model $model = null,
        array $old = [],
        array $new = [],
        ?string $auditableType = null,
        int|string|null $auditableId = null
    ): void {
        if (! config('simrs.audit_logging_enabled', true)) {
            return;
        }

        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'user_name' => Auth::user()?->name,
                'event' => $event,
                'auditable_type' => $model ? $model::class : $auditableType,
                'auditable_id' => $model ? $model->getKey() : $auditableId,
                'old_values' => $old ?: null,
                'new_values' => $new ?: null,
                'ip_address' => Request::ip(),
                'user_agent' => mb_substr((string) Request::userAgent(), 0, 500),
                'url' => mb_substr((string) Request::url(), 0, 255),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Audit log gagal ditulis.', ['message' => $e->getMessage()]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function safeAttributes(Model $model): array
    {
        $hidden = ['password', 'remember_token', 'nik_hash', 'payload', 'api_token'];

        return collect($model->attributesToArray())
            ->except($hidden)
            ->all();
    }
}
