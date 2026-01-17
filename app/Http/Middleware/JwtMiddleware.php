<?php

namespace App\Http\Middleware;

use Closure;
use Psr\Log\LoggerInterface;
use Illuminate\Http\Request;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth as JWTAuthService;

class JwtMiddleware
{
    protected LoggerInterface $logger;
    protected JWTAuthService $jwt;

    public function __construct(LoggerInterface $logger, JWTAuthService $jwt)
    {
        $this->logger = $logger;
        $this->jwt = $jwt;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        try {
            $user = $this->jwt->parseToken()->authenticate();
        } catch (JWTException $e) {
            $this->logger->error('JWT Authentication failed', ['exception' => $e]);
            return response()->json([
                'status' => 'error',
                'error' => 'Unauthorized'
            ], 401);
        } catch (Exception $e) {
            $this->logger->error('Unexpected error during JWT Authentication', ['exception' => $e]);
            return response()->json([
                'status' => 'error',
                'error' => 'Unauthorized'
            ], 401);
        }

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        // 🔑 Bind user WITHOUT sessions
        $request->setUserResolver(fn() => $user);

        return $next($request);
    }
}
