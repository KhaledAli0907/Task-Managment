<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_user_can_register_with_valid_data()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'User registered successfully'
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue($user->hasRole(RoleEnum::USER->value));
    }

    public function test_user_registers_with_default_user_role()
    {
        $userData = [
            'name' => 'Manager Doe',
            'email' => 'manager@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201);

        $user = User::where('email', 'manager@example.com')->first();
        // New users always get 'user' role by default
        $this->assertTrue($user->hasRole(RoleEnum::USER->value));
        $this->assertFalse($user->hasRole(RoleEnum::MANAGER->value));
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $user = $this->createUser();

        $loginData = [
            'email' => $user->email,
            'password' => 'password'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful'
            ])
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'user'
                ]
            ]);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = $this->createUser();

        $loginData = [
            'email' => $user->email,
            'password' => 'wrongpassword'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials'
            ]);
    }

    public function test_user_can_logout()
    {
        $user = $this->createUser();
        $token = $this->authenticateAs($user);

        $response = $this->postJson('/api/auth/logout', [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
    }

    public function test_registration_validation()
    {
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_registration_with_optional_role_field()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'invalid_role' // This should be ignored since role is not used in registration
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201); // Should succeed since role is ignored

        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue($user->hasRole(RoleEnum::USER->value)); // Should still get default user role
    }

    public function test_registration_with_duplicate_email()
    {
        $existingUser = $this->createUser();

        $userData = [
            'name' => 'John Doe',
            'email' => $existingUser->email,
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_password_confirmation_validation()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_validation()
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_logout_requires_authentication()
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }

    public function test_manager_can_assign_role_to_user()
    {
        $manager = $this->createUserWithRole(RoleEnum::MANAGER->value);
        $user = $this->createUserWithRole(RoleEnum::USER->value);
        $token = $this->authenticateAs($manager);

        $assignData = [
            'user_id' => $user->id,
            'role' => RoleEnum::MANAGER->value
        ];

        $response = $this->postJson('/api/auth/assign-role', $assignData, [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Role assigned successfully'
            ]);

        $user->refresh();
        $this->assertTrue($user->hasRole(RoleEnum::MANAGER->value));
    }

    public function test_assign_role_requires_authentication()
    {
        $user = $this->createUserWithRole(RoleEnum::USER->value);

        $assignData = [
            'user_id' => $user->id,
            'role' => RoleEnum::MANAGER->value
        ];

        $response = $this->postJson('/api/auth/assign-role', $assignData);

        $response->assertStatus(401);
    }

    public function test_only_managers_can_assign_roles()
    {
        $regularUser = $this->createUserWithRole(RoleEnum::USER->value);
        $targetUser = $this->createUserWithRole(RoleEnum::USER->value);
        $token = $this->authenticateAs($regularUser);

        $assignData = [
            'user_id' => $targetUser->id,
            'role' => RoleEnum::MANAGER->value
        ];

        $response = $this->postJson('/api/auth/assign-role', $assignData, [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(403);
    }

    public function test_assign_role_validation()
    {
        $manager = $this->createUserWithRole(RoleEnum::MANAGER->value);
        $token = $this->authenticateAs($manager);

        $response = $this->postJson('/api/auth/assign-role', [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'role']);
    }

    public function test_assign_role_with_invalid_user_id()
    {
        $manager = $this->createUserWithRole(RoleEnum::MANAGER->value);
        $token = $this->authenticateAs($manager);

        $assignData = [
            'user_id' => 99999, // Non-existent user
            'role' => RoleEnum::MANAGER->value
        ];

        $response = $this->postJson('/api/auth/assign-role', $assignData, [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    }

    public function test_assign_role_with_invalid_role()
    {
        $manager = $this->createUserWithRole(RoleEnum::MANAGER->value);
        $user = $this->createUserWithRole(RoleEnum::USER->value);
        $token = $this->authenticateAs($manager);

        $assignData = [
            'user_id' => $user->id,
            'role' => 'invalid_role'
        ];

        $response = $this->postJson('/api/auth/assign-role', $assignData, [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    public function test_assign_role_removes_previous_roles()
    {
        $manager = $this->createUserWithRole(RoleEnum::MANAGER->value);
        $user = $this->createUserWithRole(RoleEnum::USER->value);
        $token = $this->authenticateAs($manager);

        // Verify user has user role initially
        $this->assertTrue($user->hasRole(RoleEnum::USER->value));
        $this->assertFalse($user->hasRole(RoleEnum::MANAGER->value));

        $assignData = [
            'user_id' => $user->id,
            'role' => RoleEnum::MANAGER->value
        ];

        $response = $this->postJson('/api/auth/assign-role', $assignData, [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200);

        $user->refresh();
        // Should now have manager role and not user role
        $this->assertTrue($user->hasRole(RoleEnum::MANAGER->value));
        $this->assertFalse($user->hasRole(RoleEnum::USER->value));
    }
}
