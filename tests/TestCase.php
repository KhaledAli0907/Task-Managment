<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up environment for testing
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
        config(['jwt.secret' => 'test-secret-key-for-jwt-that-is-long-enough-for-256-bits']);

        // Create roles if they don't exist
        if (!Role::where('name', 'manager')->where('guard_name', 'api')->exists()) {
            Role::create(['name' => 'manager', 'guard_name' => 'api']);
        }
        if (!Role::where('name', 'user')->where('guard_name', 'api')->exists()) {
            Role::create(['name' => 'user', 'guard_name' => 'api']);
        }
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
