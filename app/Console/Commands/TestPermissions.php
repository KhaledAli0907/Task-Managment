<?php

namespace App\Console\Commands;

use App\Enums\PermissionEnum;
use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TestPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the permission system implementation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Permission System...');
        $this->newLine();

        // Test 1: Check if permissions exist
        $this->info('1. Checking if permissions exist:');
        $permissions = PermissionEnum::getAllValues();
        foreach ($permissions as $permission) {
            $exists = Permission::where('name', $permission)->exists();
            $status = $exists ? '✅' : '❌';
            $this->line("   {$status} {$permission}");
        }
        $this->newLine();

        // Test 2: Check role permissions
        $this->info('2. Checking role permissions:');
        $roles = ['manager', 'user', 'super_admin'];
        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $permissions = $role->permissions->pluck('name')->toArray();
                $this->line("   📋 {$roleName}: " . count($permissions) . " permissions");
                foreach ($permissions as $permission) {
                    $this->line("      - {$permission}");
                }
            } else {
                $this->line("   ❌ Role '{$roleName}' not found");
            }
            $this->newLine();
        }

        // Test 3: Test permission checks (if users exist)
        $this->info('3. Testing permission checks:');
        $manager = User::whereHas('roles', function ($q) {
            $q->where('name', 'manager');
        })->first();

        $user = User::whereHas('roles', function ($q) {
            $q->where('name', 'user');
        })->first();

        if ($manager) {
            $this->line("   👨‍💼 Manager permissions:");
            foreach (PermissionEnum::getTaskPermissions() as $permission) {
                $can = $manager->can($permission->value) ? '✅' : '❌';
                $this->line("      {$can} {$permission->value}");
            }
        } else {
            $this->line("   ⚠️  No manager user found");
        }

        if ($user) {
            $this->line("   👤 User permissions:");
            foreach (PermissionEnum::getTaskPermissions() as $permission) {
                $can = $user->can($permission->value) ? '✅' : '❌';
                $this->line("      {$can} {$permission->value}");
            }
        } else {
            $this->line("   ⚠️  No regular user found");
        }

        $this->newLine();
        $this->info('Permission system test completed!');
    }
}
