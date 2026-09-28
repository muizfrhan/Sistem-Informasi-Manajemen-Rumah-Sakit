<?php

namespace App\Providers;

use App\Enums\Permission;
use App\Models\Patient;
use App\Models\Poly;
use App\Models\Registration;
use App\Models\User;
use App\Observers\AuditableObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected array $auditable = [
        User::class,
        Patient::class,
        Poly::class,
        Registration::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());

        foreach ($this->auditable as $model) {
            $model::observe(AuditableObserver::class);
        }

        $this->registerPermissionGate();
        $this->configureRateLimiting();
    }

    /**
     * Jembatan antara Gate Laravel dan tabel permission.
     *
     * Setelah didaftarkan, `auth()->user()->can('patient.view')`,
     * `@can('billing.manage')`, dan middleware `can:` semuanya mengikuti
     * matriks permission yang sama. Super Admin otomatis lolos.
     */
    protected function registerPermissionGate(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }

            $permission = Permission::tryFrom($ability);

            return $permission === null ? null : $user->hasPermission($permission);
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('login', fn (Request $request) => [
            // Batas per kombinasi username + IP: pertahanan utama terhadap
            // credential stuffing pada satu akun tertentu.
            Limit::perMinute(10)->by(
                mb_strtolower((string) $request->input('username')).'|'.$request->ip()
            ),

            // Batas per IP sengaja longgar. Banyak pengguna rumah sakit
            // berada di balik satu gateway/NAT yang sama, sehingga batas
            // kecil akan mengunci seluruh pengguna sekaligus.
            Limit::perMinute(60)->by('login-ip|'.$request->ip()),
        ]);

        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('demo', fn (Request $request) => [
            Limit::perMinute(5)->by($request->ip()),
            Limit::perHour(20)->by($request->ip()),
        ]);
    }
}
