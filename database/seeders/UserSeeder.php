<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Manager User
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@test.com',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
        ]);

        // Assign manager role
        $manager->assignRole(RoleEnum::MANAGER->value);

        // Create User 1
        $user1 = User::create([
            'name' => 'User One',
            'email' => 'user1@test.com',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
        ]);

        // Assign user role
        $user1->assignRole(RoleEnum::USER->value);

        // Create User 2
        $user2 = User::create([
            'name' => 'User Two',
            'email' => 'user2@test.com',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
        ]);

        // Assign user role
        $user2->assignRole(RoleEnum::USER->value);

        $this->command->info('Created users:');
        $this->command->info('- Manager: manager@test.com (password: 12345678)');
        $this->command->info('- User 1: user1@test.com (password: 12345678)');
        $this->command->info('- User 2: user2@test.com (password: 12345678)');
    }
}
