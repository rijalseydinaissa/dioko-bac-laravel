<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;

class AuthController extends Controller
{
    use ApiResponse;

    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        try {
            $result = $this->authService->register($request->validated());

            return $this->success([
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
            ], 'Inscription réussie', 201);

        } catch (\Exception $e) {
            return $this->serverError('Erreur lors de l\'inscription');
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->login($request->validated());

            if (!$result) {
                return $this->unauthorized('Identifiants incorrects');
            }

            return $this->success([
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
            ], 'Connexion réussie');

        } catch (\Exception $e) {
            return $this->serverError('Erreur lors de la connexion');
        }
    }

    public function logout()
    {
        try {
            $this->authService->logout();
            return $this->success(null, 'Déconnexion réussie');

        } catch (\Exception $e) {
            return $this->serverError('Erreur lors de la déconnexion');
        }
    }

    public function refresh()
    {
        try {
            $token = $this->authService->refresh();

            return $this->success([
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
            ], 'Token rafraîchi');

        } catch (\Exception $e) {
            return $this->unauthorized('Impossible de rafraîchir le token');
        }
    }

    public function me()
    {
        try {
            $user = $this->authService->me();
            return $this->success(new UserResource($user));

        } catch (\Exception $e) {
            return $this->serverError('Erreur lors de la récupération du profil');
        }
    }
}