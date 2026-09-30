<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $userRole = $request->user()?->role;

        abort_unless(
            is_string($userRole) && in_array($userRole, $roles, true),
            403
        );

        $request->attributes->set('selected_role', $userRole);

        return $next($request);
    }
}
