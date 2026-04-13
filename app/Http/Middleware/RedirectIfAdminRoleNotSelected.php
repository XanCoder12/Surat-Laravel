<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAdminRoleNotSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Jika user adalah admin tapi belum pilih role → redirect ke halaman pilih role
        if ($user && $user->isAdmin() && !$user->hasSelectedRole()) {
            // Jangan redirect jika sudah di halaman pilih role
            if (!$request->routeIs('admin.role.select') && !$request->routeIs('admin.role.store')) {
                return redirect()->route('admin.role.select');
            }
        }

        return $next($request);
    }
}
