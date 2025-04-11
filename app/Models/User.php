<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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


    /**
     * Lấy tất cả vai trò của người dùng
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Lấy tất cả quyền được cấp trực tiếp cho người dùng
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Kiểm tra người dùng có vai trò cụ thể hay không
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    /**
     * Kiểm tra người dùng có quyền cụ thể hay không
     * (qua vai trò hoặc được cấp trực tiếp)
     */
    public function hasPermission(string $permissionSlug): bool
    {
        // Kiểm tra quyền trực tiếp
        if ($this->permissions()->where('slug', $permissionSlug)->exists()) {
            return true;
        }

        // Kiểm tra quyền thông qua vai trò
        foreach ($this->roles as $role) {
            if ($role->permissions()->where('slug', $permissionSlug)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gán vai trò cho người dùng
     */
    public function assignRole(string $roleSlug): void
    {
        $role = Role::where('slug', $roleSlug)->firstOrFail();
        if (!$this->hasRole($roleSlug)) {
            $this->roles()->attach($role->id);
        }
    }

    /**
     * Thu hồi vai trò của người dùng
     */
    public function revokeRole(string $roleSlug): void
    {
        $role = Role::where('slug', $roleSlug)->firstOrFail();
        $this->roles()->detach($role->id);
    }
}
