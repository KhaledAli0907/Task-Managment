<?php

namespace Tests;

use App\Enums\PermissionEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up environment for testing
        $this->app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
        $this->app['config']->set('jwt.secret', 'test-secret-key-for-jwt-that-is-long-enough-for-256-bits');

        // Create permissions
        $this->createPermissions();

        // Create roles and assign permissions
        $this->createRolesWithPermissions();
    }

    protected function createPermissions(): void
    {
        $permissions = PermissionEnum::getAllValues();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api',
            ]);
        }
    }

    protected function createRolesWithPermissions(): void
    {
        // Create manager role with all task permissions
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'api']);
        $managerPermissions = PermissionEnum::getManagerPermissions();
        $managerRole->syncPermissions($managerPermissions);

        // Create user role with limited permissions
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'api']);
        $userPermissions = PermissionEnum::getUserPermissions();
        $userRole->syncPermissions($userPermissions);
    }


    protected function createManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole('manager');
        return $user;
    }

    protected function createUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        return $user;
    }

    protected function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    protected function authenticateAs(User $user): string
    {
        return JWTAuth::fromUser($user);
    }

    protected function withAuth(User $user): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->authenticateAs($user)
        ];
    }
}
