<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Services\Interfaces\AuthServiceInterface;
use App\Traits\ResponseTrait;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use ResponseTrait;
    public function __construct(private AuthServiceInterface $authService)
    {
    }

    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register(
            $request->email,
            $request->password,
            $request->name,
            $request->role,
            $request->device_token ?? null
        );

        return $this->success201($result, 'User registered successfully');
    }

    public function login(Request $request)
    {
        Log::info('Login attempt', ['email' => $request->email, 'ip' => $request->ip()]);

        try {
            $result = $this->authService->login($request->email, $request->password);
            Log::info('Login successful', ['user_id' => auth()->id()]);
            return response()->json($result);
        } catch (AuthenticationException $e) {
            Log::warning('Login failed', ['email' => $request->email, 'ip' => $request->ip()]);
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    }

    public function logout()
    {
        $this->authService->logout(JWTAuth::getToken());
        return $this->success200(null, 'Logged out successfully');
    }
}
