<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;


class CreateRoles extends Seeder
{

    private const API_GUARD_NAME = 'api';
    private const WEB_GUARD_NAME = 'web';
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (RoleEnum::cases() as $role) {
            Role::create([
                'name' => $role->value,
                'guard_name' => self::API_GUARD_NAME
            ]);
        }

        $this->command->info('Created roles: ' . implode(', ', RoleEnum::getAllValues()));
    }
}
