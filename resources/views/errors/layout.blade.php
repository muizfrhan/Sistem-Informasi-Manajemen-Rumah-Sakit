@php
    /**
     * Layout bersama untuk seluruh halaman error (403/404/419/429/500/503).
     *
     * Laravel hanya mengoper `$exception` ke view error, sehingga status dan
     * pesan diambil dari objek exception. Detail teknis mentah (stack trace,
     * nama kelas exception, dsb) tidak pernah dibocorkan ke pengguna; yang
     * ditampilkan hanya penjelasan dalam bahasa manusia.
     *
     * @var \Throwable|null $exception
     */
    $exception = $exception ?? null;

    $code = (int) ($exception?->getStatusCode() ?? 500);
    $message = (string) ($exception?->getMessage() ?? '');

    $details = [
        403 => [
            'title' => 'Akses Ditolak',
            'heading' => 'Anda tidak memiliki izin',
            'description' => 'Akun Anda tidak memiliki hak akses untuk membuka halaman ini. Hubungi Super Admin bila Anda merasa ini keliru.',
            'icon' => 'lock',
            'tone' => 'warning',
        ],
        404 => [
            'title' => 'Halaman Tidak Ditemukan',
            'heading' => 'Halaman tidak ditemukan',
            'description' => 'Alamat yang Anda buka tidak tersedia, sudah dipindahkan, atau tautannya salah ketik.',
            'icon' => 'search',
            'tone' => 'info',
        ],
        419 => [
            'title' => 'Sesi Kedaluwarsa',
            'heading' => 'Sesi Anda sudah berakhir',
            'description' => 'Untuk keamanan, sesi berakhir setelah lama tidak aktif. Silakan masuk kembali lalu ulangi tindakan Anda.',
            'icon' => 'clock',
            'tone' => 'warning',
        ],
        429 => [
            'title' => 'Terlalu Banyak Permintaan',
            'heading' => 'Permintaan terlalu sering',
            'description' => 'Anda melakukan permintaan terlalu banyak dalam waktu singkat. Tunggu sebentar sebelum mencoba lagi.',
            'icon' => 'clock',
            'tone' => 'danger',
        ],
        503 => [
            'title' => 'Layanan Tidak Tersedia',
            'heading' => 'Sistem sedang dalam pemeliharaan',
            'description' => 'Layanan sedang tidak tersedia untuk pemeliharaan terjadwal. Silakan coba kembali beberapa saat lagi.',
            'icon' => 'cog',
            'tone' => 'warning',
        ],
        500 => [
            'title' => 'Kesalahan Sistem',
            'heading' => 'Terjadi kesalahan pada server',
            'description' => 'Permintaan Anda tidak dapat diproses karena kesalahan pada sistem. Tim teknis telah diberi tahu.',
            'icon' => 'exclamation',
            'tone' => 'danger',
        ],
    ];

    $detail = $details[$code] ?? $details[500];

    $tones = [
        'info' => 'bg-info-soft text-info-ink',
        'warning' => 'bg-warning-soft text-warning-ink',
        'danger' => 'bg-danger-soft text-danger-ink',
    ];

    // Judul tab dan pesan tetap memakai kode yang benar.
    $isKnown = array_key_exists($code, $details);
    $showMessage = ! $isKnown && $message !== '';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $detail['title'] }} &middot; {{ config('app.name') }}</title>

    <script>
        (function () {
            try {
                var pref = window.localStorage.getItem('simrs-theme') || 'system';
                var dark = pref === 'dark' || (pref === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', dark);
                document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-canvas text-ink antialiased">
    <div class="flex min-h-full items-center justify-center px-4 py-12">
        <div class="w-full max-w-md text-center">
            <div class="flex justify-center">
                <span class="flex h-16 w-16 items-center justify-center rounded-2xl {{ $tones[$detail['tone']] ?? $tones['danger'] }}">
                    <x-icon :name="$detail['icon']" class="h-8 w-8" />
                </span>
            </div>

            <p class="mt-6 font-mono text-5xl font-bold tracking-tight text-ink tabular-nums">{{ $code }}</p>

            <h1 class="mt-3 text-xl font-bold tracking-tight text-ink">{{ $detail['heading'] }}</h1>

            <p class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-ink-3">
                {{ $showMessage ? \Illuminate\Support\Str::limit($message, 200) : $detail['description'] }}
            </p>

            <div class="mt-8 flex flex-col justify-center gap-2 sm:flex-row">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('login') }}"
                   class="btn btn-md btn-secondary">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Kembali
                </a>

                <a href="{{ route('dashboard') }}" class="btn btn-md btn-primary">
                    <x-icon name="dashboard" class="h-4 w-4" />
                    Ke Dashboard
                </a>
            </div>

            <p class="mt-8 text-xs text-ink-3">
                {{ config('simrs.hospital_name') }} &middot; {{ config('app.name') }}
            </p>
        </div>
    </div>
</body>
</html>
