<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        $key = Str::lower($credentials['NOMBRE_USUARIO']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'NOMBRE_USUARIO' => 'Demasiados intentos. Espera un minuto y vuelve a intentar.',
            ]);
        }

        $user = Usuario::query()
            ->whereRaw('UPPER(NOMBRE_USUARIO) = ?', [Str::upper($credentials['NOMBRE_USUARIO'])])
            ->first();

        $validPassword = false;

        if ($user) {
            $authPassword = $user->getAuthPassword();
            $validPassword = $authPassword !== ''
                && Hash::check($credentials['password'], $authPassword);
        }

        if (! $user || ! $validPassword) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'NOMBRE_USUARIO' => 'Las credenciales ingresadas no son válidas.',
            ]);
        }

        Auth::login($user);

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
