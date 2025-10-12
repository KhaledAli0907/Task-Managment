<?php

namespace Database\Seeders;

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
        Role::create(['name' => 'manager', 'guard_name' => self::API_GUARD_NAME]);
        Role::create(['name' => 'user', 'guard_name' => self::API_GUARD_NAME]);
        Role::create(['name' => 'super_admin', 'guard_name' => self::API_GUARD_NAME]);
    }
}
