<?php

namespace App\Services\Implementations;

use App\Models\User;
use App\Services\Interfaces\AuthServiceInterface;
use Hash;
use Illuminate\Auth\AuthenticationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService implements AuthServiceInterface
{
    public function register(string $email, string $password, string $name, string $role, ?string $deviceToken = null): User
    {
        $user = User::create([
            'email' => $email,
            'password' => Hash::make($password),
            'name' => $name,
            'device_token' => $deviceToken,
        ]);

        $user->assignRole($role);
        return $user;
    }

    public function login(string $email, string $password): array
    {
        $credentials = ['email' => $email, 'password' => $password];
        $token = JWTAuth::attempt($credentials);

        if (!$token) {
            throw new AuthenticationException('Invalid credentials provided');
        }

        $user = auth()->user();
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
}
