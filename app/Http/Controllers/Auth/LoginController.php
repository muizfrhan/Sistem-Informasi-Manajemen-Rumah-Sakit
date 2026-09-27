<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function show(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ], [], [
            'username' => 'username',
            'password' => 'kata sandi',
        ]);

        $throttleKey = 'login:'.mb_strtolower($credentials['username']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (! Auth::attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ], (bool) ($credentials['remember'] ?? false))) {
            $this->audit->login($credentials['username'], false, 'kredensial salah');

            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'username' => 'Username atau kata sandi tidak sesuai.',
            ]);
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $this->audit->login($credentials['username'], false, 'akun nonaktif');

            throw ValidationException::withMessages([
                'username' => 'Akun Anda dinonaktifkan. Hubungi Super Admin.',
            ]);
        }

        if ($user->isLockedOut()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'username' => 'Akun terkunci sementara karena terlalu banyak percobaan login.',
            ]);
        }

        if ($user->failed_login_count > 0) {
            $user->forceFill(['failed_login_count' => 0, 'locked_until' => null])->save();
        }

        $request->session()->regenerate();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $this->audit->login($credentials['username'], true);

        return redirect()->intended(route('dashboard'))
            ->with('success', "Selamat datang, {$user->name}.");
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->audit->logout();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah keluar dari sistem.');
    }
}
