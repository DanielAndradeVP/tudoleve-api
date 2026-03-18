<?php

namespace App\Http\Controllers\Api\Auth;

use App\Domain\Auth\Services\AuthService;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterCustomerRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends ApiController
{
    public function __construct(
        protected AuthService $authService,
    ) {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $result = $this->authService->login(
            $credentials['email'],
            $credentials['password'],
        );

        return $this->success([
            'user' => $result['user'],
            'token' => $result['token'],
        ]);
    }

    public function register(RegisterCustomerRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->authService->registerCustomer($data);

        return $this->success([
            'user' => $result['user'],
            'customer' => $result['customer'],
            'token' => $result['token'],
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return $this->error('Not authenticated.', 401);
        }

        $this->authService->logout($user);

        return $this->success(null, 204);
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return $this->error('Not authenticated.', 401);
        }

        $token = $this->authService->refreshToken($user);

        return $this->success([
            'token' => $token,
        ]);
    }
}

