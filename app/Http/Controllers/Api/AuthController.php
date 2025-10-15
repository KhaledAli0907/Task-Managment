<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\AssignRoleRequest;
use App\Services\Interfaces\AuthServiceInterface;
use App\Traits\ResponseTrait;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use ResponseTrait;
    public function __construct(private AuthServiceInterface $authService)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register(
            $request->email,
            $request->password,
            $request->name,
            $request->device_token
        );

        return $this->success201($result, 'User registered successfully');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        Log::info('Login attempt', ['email' => $request->email, 'ip' => $request->ip()]);

        try {
            $result = $this->authService->login($request->email, $request->password, $request->device_token);
            Log::info('Login successful', ['user_id' => auth()->id()]);
            return $this->success200($result, 'Login successful');
        } catch (AuthenticationException $e) {
            Log::warning('Login failed', ['email' => $request->email, 'ip' => $request->ip()]);
            return $this->error401('Invalid credentials', 'Invalid credentials');
        }
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout(JWTAuth::getToken());
        Log::info('Logged out successfully', ['user_id' => auth()->id(), 'ip' => request()->ip()]);
        return $this->success200(null, 'Logged out successfully');
    }

    public function assignRole(AssignRoleRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->assignRoleToUser(
                $request->user_id,
                $request->role
            );

            Log::info('Role assigned successfully', [
                'assigned_by' => auth()->id(),
                'target_user_id' => $request->user_id,
                'role' => $request->role,
                'ip' => request()->ip()
            ]);

            return $this->success200($user, 'Role assigned successfully');
        } catch (\Exception $e) {
            Log::error('Role assignment failed', [
                'assigned_by' => auth()->id(),
                'target_user_id' => $request->user_id,
                'role' => $request->role,
                'error' => $e->getMessage(),
                'ip' => request()->ip()
            ]);

            return $this->error500('Failed to assign role: ' . $e->getMessage());
        }
    }
}
