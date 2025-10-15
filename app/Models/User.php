<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'device_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'device_token',
        'email_verified_at',
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

    public function isManager(): bool
    {
        return $this->hasRole(RoleEnum::MANAGER->value);
    }

    public function isUser(): bool
    {
        return $this->hasRole(RoleEnum::USER->value);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(RoleEnum::SUPER_ADMIN->value);
    }

    // Permission-based helper methods
    public function canCreateTasks(): bool
    {
        return $this->can(PermissionEnum::TASK_CREATE->value);
    }

    public function canReadTasks(): bool
    {
        return $this->can(PermissionEnum::TASK_READ->value);
    }

    public function canUpdateTasks(): bool
    {
        return $this->can(PermissionEnum::TASK_UPDATE->value);
    }

    public function canDeleteTasks(): bool
    {
        return $this->can(PermissionEnum::TASK_DELETE->value);
    }

    public function canAssignTasks(): bool
    {
        return $this->can(PermissionEnum::TASK_ASSIGN->value);
    }

    public function canUpdateTaskStatus(): bool
    {
        return $this->can(PermissionEnum::TASK_STATUS_UPDATE->value);
    }

    public function canManageTaskChildren(): bool
    {
        return $this->can(PermissionEnum::TASK_MANAGE_CHILDREN->value);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
