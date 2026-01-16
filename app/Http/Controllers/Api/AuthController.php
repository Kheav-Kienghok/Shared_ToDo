<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Psr\Log\LoggerInterface;

class AuthController extends Controller
{
    protected LoggerInterface $logger;
    protected User $user;
    protected JWTAuth $jwt;

    public function __construct(
        LoggerInterface $logger,
        User $user,
        JWTAuth $jwt,
    ) {
        $this->logger = $logger;
        $this->user = $user;
        $this->jwt = $jwt;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        # Create User
        $user = $this->user->newQuery()->create($request->validated());

        $this->logger->info("User registered", [
            "user_id" => $user->id,
            "email" => $user->email,
            "ip" => $request->ip(),
        ]);

        return response()->json(
            [
                "status" => "success",
                "message" => "User registered successfully",
            ],
            201,
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = $this->user
            ->newQuery()
            ->where("email", $credentials["email"])
            ->first();

        $this->logger->info("Login attempt", [
            "email" => $credentials["email"],
            "ip" => $request->ip(),
        ]);

        try {
            $token = $this->jwt
                ->Claims([
                    "username" => $user->name,
                ])
                ->attempt($credentials);

            if (!$token) {
                $this->logger->warning("Login failed: invalid credentials", [
                    "email" => $credentials["email"],
                    "ip" => $request->ip(),
                ]);

                return response()->json(
                    [
                        "status" => "error",
                        "error" => "Invalid credentials",
                    ],
                    401,
                );
            }
        } catch (JWTException $e) {
            $this->logger->error("Login failed: could not create token", [
                "email" => $credentials["email"],
                "ip" => $request->ip(),
                "error" => $e->getMessage(),
            ]);

            return response()->json(
                [
                    "status" => "error",
                    "error" => "Could not create token",
                ],
                500,
            );
        }

        return response()->json(
            [
                "status" => "success",
                "message" => "User logged in successfully",
                "token" => $token,
            ],
            200,
        );
    }

    public function logout(): JsonResponse
    {
        $user = auth()->user();

        $this->logger->info("User logged out", [
            "user_id" => $user ? $user->id : null,
            "ip" => request()->ip(),
        ]);

        try {
            // Use JWTAuth::parseToken()->invalidate() to invalidate the token
            $this->jwt->parseToken()->invalidate();
        } catch (JWTException $e) {
            $this->logger->error("Logout failed: could not invalidate token", [
                "user_id" => $user ? $user->id : null,
                "ip" => request()->ip(),
                "error" => $e->getMessage(),
            ]);

            return response()->json(
                [
                    "status" => "error",
                    "error" => "Could not invalidate token",
                ],
                500,
            );
        }

        return response()->json(
            [
                "status" => "success",
                "message" => "User logged out successfully",
            ],
            200,
        );
    }

    public function profile(): JsonResponse
    {
        $this->logger->info("Profile accessed", [
            "user_id" => auth()->id(),
            "ip" => request()->ip(),
        ]);

        try {
            $this->jwt->parseToken()->authenticate();
        } catch (JWTException $e) {
            $this->logger->error("Profile access failed: invalid token", [
                "user_id" => auth()->id(),
                "ip" => request()->ip(),
                "error" => $e->getMessage(),
            ]);

            return response()->json(
                [
                    "status" => "error",
                    "error" => "Invalid token",
                ],
                401,
            );
        }

        $resource = (new UserResource(auth()->user()))->toArray(request());
        return response()->json([
            "status" => "success",
            "data" => $resource,
        ]);
    }
}
