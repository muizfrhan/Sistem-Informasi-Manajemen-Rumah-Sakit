<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Rekam Medis Elektronik Terintegrasi untuk Rumah Sakit') &middot; {{ config('app.name') }}</title>
    <meta name="description" content="@yield('description', 'SIMRS berbasis Rekam Medis Elektronik (RME) yang aman, sesuai standar SATUSEHAT, dan membantu rumah sakit memangkas biaya administrasi, mempercepat antrean, serta meningkatkan akurasi klaim BPJS.')">

    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'Rekam Medis Elektronik Terintegrasi untuk Rumah Sakit')">
    <meta property="og:description" content="@yield('description', 'SIMRS berbasis Rekam Medis Elektronik (RME) yang aman, sesuai standar SATUSEHAT, dan efisien.')">
    <meta property="og:url" content="{{ route('home') }}">

    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏥</text></svg>">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="font-inter bg-white text-slate-800 antialiased">

<div x-data="{ nav: false, demo: @js(session('open_demo_form', false)) }" @keydown.escape.window="demo = false">

    <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/85 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-7xl items-center gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-700 text-base font-bold text-white">S</span>
                <span class="leading-tight">
                    <span class="block text-sm font-bold text-slate-900">{{ config('app.name') }}</span>
                    <span class="hidden text-[11px] text-slate-500 sm:block">Hospital Information System</span>
                </span>
            </a>

            <nav class="ml-auto hidden items-center gap-8 text-sm font-medium text-slate-600 lg:flex">
                <a href="{{ route('home') }}#masalah" class="hover:text-blue-700">Masalah</a>
                <a href="{{ route('home') }}#solusi" class="hover:text-blue-700">Fitur</a>
                <a href="{{ route('home') }}#keamanan" class="hover:text-blue-700">Keamanan</a>
                <a href="{{ route('home') }}#harga" class="hover:text-blue-700">Harga</a>
                <a href="{{ route('home') }}#faq" class="hover:text-blue-700">FAQ</a>
            </nav>

            <div class="ml-auto flex items-center gap-2 lg:ml-0">
                @auth
                    <a href="{{ route('dashboard') }}" class="hidden text-sm font-semibold text-blue-700 hover:text-blue-800 sm:inline">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="hidden text-sm font-semibold text-slate-600 hover:text-slate-900 sm:inline">Masuk</a>
                @endauth
                <button type="button" @click="demo = true"
                        class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Jadwalkan Demo
                </button>
                <button type="button" @click="nav = !nav" aria-label="Menu"
                        class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        <div x-show="nav" x-collapse class="border-t border-slate-200 bg-white lg:hidden">
            <nav class="mx-auto max-w-7xl space-y-1 px-4 py-4 text-sm sm:px-6">
                <a href="{{ route('home') }}#masalah" @click="nav = false" class="block rounded-lg px-3 py-2 font-medium text-slate-700 hover:bg-slate-50">Masalah</a>
                <a href="{{ route('home') }}#solusi" @click="nav = false" class="block rounded-lg px-3 py-2 font-medium text-slate-700 hover:bg-slate-50">Fitur</a>
                <a href="{{ route('home') }}#keamanan" @click="nav = false" class="block rounded-lg px-3 py-2 font-medium text-slate-700 hover:bg-slate-50">Keamanan &amp; Kepatuhan</a>
                <a href="{{ route('home') }}#harga" @click="nav = false" class="block rounded-lg px-3 py-2 font-medium text-slate-700 hover:bg-slate-50">Harga</a>
                <a href="{{ route('home') }}#faq" @click="nav = false" class="block rounded-lg px-3 py-2 font-medium text-slate-700 hover:bg-slate-50">FAQ</a>
                @guest
                    <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2 font-medium text-blue-700 hover:bg-blue-50">Masuk ke sistem</a>
                @else
                    <a href="{{ route('dashboard') }}" class="block rounded-lg px-3 py-2 font-medium text-blue-700 hover:bg-blue-50">Buka dashboard</a>
                @endguest
            </nav>
        </div>
    </header>

    <main>
        @include('partials.flash')
        @yield('content')
    </main>

    <footer class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-5">
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-700 text-base font-bold text-white">S</span>
                        <span class="text-sm font-bold text-slate-900">{{ config('app.name') }}</span>
                    </div>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-600">
                        Sistem Informasi Manajemen Rumah Sakit berbasis Rekam Medis Elektronik untuk
                        pelayanan yang aman, terukur, dan sesuai regulasi.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-200">Standar SATUSEHAT</span>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-200">UUPDP No. 27/2022</span>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-200">ICD-10 WHO</span>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Produk</h3>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-600">
                        <li><a href="{{ route('home') }}#solusi" class="hover:text-blue-700">Rekam Medis Elektronik</a></li>
                        <li><a href="{{ route('home') }}#solusi" class="hover:text-blue-700">Antrean Digital</a></li>
                        <li><a href="{{ route('home') }}#solusi" class="hover:text-blue-700">Farmasi &amp; Resep</a></li>
                        <li><a href="{{ route('home') }}#solusi" class="hover:text-blue-700">Billing &amp; Klaim BPJS</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Perusahaan</h3>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-600">
                        <li><a href="{{ route('home') }}#keamanan" class="hover:text-blue-700">Keamanan Data</a></li>
                        <li><a href="{{ route('home') }}#harga" class="hover:text-blue-700">Harga &amp; Paket</a></li>
                        <li><a href="{{ route('home') }}#faq" class="hover:text-blue-700">FAQ</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-blue-700">Masuk</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Hubungi Kami</h3>
                    <ul class="mt-4 space-y-2.5 text-sm text-slate-600">
                        <li>Jl. Contoh No. 00, Jakarta</li>
                        <li><a href="tel:+622112345678" class="hover:text-blue-700">(021) 1234-5678</a></li>
                        <li><a href="mailto:halo@simrs-rme.test" class="hover:text-blue-700">halo@simrs-rme.test</a></li>
                        <li class="text-slate-500">Senin&ndash;Jumat, 08.00&ndash;17.00 WIB</li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 flex flex-col items-start justify-between gap-3 border-t border-slate-200 pt-6 text-xs text-slate-500 sm:flex-row sm:items-center">
                <p>&copy; {{ now()->year }} {{ config('app.name') }}. Seluruh hak cipta dilindungi.</p>
                <p class="flex flex-wrap gap-x-5 gap-y-1">
                    <span>Kebijakan Privasi</span>
                    <span>Syarat &amp; Ketentuan</span>
                    <span>Dokumentasi Teknis</span>
                </p>
            </div>
        </div>
    </footer>

    {{-- Sticky CTA khusus mobile --}}
    <div class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 p-3 backdrop-blur lg:hidden">
        <div class="flex items-center gap-2">
            <button type="button" @click="demo = true"
                    class="flex-1 rounded-lg bg-blue-700 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
                Jadwalkan Demo Gratis
            </button>
            <a href="{{ route('login') }}" aria-label="Masuk"
               class="rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700">
                Masuk
            </a>
        </div>
    </div>
    <div class="h-20 lg:hidden"></div>

    <div x-show="demo" x-cloak class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="demo = false"></div>

        <div class="relative mx-auto mt-8 w-full max-w-2xl">
            <div class="overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 bg-slate-50 px-6 py-5">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Jadwalkan Demo Gratis</h2>
                        <p class="mt-1 text-sm text-slate-600">30 menit bersama tim kami. Tanpa biaya, tanpa komitmen.</p>
                    </div>
                    <button type="button" @click="demo = false" aria-label="Tutup"
                            class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-200 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                @include('landing.partials.demo-form')
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs text-slate-600">
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Data tersimpan aman
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Balasan 1x24 jam kerja
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Tanpa spam
                </span>
            </div>
        </div>
    </div>

</div>

@stack('scripts')
</body>
</html>
