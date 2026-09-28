<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    <meta name="description" content="@yield('meta_description', 'Sistem Informasi Manajemen Rumah Sakit ' . config('simrs.hospital_name') . '.')">

    <title>@yield('title', 'Dashboard') &middot; {{ config('app.name') }}</title>

    {{-- Anti-FOUC: tema diterapkan sebelum stylesheet dirender. --}}
    <script>
        (function () {
            try {
                var pref = window.localStorage.getItem('simrs-theme') || 'system';
                var dark = pref === 'dark' || (pref === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', dark);
                document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
            } catch (e) { /* abaikan */ }
        })();
    </script>

    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%22 0 0 100 100%22><rect width=%22100%22 height=%22100%22 rx=%2222%22 fill=%22%230d9488%22/><path d=%22M50 24v52M24 50h52%22 stroke=%22white%22 stroke-width=%2214%22 stroke-linecap=%22round%22/></svg>">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
{{-- `x-data` di <body> adalah akar Alpine untuk SELURUH halaman.

     Alpine hanya menginisialisasi subtree yang berakar pada elemen ber-[x-data],
     [x-init], atau selector plugin. Tanpa akar di sini, setiap `x-on:click`,
     `x-show`, dan `x-for` yang berada di luar `x-data` miliknya sendiri
     (tombol mode gelap, pembuka menu mobile, tombol aksi di dalam baris tabel,
     toast flash) tidak pernah dijalankan dan diam-diam mati. --}}
<body x-data class="min-h-full bg-canvas text-ink antialiased">
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[80] focus:rounded-lg focus:bg-brand focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-brand-fg">
        Lewati ke konten utama
    </a>

    @include('partials.sidebar')

    <div class="flex min-h-full flex-col lg:pl-[17rem]">
        @include('partials.topbar')

        <main id="main-content" class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-[100rem]">
                @include('partials.flash')
                @yield('content')
            </div>
        </main>

        <footer class="border-t border-line px-4 py-4 text-center text-xs text-ink-3 sm:px-6 lg:px-8">
            &copy; {{ now()->year }} {{ config('simrs.hospital_name') }} &middot;
            {{ config('app.name') }}
            <span class="hidden sm:inline">&middot; environment <span class="font-mono">{{ app()->environment() }}</span></span>
        </footer>
    </div>

    {{-- Dialog konfirmasi global untuk seluruh aksi destruktif. --}}
    <x-confirm-dialog />

    @stack('modals')
    @stack('scripts')
</body>
</html>
