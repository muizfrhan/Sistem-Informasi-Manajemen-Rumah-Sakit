@extends('layouts.app')

@section('title', 'Profil Saya')
@section('heading', 'Profil Saya')
@section('subheading', $user->name)

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card title="Informasi Akun">
                <dl class="grid grid-cols-1 gap-x-6 gap-y-3.5 text-sm sm:grid-cols-2">
                    <div><dt class="text-xs text-slate-500">Nama Lengkap</dt><dd class="mt-0.5 font-medium text-slate-800">{{ $user->name }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Username</dt><dd class="mt-0.5 font-mono text-slate-800">{{ $user->username }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Email</dt><dd class="mt-0.5 text-slate-800">{{ $user->email ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Telepon</dt><dd class="mt-0.5 text-slate-800">{{ $user->phone ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Peran</dt><dd class="mt-0.5"><x-badge :variant="$user->isSuperAdmin() ? 'purple' : 'slate'">{{ $user->role?->name ?? '-' }}</x-badge></dd></div>
                    <div><dt class="text-xs text-slate-500">Status</dt><dd class="mt-0.5"><x-badge :variant="$user->is_active ? 'green' : 'red'">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></dd></div>
                    <div><dt class="text-xs text-slate-500">Login Terakhir</dt><dd class="mt-0.5 text-slate-800">{{ $user->last_login_at?->translatedFormat('d M Y H:i') ?? 'Belum pernah' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">IP Terakhir</dt><dd class="mt-0.5 font-mono text-xs text-slate-600">{{ $user->last_login_ip ?? '-' }}</dd></div>
                </dl>
            </x-card>

            <x-card title="Hak Akses" :subtitle="$user->isSuperAdmin() ? 'Super Admin memiliki akses penuh ke seluruh modul' : $user->permissions->count().' izin aktif'">
                @if ($user->isSuperAdmin())
                    <x-badge variant="purple">Akses penuh semua modul</x-badge>
                @else
                    @php $grouped = $user->permissions->groupBy('group'); @endphp
                    <div class="space-y-4">
                        @foreach ($grouped as $group => $permissions)
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ \App\Enums\Permission::from($permissions->first()->code)?->groupLabel() ?? $group }}</p>
                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                    @foreach ($permissions as $permission)
                                        <span class="rounded-full bg-teal-50 px-2.5 py-0.5 text-xs text-teal-800 ring-1 ring-teal-200">{{ $permission->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl bg-slate-900 p-5 text-center text-white shadow-lg">
                <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-teal-500 text-xl font-bold text-slate-900">{{ $user->initials }}</span>
                <p class="mt-3 text-sm font-semibold">{{ $user->name }}</p>
                <p class="text-xs text-slate-400">{{ $user->role?->name }}</p>
            </div>

            <x-card title="Ganti Kata Sandi">
                <p class="text-xs leading-relaxed text-slate-600">
                    Pengubahan kata sandi dilakukan oleh Super Admin melalui menu
                    <span class="font-medium">Manajemen Pengguna</span>. Hubungi administrator bila
                    kata sandi Anda lupa.
                </p>
            </x-card>
        </div>
    </div>
@endsection
