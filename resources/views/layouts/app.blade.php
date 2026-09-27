<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') &middot; {{ config('app.name') }}</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏥</text></svg>">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="h-full bg-slate-100 text-slate-800 antialiased">
<div class="flex min-h-full">
    @include('partials.sidebar')

    <div class="flex min-w-0 flex-1 flex-col lg:pl-72">
        @include('partials.topbar')

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                @include('partials.flash')
                @yield('content')
            </div>
        </main>

        <footer class="border-t border-slate-200 px-4 py-4 text-center text-xs text-slate-400 sm:px-6 lg:px-8">
            &copy; {{ now()->year }} {{ config('app.name') }} &middot; environment <span class="font-mono">{{ app()->environment() }}</span>
        </footer>
    </div>
</div>

@stack('modals')
@stack('scripts')
</body>
</html>
