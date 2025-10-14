<?php

namespace App\Enums;

enum PermissionEnum: string
{
    // Task Management Permissions
    case TASK_CREATE = 'task.create';
    case TASK_READ = 'task.read';
    case TASK_UPDATE = 'task.update';
    case TASK_DELETE = 'task.delete';
    case TASK_ASSIGN = 'task.assign';
    case TASK_STATUS_UPDATE = 'task.status-update';
    case TASK_MANAGE_CHILDREN = 'task.manage-children';

    // User Management Permissions (for future use)
    case USER_CREATE = 'user.create';
    case USER_READ = 'user.read';
    case USER_UPDATE = 'user.update';
    case USER_DELETE = 'user.delete';

    // Role Management Permissions (for future use)
    case ROLE_CREATE = 'role.create';
    case ROLE_READ = 'role.read';
    case ROLE_UPDATE = 'role.update';
    case ROLE_DELETE = 'role.delete';

    /**
     * Get all task-related permissions
     */
    public static function getTaskPermissions(): array
    {
        return [
            self::TASK_CREATE,
            self::TASK_READ,
            self::TASK_UPDATE,
            self::TASK_DELETE,
            self::TASK_ASSIGN,
            self::TASK_STATUS_UPDATE,
            self::TASK_MANAGE_CHILDREN,
        ];
    }

    /**
     * Get permissions for manager role
     */
    public static function getManagerPermissions(): array
    {
        return [
                // All task permissions
            self::TASK_CREATE,
            self::TASK_READ,
            self::TASK_UPDATE,
            self::TASK_DELETE,
            self::TASK_ASSIGN,
            self::TASK_STATUS_UPDATE,
            self::TASK_MANAGE_CHILDREN,

                // User management permissions
            self::USER_CREATE,
            self::USER_READ,
            self::USER_UPDATE,
            self::USER_DELETE,
        ];
    }

    /**
     * Get permissions for regular user role
     */
    public static function getUserPermissions(): array
    {
        return [
            self::TASK_READ,
            self::TASK_STATUS_UPDATE,
        ];
    }

    /**
     * Get permissions for super admin role
     */
    public static function getSuperAdminPermissions(): array
    {
        return [
            // All permissions
            ...self::getManagerPermissions(),
            self::ROLE_CREATE,
            self::ROLE_READ,
            self::ROLE_UPDATE,
            self::ROLE_DELETE,
        ];
    }

    /**
     * Get the permission value as string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Get all permission values as array
     */
    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
