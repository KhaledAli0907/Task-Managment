<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreatePermissions extends Seeder
{
    private const API_GUARD_NAME = 'api';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create all permissions
        $this->createPermissions();

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    /**
     * Create all permissions from the enum
     */
    private function createPermissions(): void
    {
        $permissions = PermissionEnum::getAllValues();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => self::API_GUARD_NAME,
            ]);
        }

        $this->command->info('Created ' . count($permissions) . ' permissions');
    }

    /**
     * Assign permissions to roles
     */
    private function assignPermissionsToRoles(): void
    {
        // Get roles
        $managerRole = Role::where('name', 'manager')
            ->where('guard_name', self::API_GUARD_NAME)
            ->first();

        $userRole = Role::where('name', 'user')
            ->where('guard_name', self::API_GUARD_NAME)
            ->first();

        $superAdminRole = Role::where('name', 'super_admin')
            ->where('guard_name', self::API_GUARD_NAME)
            ->first();

        if (!$managerRole || !$userRole || !$superAdminRole) {
            $this->command->error('Roles not found. Please run CreateRoles seeder first.');
            return;
        }

        // Assign permissions to manager role
        $managerPermissions = PermissionEnum::getManagerPermissions();
        $managerRole->syncPermissions($managerPermissions);
        $this->command->info('Assigned ' . count($managerPermissions) . ' permissions to manager role');

        // Assign permissions to user role
        $userPermissions = PermissionEnum::getUserPermissions();
        $userRole->syncPermissions($userPermissions);
        $this->command->info('Assigned ' . count($userPermissions) . ' permissions to user role');

        // Assign permissions to super admin role
        $superAdminPermissions = PermissionEnum::getSuperAdminPermissions();
        $superAdminRole->syncPermissions($superAdminPermissions);
        $this->command->info('Assigned ' . count($superAdminPermissions) . ' permissions to super_admin role');
    }
}
