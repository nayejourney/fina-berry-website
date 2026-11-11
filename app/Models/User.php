<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_OWNER = 'owner';
    public const ROLE_STAFF = 'staff';

    public const ROLES = [
        self::ROLE_SUPER_ADMIN => 'Super Admin (Bos/Manajer)',
        self::ROLE_OWNER => 'Pemilik',
        self::ROLE_STAFF => 'Staf',
    ];

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /** Pemilik atau Super Admin. */
    public function isOwnerOrAbove(): bool
    {
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_SUPER_ADMIN], true);
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }
}
