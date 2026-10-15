<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Checks if the authenticated user has one of the allowed roles.
     * If not, aborts with 403 Forbidden response.
     *
     * Satisfies SDD section 7.1 — Role Middleware design.
     * Satisfies SRS: AUTH-03 — role-based access control.
     *
     * Usage in routes:
     *   Route::get('/dashboard', [DashboardController::class, 'show'])->middleware('role:admin');
     *   Route::get('/pos', [PosController::class, 'show'])->middleware('role:cashier');
     *   Route::get('/kds', [KdsController::class, 'show'])->middleware('role:barista');
     *
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles  Variable number of role names allowed.
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.']);
        }

        if (!in_array(Auth::user()->role, $roles)) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
