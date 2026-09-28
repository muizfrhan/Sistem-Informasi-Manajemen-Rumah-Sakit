{{--
    Shell landing page.

    Halaman publik dirender sebagai satu aplikasi Vue yang di-mount di #landing-app.
    Blade tetap menjadi backend: seluruh data yang dibutuhkan komponen dikirim lewat
    #landing-config (URL, token CSRF, flash session, input lama).

    Dua lapis fallback:
      1. <noscript> memuat form demo native, supaya permintaan tetap bisa terkirim
         tanpa JavaScript.
      2. Konten utama (hero, fitur, dst.) sengaja tidak ditulis ganda di Blade —
         akan jadi dua sumber kebenaran yang pasti berbeda seiring waktu. Yang
         tersisa tanpa JS adalah tautan navigasi ke section.
--}}
@php
    $appName = config('app.name');
    $tagline = 'Hospital Information System';
    $isGuest = auth()->guest();

    $landingConfig = [
        'appName' => $appName,
        'tagline' => $tagline,
        'loginUrl' => route('login'),
        'dashboardUrl' => $isGuest ? null : route('dashboard'),
        'demoAction' => route('demo.store'),
        'csrfToken' => csrf_token(),
        'year' => (int) now()->year,
        'contact' => [
            'address' => config('simrs.hospital_address'),
            'phone' => config('simrs.hospital_phone'),
        ],
        'demoDefaults' => [
            'facility_type' => old('facility_type', 'HOSPITAL'),
        ],
        'oldInput' => [
            'name' => old('name'),
            'position' => old('position'),
            'institution' => old('institution'),
            'email' => old('email'),
            'phone' => old('phone'),
            'facility_type' => old('facility_type'),
            'bed_count' => old('bed_count'),
            'message' => old('message'),
        ],
        'serverErrors' => $errors->getBag('default')->getMessages(),
        'demoSuccess' => session()->has('success'),
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $appName }} — Sistem Informasi Manajemen Rumah Sakit</title>
    <meta name="description" content="Platform manajemen rumah sakit terintegrasi: pendaftaran dan antrean, rekam medis elektronik, farmasi, penagihan, rawat inap, jadwal dokter, dan laporan. NIK terenkripsi dengan 22 izin akses per peran.">
    <meta name="theme-color" content="#060d1f">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $appName }} — Sistem Informasi Manajemen Rumah Sakit">
    <meta property="og:description" content="Pendaftaran, antrean, rekam medis, farmasi, penagihan, dan rawat inap dalam satu platform.">
    <meta property="og:url" content="{{ route('home') }}">

    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%230d9488'/><path d='M16 8v16M8 16h16' stroke='white' stroke-width='4' stroke-linecap='round'/></svg>">

    @vite(['resources/css/landing.css', 'resources/js/landing/main.js'])
</head>
<body class="lp-page">
    <div id="landing-app">
        {{-- Placeholder sebelum Vue selesai mount. disembunyikan lewat CSS. --}}
        <div class="lp-boot" role="status" aria-live="polite">
            <p class="lp-boot__title">{{ $appName }}</p>
            <p class="lp-boot__text">
                Memuat sistem informasi manajemen rumah sakit&hellip;
            </p>
            <noscript>
                <p class="lp-boot__noscript">
                    Halaman ini menggunakan JavaScript untuk menampilkan bagian interaktif.
                    Formulir permintaan demo di bawah tetap bisa diisi tanpa JavaScript.
                </p>
            </noscript>
        </div>
    </div>

    {{-- Fallback tanpa JavaScript: form demo native, POST ke route yang sama. --}}
    <noscript>
        <div class="lp-nojs">
            <div class="lp-nojs__inner">
                <h2>Minta demo</h2>
                <p>Isi formulir berikut. Data dikirim ke administrator sistem.</p>

                <form method="POST" action="{{ route('demo.store') }}">
                    @csrf

                    <label for="nojs-name">Nama lengkap *</label>
                    <input id="nojs-name" name="name" type="text" required maxlength="150">

                    <label for="nojs-position">Jabatan</label>
                    <input id="nojs-position" name="position" type="text" maxlength="100">

                    <label for="nojs-institution">Nama rumah sakit / klinik *</label>
                    <input id="nojs-institution" name="institution" type="text" required maxlength="200">

                    <label for="nojs-email">Email kantor *</label>
                    <input id="nojs-email" name="email" type="email" required maxlength="150">

                    <label for="nojs-phone">Nomor WhatsApp / telepon *</label>
                    <input id="nojs-phone" name="phone" type="tel" required maxlength="30">

                    <label for="nojs-facility">Jenis fasilitas *</label>
                    <select id="nojs-facility" name="facility_type" required>
                        <option value="HOSPITAL">Rumah Sakit</option>
                        <option value="CLINIC">Klinik</option>
                        <option value="PRIVATE_PRACTICE">Praktik Mandiri</option>
                        <option value="OTHER">Lainnya</option>
                    </select>

                    <label for="nojs-bed">Jumlah tempat tidur</label>
                    <input id="nojs-bed" name="bed_count" type="number" min="0" max="5000">

                    <label for="nojs-message">Kebutuhan Anda</label>
                    <textarea id="nojs-message" name="message" rows="3" maxlength="2000"></textarea>

                    <div style="position: absolute; left: -9999px" aria-hidden="true">
                        <label for="nojs-website">Website perusahaan</label>
                        <input id="nojs-website" name="company_website" type="text" tabindex="-1" autocomplete="off">
                    </div>

                    @error('name')<p class="lp-boot__error">{{ $message }}</p>@enderror
                    @error('institution')<p class="lp-boot__error">{{ $message }}</p>@enderror
                    @error('email')<p class="lp-boot__error">{{ $message }}</p>@enderror
                    @error('phone')<p class="lp-boot__error">{{ $message }}</p>@enderror
                    @error('facility_type')<p class="lp-boot__error">{{ $message }}</p>@enderror
                    @error('throttle')<p class="lp-boot__error">{{ $message }}</p>@enderror

                    <button type="submit">Kirim Permintaan Demo</button>
                </form>

                <p><a href="{{ route('login') }}">Masuk ke sistem</a></p>
            </div>
        </div>
    </noscript>

    {{-- JSON_HEX_* mencegah string berisi "</script>" keluar dari blok script.
         JSON_UNESCAPED_UNICODE supaya teks Indonesia tetap terbaca. --}}
    <script type="application/json" id="landing-config">{!! json_encode($landingConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
</body>
</html>
