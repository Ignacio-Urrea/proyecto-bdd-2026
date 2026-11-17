<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'NOMBRE_USUARIO' => ['required', 'string', 'max:16'],
            'password' => ['required', 'string'],
        ]);

        $key = Str::lower($credentials['NOMBRE_USUARIO']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'NOMBRE_USUARIO' => 'Demasiados intentos. Espera un minuto y vuelve a intentar.',
            ]);
        }

        if (! Auth::attempt([
            'NOMBRE_USUARIO' => $credentials['NOMBRE_USUARIO'],
            'password' => $credentials['password'],
        ])) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'NOMBRE_USUARIO' => 'Las credenciales ingresadas no son válidas.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
