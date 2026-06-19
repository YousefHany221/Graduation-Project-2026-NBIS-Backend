<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$role): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($this->expectsApiResponse($request)) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect('login');
        }

        if (! in_array($user->role, $role, true)) {
            $message = 'Unauthorized action. This area is for ' . implode(', ', $role) . ' only.';

            if ($this->expectsApiResponse($request)) {
                return response()->json(['message' => $message], 403);
            }

            abort(403, $message);
        }

        return $next($request);
    }

    private function expectsApiResponse(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }
}
