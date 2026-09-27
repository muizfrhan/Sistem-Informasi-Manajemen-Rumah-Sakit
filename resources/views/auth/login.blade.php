@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <div class="overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="bg-slate-900 px-8 py-6 text-center">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-teal-500 text-xl font-bold text-slate-900">S</span>
            <h1 class="mt-3 text-lg font-bold text-white">{{ config('app.name') }}</h1>
            <p class="mt-1 text-xs text-slate-400">Sistem Informasi Manajemen Rumah Sakit &amp; Rekam Medis Elektronik</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-4 p-8">
            @csrf

            <div>
                <label for="username" class="mb-1.5 block text-sm font-medium text-slate-700">Username</label>
                <input id="username" name="username" type="text" required autofocus autocomplete="username"
                       value="{{ old('username') }}"
                       class="block w-full rounded-lg border-0 px-3 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset sm:text-sm {{ $errors->has('username') ? 'ring-rose-400' : 'ring-slate-300 focus:ring-teal-600' }}">
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Kata Sandi</label>
                <input id="password" name="password" type="password" required autocomplete="current-password"
                       class="block w-full rounded-lg border-0 px-3 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset sm:text-sm {{ $errors->has('password') ? 'ring-rose-400' : 'ring-slate-300 focus:ring-teal-600' }}">
            </div>

            <div class="flex items-center justify-between">
                <label for="remember" class="flex items-center gap-2 text-sm text-slate-600">
                    <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))
                           class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                    Ingat saya
                </label>
            </div>

            <x-button type="submit" size="lg" class="w-full">Masuk</x-button>
        </form>

        <div class="border-t border-slate-100 bg-slate-50 px-8 py-4">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Akun demo (sandi: password)</p>
            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1 text-[11px] text-slate-600 sm:grid-cols-3">
                @foreach ([
                    'superadmin' => 'Super Admin',
                    'receptionist' => 'Resepsionis',
                    'dokter' => 'Dokter',
                    'perawat' => 'Perawat',
                    'apoteker' => 'Apoteker',
                    'kasir' => 'Kasir',
                    'manajemen' => 'Manajemen',
                ] as $login => $label)
                    <button type="button" class="text-left font-mono text-teal-700 hover:underline" data-fill-login="{{ $login }}">{{ $login }}</button>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-fill-login]').forEach((el) => {
            el.addEventListener('click', () => {
                document.getElementById('username').value = el.dataset.fillLogin;
                document.getElementById('password').value = 'password';
                document.getElementById('password').focus();
            });
        });
    </script>
@endpush
