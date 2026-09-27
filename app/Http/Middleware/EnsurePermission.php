<?php

namespace App\Http\Middleware;

use App\Enums\Permission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, Permission|string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->is_active) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['username' => 'Akun Anda dinonaktifkan.']);
        }

        $resolved = array_map(
            fn (Permission|string $permission) => $permission instanceof Permission
                ? $permission
                : Permission::from($permission),
            $permissions
        );

        abort_unless($user->hasPermission(...$resolved), 403, 'Anda tidak memiliki hak akses untuk aksi ini.');

        return $next($request);
    }
}
