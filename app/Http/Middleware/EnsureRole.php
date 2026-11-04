<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => __('Unauthenticated.')], 401)
                : redirect()->guest(route('login'));
        }

        abort_unless($user->status === 'active', 403, __('Your account has been suspended.'));

        // A user without a tenant (super admin) may only enter super-admin routes.
        if (! $user->isSuperAdmin() && $user->institute_id === null) {
            abort(403, __('Your account is not linked to an institute.'));
        }

        abort_unless(in_array($user->role, $roles, true), 403, __('You are not authorized to access this area.'));

        return $next($request);
    }
}
