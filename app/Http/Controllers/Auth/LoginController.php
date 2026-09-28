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
    /** Jumlah kegagalan sebelum akun dikunci sementara. */
    private const MAX_ATTEMPTS = 5;

    /** Lama penguncian akun dalam detik. */
    private const LOCK_SECONDS = 900;

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

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'username' => 'Terlalu banyak percobaan login. Coba lagi dalam '
                    .RateLimiter::availableIn($throttleKey).' detik.',
            ]);
        }

        $user = User::where('username', $credentials['username'])->first();

        if ($user !== null && $user->isLockedOut()) {
            throw ValidationException::withMessages([
                'username' => 'Akun terkunci sementara karena terlalu banyak percobaan login. '
                    .'Coba lagi dalam '.$user->locked_until->diffInSeconds(now()).' detik.',
            ]);
        }

        if (! Auth::attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ], (bool) ($credentials['remember'] ?? false))) {
            $this->registerFailedAttempt($user, $throttleKey, $request);

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

        // Berhasil masuk: bersihkan penghitung kegagalan dan kunci sebelumnya.
        RateLimiter::clear($throttleKey);

        $user->forceFill([
            'failed_login_count' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $request->session()->regenerate();

        $this->audit->login($credentials['username'], true);

        return redirect()->intended(route('dashboard'))
            ->with('success', "Selamat datang, {$user->name}.");
    }

    /**
     * Catat kegagalan login: limit per percobaan dan penguncian akun berbasis
     * jumlah kegagalan, sehingga brute force terdeteksi meski dijalankan
     * dari banyak alamat IP berbeda.
     */
    protected function registerFailedAttempt(?User $user, string $throttleKey, Request $request): void
    {
        RateLimiter::hit($throttleKey, 60);

        $this->audit->login($request->string('username')->toString(), false, 'kredensial salah');

        if ($user === null) {
            return;
        }

        $attempts = (int) $user->failed_login_count + 1;

        $user->forceFill([
            'failed_login_count' => $attempts,
            'locked_until' => $attempts >= self::MAX_ATTEMPTS
                ? now()->addSeconds(self::LOCK_SECONDS)
                : $user->locked_until,
        ])->save();
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
