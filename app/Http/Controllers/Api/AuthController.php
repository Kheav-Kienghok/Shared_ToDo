<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Exceptions\JWTException;
use Psr\Log\LoggerInterface;
use Tymon\JWTAuth\JWTAuth as JWTAuthService;

class AuthController extends Controller
{
    protected LoggerInterface $logger;
    protected JWTAuthService $jwt;

    public function __construct(LoggerInterface $logger, JWTAuthService $jwt)
    {
        $this->logger = $logger;
        $this->jwt = $jwt;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        # Create User
        $user = User::create($request->validated());

        $this->logger->info("User registered: user_id={$user->id}, email={$user->email}");

        return response()->json(
            [
                "status" => "success",
                "message" => "User registered successfully",
            ],
            201,
        );
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        // Find user
        $user = User::where("email", $credentials["email"])->first();
        if (!$user) {
            $this->logger->warning("User Login failed: user not found: email={$credentials["email"]}");

            return response()->json(
                [
                    "status" => "error",
                    "error" => "Invalid credentials",
                ],
                401,
            );
        }

        $this->logger->info("User found: user_id={$user->id}, email={$user->email}");

        try {
            // Set token TTL (24 hours)
            $this->jwt->factory()->setTTL(60 * 24);

            $token = $this->jwt
                ->claims([
                    "username" => $user->name,
                ])
                ->attempt($credentials);

            if (!$token) {

                $this->logger->warning("User Login failed: invalid credentials email={$credentials["email"]}");

                return response()->json(
                    [
                        "status" => "error",
                        "error" => "Invalid credentials",
                    ],
                    401,
                );
            }
        } catch (JWTException $e) {

            $this->logger->error("User Login failed: could not create token email={$credentials["email"]}, error={$e->getMessage()}");

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

    public function logout()
    {
        $user = auth()->user();

        if (!$user) {
            $this->logger->warning("User Logout failed: no authenticated user found");
            return response()->json([
                "status" => "error",
                "error" => "No authenticated user",
            ], 401); // unauthorized
        }

        $this->logger->info("User Logout request received for user_id={$user->id}");

        try {
            $this->jwt->parseToken()->invalidate(); // to invalidate the token
        } catch (JWTException $e) {
            $this->logger->error("User Logout failed: could not invalidate token user_id={$user->id} and error={$e->getMessage()}");

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

        $this->logger->info("Authenticating token for profile access for user_id=" . (auth()->id() ?? 'guest'));

        try {
            $this->jwt->parseToken()->authenticate();
        } catch (JWTException $e) {
            $this->logger->info("User profile access failed: invalid token for user_id=" . (auth()->id() ?? 'guest'));

            return response()->json(
                [
                    "status" => "error",
                    "error" => "Invalid token",
                ],
                401,
            );
        }

        $resource = UserResource::make(auth()->user());
        return response()->json([
            "status" => "success",
            "data" => $resource,
        ]);
    }
}
