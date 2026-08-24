<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Restrict access to the admin panel by role.
     *
     * Without arguments, both Admin and Manager may proceed. With the
     * `admin` argument, only Admin may proceed (users & settings modules).
     */
    public function handle(Request $request, Closure $next, ?string $level = null): Response
    {
        $user = $request->user();

        abort_unless($user !== null, 403);

        $allowed = $level === 'admin'
            ? $user->role === UserRole::Admin
            : in_array($user->role, [UserRole::Admin, UserRole::Manager], true);

        abort_unless($allowed, 403);

        return $next($request);
    }
}
