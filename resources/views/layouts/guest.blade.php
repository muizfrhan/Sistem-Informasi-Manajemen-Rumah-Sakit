<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">

    <title>@yield('title', 'Masuk') &middot; {{ config('app.name') }}</title>

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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-full bg-canvas text-ink antialiased">
    <div class="flex min-h-full flex-col items-center justify-center px-4 py-8 sm:py-12">
        <main class="w-full max-w-md">
            @include('partials.flash')
            @yield('content')
        </main>

        <p class="mt-6 text-center text-xs text-ink-3">
            &copy; {{ now()->year }} {{ config('simrs.hospital_name') }}
        </p>
    </div>

    @stack('scripts')
</body>
</html>
