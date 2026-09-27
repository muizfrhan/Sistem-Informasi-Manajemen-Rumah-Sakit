@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('heading', 'Manajemen Pengguna')
@section('subheading', $users->total().' pengguna terdaftar')

@section('content')
    <x-card padding="p-0">
        <x-slot:title>Daftar Pengguna</x-slot:title>
        <x-slot:subtitle>Akun yang dapat mengakses sistem beserta perannya</x-slot:subtitle>
        <x-slot:actions>
            <x-button :href="route('admin.users.create')" size="sm">+ Pengguna Baru</x-button>
        </x-slot:actions>

        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4">
            <div class="min-w-56 flex-1"><x-input name="q" :value="request('q')" placeholder="Cari nama atau username..." /></div>
            <div class="w-56"><x-select name="role_id" :value="request('role_id')" :options="$roles->pluck('name', 'id')->all()" placeholder="Semua peran" /></div>
            <x-button type="submit" variant="secondary">Filter</x-button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Pengguna</th>
                        <th class="px-5 py-3 font-medium">Username</th>
                        <th class="px-5 py-3 font-medium">Peran</th>
                        <th class="px-5 py-3 font-medium">Login Terakhir</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $user)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-100 text-xs font-bold text-teal-700">{{ $user->initials }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-slate-800">{{ $user->name }}</p>
                                        <p class="truncate text-xs text-slate-500">{{ $user->email ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-slate-600">{{ $user->username }}</td>
                            <td class="px-5 py-3"><x-badge :variant="$user->isSuperAdmin() ? 'purple' : 'slate'">{{ $user->role?->name ?? '-' }}</x-badge></td>
                            <td class="whitespace-nowrap px-5 py-3 text-xs text-slate-500">
                                @if ($user->last_login_at)
                                    {{ $user->last_login_at->translatedFormat('d M Y H:i') }}
                                    <span class="block text-slate-400">{{ $user->last_login_ip }}</span>
                                @else
                                    Belum pernah
                                @endif
                            </td>
                            <td class="px-5 py-3"><x-badge :variant="$user->is_active ? 'green' : 'red'">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                            <td class="whitespace-nowrap px-5 py-3 text-right">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-xs font-medium text-teal-700 hover:underline">Edit</a>
                                <span class="mx-1 text-slate-300">|</span>
                                @if (! $user->is(auth()->user()))
                                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-xs font-medium text-slate-600 hover:underline">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                    <span class="mx-1 text-slate-300">|</span>
                                    <x-delete-button :action="route('admin.users.destroy', $user)" method="DELETE"
                                                    :confirm="'Hapus pengguna {$user->name}?'" label="Hapus" />
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())<div class="border-t border-slate-200 px-5 py-3">{{ $users->links() }}</div>@endif
    </x-card>
@endsection
