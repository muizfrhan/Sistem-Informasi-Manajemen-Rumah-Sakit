<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'user_name',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): ?Model
    {
        if ($this->auditable_type === null || ! class_exists($this->auditable_type)) {
            return null;
        }

        return $this->auditable_type::find($this->auditable_id);
    }

    public function getChangedKeysAttribute(): array
    {
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];

        return collect(array_unique(array_merge(array_keys($old), array_keys($new))))
            ->reject(fn ($key) => ($old[$key] ?? null) === ($new[$key] ?? null))
            ->values()
            ->all();
    }
}
