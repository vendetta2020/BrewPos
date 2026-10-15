<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form.
     *
     * Satisfies SRS: AUTH-01 — login page with email/username and password input.
     */
    public function show()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * Validates credentials, authenticates the user, and redirects based on role.
     * Satisfies SRS: AUTH-02 — credential validation with clear error messages.
     * Satisfies SRS: AUTH-04 — redirect based on role (admin→/dashboard, cashier→/pos, barista→/kds).
     */
    public function store(Request $request)
    {
        // Validate input
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Attempt authentication
        if (!Auth::attempt($credentials, $request->filled('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password tidak sesuai.',
            ]);
        }

        // Verify active status: deactivated employees must not be allowed to log in
        $user = Auth::user();
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.',
            ]);
        }

        // Regenerate session ID for security
        $request->session()->regenerate();

        // Redirect based on authenticated user's role
        $user = Auth::user();

        return match ($user->role) {
            'admin'   => redirect()->intended('/dashboard'),
            'cashier' => redirect()->intended('/pos'),
            'barista' => redirect()->intended('/kds'),
            default   => redirect()->intended('/'),
        };
    }

    /**
     * Log the user out of the application.
     *
     * Satisfies SRS: AUTH-06 — session management and logout.
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
