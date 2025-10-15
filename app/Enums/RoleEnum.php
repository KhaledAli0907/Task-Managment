<?php

namespace App\Enums;

enum RoleEnum: string
{
    case MANAGER = 'manager';
    case USER = 'user';
    case SUPER_ADMIN = 'super_admin';

    /**
     * Get all role values as array
     */
    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get role display name
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::MANAGER => 'Manager',
            self::USER => 'User',
            self::SUPER_ADMIN => 'Super Admin',
        };
    }

    /**
     * Get role description
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::MANAGER => 'Can manage tasks, assign roles, and oversee all operations',
            self::USER => 'Can view assigned tasks and update their status',
            self::SUPER_ADMIN => 'Full system access including user and role management',
        };
    }

    /**
     * Get permissions associated with this role
     */
    public function getPermissions(): array
    {
        return match ($this) {
            self::MANAGER => [
                PermissionEnum::TASK_CREATE,
                PermissionEnum::TASK_READ,
                PermissionEnum::TASK_UPDATE,
                PermissionEnum::TASK_DELETE,
                PermissionEnum::TASK_ASSIGN,
                PermissionEnum::TASK_STATUS_UPDATE,
                PermissionEnum::TASK_MANAGE_CHILDREN,
                PermissionEnum::USER_READ,
            ],
            self::USER => [
                PermissionEnum::TASK_READ,
                PermissionEnum::TASK_STATUS_UPDATE,
            ],
            self::SUPER_ADMIN => [
                // All permissions
                ...PermissionEnum::getAllValues(),
            ],
        };
    }

    /**
     * Check if role can assign other roles
     */
    public function canAssignRoles(): bool
    {
        return match ($this) {
            self::MANAGER, self::SUPER_ADMIN => true,
            self::USER => false,
        };
    }

    /**
     * Check if role can manage tasks
     */
    public function canManageTasks(): bool
    {
        return match ($this) {
            self::MANAGER, self::SUPER_ADMIN => true,
            self::USER => false,
        };
    }

    /**
     * Check if role can view all tasks
     */
    public function canViewAllTasks(): bool
    {
        return match ($this) {
            self::MANAGER, self::SUPER_ADMIN => true,
            self::USER => false,
        };
    }

    /**
     * Get default role for new users
     */
    public static function getDefault(): self
    {
        return self::USER;
    }

    /**
     * Validate if a role string is valid
     */
    public static function isValid(string $role): bool
    {
        return in_array($role, self::getAllValues());
    }

    /**
     * Get role from string value
     */
    public static function fromString(string $role): ?self
    {
        return self::tryFrom($role);
    }
}
