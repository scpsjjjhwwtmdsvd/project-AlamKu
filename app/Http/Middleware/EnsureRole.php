<?php

namespace App\Http\Middleware;

use App\Support\RoleHome;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Batasi akses halaman berdasarkan peran pengguna.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if (! in_array($user->role, $roles, true)) {
            return redirect()
                ->route(RoleHome::routeName($user->role))
                ->with('error', 'Halaman tersebut khusus untuk peran lain. Anda diarahkan ke ruang kerja Anda.');
        }

        return $next($request);
    }
}
