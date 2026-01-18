<?php

namespace App\Http\Middleware;

use App\Models\Lists;
use Auth;
use Closure;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class CheckListRole
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated'
            ], 401);
        }

        $list = $request->route('list');

        if (!$list instanceof Lists) {
            return response()->json([
                'status' => 'error',
                'message' => 'List not found'
            ], 404);
        }

        $this->logger->info('Checking list role', [
            'user_id' => $user->id,
            'list_id' => $list->id,
            'required_roles' => $roles,
            'ip' => $request->ip(),
        ]);

        $userOnList = $list->users()
            ->where('users.id', $user->id)
            ->first();

        if (!$userOnList || !in_array($userOnList->pivot->role, $roles, true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to perform this action'
            ], 403);
        }

        return $next($request);
    }
}
