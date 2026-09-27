<?php

namespace App\Models;

use App\Enums\Permission as PermissionCode;
use App\Enums\RoleCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'role_id',
        'username',
        'name',
        'email',
        'phone',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected ?Collection $permissionCodesCache = null;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'is_active' => 'boolean',
            'failed_login_count' => 'integer',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
    }
    public function getRoleCodeAttribute(): ?string
    {
        return $this->role?->code;
    }

    /** @return Collection<int, string> */
    public function permissionCodes(): Collection
    {
        if ($this->role?->relationLoaded('permissions')) {
            return $this->role->permissions->pluck('code');
        }

        return $this->permissionCodesCache ??= $this->permissions()->pluck('code');
    }

    public function hasRole(RoleCode|string ...$roles): bool
    {
        $codes = array_map(
            fn (RoleCode|string $role) => $role instanceof RoleCode ? $role->value : $role,
            $roles
        );

        return $this->role_code !== null && in_array($this->role_code, $codes, true);
    }

    public function hasPermission(PermissionCode|string ...$permissions): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $codes = array_map(
            fn (PermissionCode|string $permission) => $permission instanceof PermissionCode ? $permission->value : $permission,
            $permissions
        );

        return $this->permissionCodes()->intersect($codes)->isNotEmpty();
    }

    public function hasAnyPermission(PermissionCode|string ...$permissions): bool
    {
        return $this->hasPermission(...$permissions);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(RoleCode::SUPER_ADMIN);
    }

    public function isLockedOut(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];

        return strtoupper(collect($parts)->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode(''));
    }
}
