<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuid, Notifiable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'status',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function isPlatformOwner(): bool
    {
        return $this->roles()->where('code', 'platform_owner')->exists();
    }

    public function hasRole(string|array $codes): bool
    {
        $codes = is_array($codes) ? $codes : [$codes];

        return $this->roles()->whereIn('code', $codes)->exists();
    }

    public function hasPermission(string $code): bool
    {
        if ($this->isPlatformOwner()) {
            return true;
        }

        return $this->roles()->whereHas('permissions', fn ($query) => $query->where('code', $code))->exists();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        return match ($panel->getId()) {
            'owner' => $this->hasRole('platform_owner'),
            'isp' => $this->hasRole(['tenant_owner', 'tenant_admin', 'finance', 'noc', 'sales', 'technician']),
            'customer' => $this->hasRole('customer'),
            'admin' => $this->roles()->whereNotIn('code', ['customer', 'partner'])->exists(),
            default => false,
        };
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
