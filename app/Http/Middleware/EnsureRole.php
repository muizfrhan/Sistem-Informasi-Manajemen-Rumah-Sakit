<?php

namespace App\Http\Middleware;

use App\Enums\RoleCode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, RoleCode ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        abort_unless($user->hasRole(...$roles), 403, 'Peran Anda tidak diizinkan mengakses halaman ini.');

        return $next($request);
    }
}
