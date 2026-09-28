@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <div class="card overflow-hidden">
        {{-- Kepala --}}
        <div class="flex flex-col items-center gap-3 border-b border-line px-6 py-7 text-center">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand text-brand-fg shadow-e2">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
            </span>

            <div>
                <h1 class="text-lg font-bold tracking-tight text-ink">Masuk ke Sistem</h1>
                <p class="mt-0.5 text-sm text-ink-3">{{ config('simrs.hospital_name') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-4 px-6 py-6">
            @csrf

            <x-input name="username" label="Username" required autocomplete="username"
                     autofocus placeholder="Masukkan username"
                     hint="Gunakan username yang diberikan administrator rumah sakit." />

            <div>
                <x-input name="password" label="Kata Sandi" type="password" required
                         autocomplete="current-password" placeholder="Masukkan kata sandi" />

                <p class="mt-2 flex items-start gap-1.5 text-xs text-ink-3">
                    <x-icon name="info" class="mt-px h-3.5 w-3.5 shrink-0" />
                    <span>Akun terkunci sementara setelah 5 kali gagal login. Hubungi administrator bila lupa kata sandi.</span>
                </p>
            </div>

            <x-checkbox name="remember" label="Tetap masuk di perangkat ini" />

            <x-button type="submit" size="lg" class="w-full">Masuk</x-button>
        </form>

        @if (app()->environment('local'))
            <div class="border-t border-line bg-surface-2 px-6 py-4">
                <p class="text-[11px] font-semibold tracking-wide text-ink-3 uppercase">
                    Akun demo &mdash; sandi: <code class="font-mono normal-case">password</code>
                </p>
                <div class="mt-2 grid grid-cols-2 gap-1.5 text-[11px] sm:grid-cols-3">
                    @foreach ([
                        'superadmin' => 'Super Admin',
                        'receptionist' => 'Resepsionis',
                        'dokter' => 'Dokter',
                        'perawat' => 'Perawat',
                        'apoteker' => 'Apoteker',
                        'kasir' => 'Kasir',
                        'manajemen' => 'Manajemen',
                    ] as $login => $role)
                        <button type="button" data-fill-login="{{ $login }}"
                                class="rounded-md bg-surface px-2 py-1.5 text-left font-mono font-semibold text-brand transition hover:bg-brand-soft"
                                aria-label="Isi username {{ $role }} dan kata sandi demo">
                            {{ $login }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <p class="mt-6 text-center text-xs text-ink-3">
        <a href="{{ route('home') }}" class="font-medium transition hover:text-ink hover:underline">
            &larr; Kembali ke beranda
        </a>
    </p>
@endsection

@push('scripts')
    @if (app()->environment('local'))
        <script>
            document.querySelectorAll('[data-fill-login]').forEach((button) => {
                button.addEventListener('click', () => {
                    document.getElementById('username').value = button.dataset.fillLogin;
                    document.getElementById('password').value = 'password';
                    document.getElementById('password').focus();
                });
            });
        </script>
    @endif
@endpush
