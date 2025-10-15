<?php

namespace App\Services\Implementations;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Services\Interfaces\AuthServiceInterface;
use Hash;
use Illuminate\Auth\AuthenticationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthService implements AuthServiceInterface
{
    public function register(string $email, string $password, string $name, ?string $deviceToken = null): User
    {
        Log::info('Register attempt', ['email' => $email, 'ip' => request()->ip()]);
        DB::beginTransaction();
        try {
            $user = User::create([
                'email' => $email,
                'password' => Hash::make($password),
                'name' => $name,
                'device_token' => $deviceToken,
            ]);

            // Assign default 'user' role for self-registration
            // Managers can change roles later using the assign-role endpoint
            $user->assignRole(RoleEnum::getDefault()->value);
            DB::commit();
            Log::info('Register successful', ['email' => $email, 'ip' => request()->ip()]);
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Register failed', ['email' => $email, 'ip' => request()->ip(), 'error' => $e->getMessage()]);
            throw new \Exception('Failed to register user');
        }
    }

    public function login(string $email, string $password, ?string $deviceToken = null): array
    {
        $credentials = ['email' => $email, 'password' => $password];
        $token = JWTAuth::attempt($credentials);

        if (!$token) {
            throw new AuthenticationException('Invalid credentials provided');
        }

        $user = auth()->user();

        // Update device token if provided
        if ($deviceToken) {
            $user->update(['device_token' => $deviceToken]);
        }

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => $user->load('roles')
        ];
    }

    public function logout(string $token): void
    {
        $user = JWTAuth::parseToken()->authenticate();
        JWTAuth::invalidate($token);
        $user->update(['device_token' => null]);
        $user->save();
    }

    // In AuthService
    public function refreshToken(): array
    {
        $token = JWTAuth::refresh();
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ];
    }

    public function assignRoleToUser(int $userId, string $role): User
    {
        $user = User::findOrFail($userId);
        $user->syncRoles([$role]); // Replace all roles with the new one
        return $user->load('roles');
    }
}
