<?php

namespace App\Services\Interfaces;

use App\Models\User;

interface AuthServiceInterface
{
    public function register(string $email, string $password, string $name, string $role, ?string $deviceToken = null): User;
    public function login(string $email, string $password): array;
    public function logout(string $token): void;
    public function refreshToken(): array;
    // public function verifyEmail(string $email): void;
    // public function resetPassword(string $email): void;
    // public function updatePassword(string $email, string $password): void;
}
