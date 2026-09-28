@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('heading', 'Manajemen Pengguna')
@section('subheading', $users->total().' pengguna terdaftar')

@php
    $search = trim((string) request('q', ''));
    $roleId = trim((string) request('role_id', ''));
    $hasFilter = $search !== '' || $roleId !== '';
    $currentUserId = auth()->id();

    $emptyCta = $hasFilter
        ? ['label' => 'Reset Filter', 'href' => route('admin.users.index'), 'variant' => 'secondary', 'icon' => 'refresh', 'permission' => null]
        : ['label' => 'Pengguna Baru', 'href' => route('admin.users.create'), 'variant' => 'primary', 'icon' => 'user-plus', 'permission' => 'user.manage'];

    $pageDescription = 'Total '.number_format($users->total(), 0, ',', '.').' pengguna'
        .($search !== '' ? ' untuk pencarian '.$search : '');

    $activeCount = $users->getCollection()->where('is_active', true)->count();
@endphp

@section('content')
    <div class="space-y-5">
        <x-page-header title="Manajemen Pengguna" :description="$pageDescription">
            <x-slot:actions>
                <x-button permission="user.manage" :href="route('admin.users.create')" icon="user-plus">
                    Pengguna Baru
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            <x-stat-card label="Total Pengguna" :value="number_format($users->total(), 0, ',', '.')"
                         unit="akun" tone="brand" icon="users" compact />
            <x-stat-card label="Akun Aktif" :value="number_format($activeCount, 0, ',', '.')"
                         unit="akun" tone="success" icon="check-circle"
                         hint="Dapat masuk ke sistem" compact />
            <x-stat-card label="Peran Tersedia" :value="number_format($roles->count(), 0, ',', '.')"
                         unit="peran" tone="info" icon="shield"
                         hint="Menentukan hak akses pengguna" compact />
        </div>

        <x-card padding="p-0">
            <x-slot:title>Daftar Pengguna</x-slot:title>
            <x-slot:subtitle>Akun yang dapat mengakses sistem beserta peran dan hak aksesnya</x-slot:subtitle>

            <x-filter-bar :action="route('admin.users.index')" :reset="route('admin.users.index')">
                <x-slot:fields>
                    <div>
                        <label for="q" class="field-label">Pencarian</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-ink-3">
                                <x-icon name="search" class="h-4 w-4" />
                            </span>
                            <input id="q" name="q" type="search" value="{{ $search }}"
                                   placeholder="Nama atau username" class="field-control pl-9">
                        </div>
                    </div>

                    <div>
                        <label for="user_role_filter" class="field-label">Peran</label>
                        <select id="user_role_filter" name="role_id" class="field-control">
                            <option value="">Semua peran</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected((string) $role->id === $roleId)>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </x-slot:fields>

                <x-slot:default>
                    <span class="text-xs text-ink-3">
                        Menampilkan {{ $users->firstItem() ?? 0 }}&ndash;{{ $users->lastItem() ?? 0 }}
                        dari {{ number_format($users->total(), 0, ',', '.') }} pengguna
                    </span>
                </x-slot:default>
            </x-filter-bar>

            @if ($hasFilter)
                <div class="flex items-start gap-2.5 border-b border-line bg-warning-soft/50 px-4 py-3">
                    <x-icon name="info" class="mt-0.5 h-4 w-4 shrink-0 text-warning-ink" />
                    <p class="text-xs text-ink-2">
                        Filter sedang aktif. Klik <strong>Reset</strong> pada bilah filter di atas untuk melihat seluruh pengguna.
                    </p>
                </div>
            @endif

            <x-data-table :cols="$users->count()" min-width="58rem"
                          empty="Pengguna tidak ditemukan"
                          empty-message="Belum ada pengguna yang cocok dengan filter ini. Ubah pencarian atau tambahkan pengguna baru."
                          empty-icon="users">
                <x-slot:emptyAction>
                    <x-button :href="$emptyCta['href']" :variant="$emptyCta['variant']" size="sm"
                              :icon="$emptyCta['icon']" :permission="$emptyCta['permission']">
                        {{ $emptyCta['label'] }}
                    </x-button>
                </x-slot:emptyAction>

                <x-slot:head>
                    <tr>
                        <th scope="col">Pengguna</th>
                        <th scope="col" class="hidden sm:table-cell">Username</th>
                        <th scope="col">Peran</th>
                        <th scope="col" class="hidden lg:table-cell">Login Terakhir</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-right">Aksi</th>
                    </tr>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($users as $user)
                        @php $isSelf = (int) $user->id === (int) $currentUserId; @endphp

                        <tr @class(['bg-brand-soft/30' => $isSelf])>
                            <td data-label="Pengguna">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-soft text-[11px] font-bold text-brand-ink">
                                        {{ $user->initials }}
                                    </span>
                                    <div class="min-w-0">
                                        <span class="block truncate font-semibold text-ink">{{ $user->name }}</span>
                                        <span class="block truncate text-xs text-ink-3">
                                            {{ $user->email ?? 'Tanpa email' }}
                                            @if ($isSelf)
                                                <span class="font-semibold text-brand-ink">(Anda)</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td data-label="Username" class="hidden sm:table-cell">
                                <span class="font-mono text-xs text-ink-2">{{ $user->username }}</span>
                            </td>

                            <td data-label="Peran">
                                @if ($user->role)
                                    <x-badge :variant="$user->isSuperAdmin() ? 'brand' : 'slate'">
                                        {{ $user->role->name }}
                                    </x-badge>
                                @else
                                    <span class="text-xs text-ink-3">Tanpa peran</span>
                                @endif
                            </td>

                            <td data-label="Login Terakhir" class="hidden lg:table-cell">
                                @if ($user->last_login_at)
                                    <span class="text-xs text-ink-2">
                                        {{ $user->last_login_at->translatedFormat('d M Y H:i') }}
                                    </span>
                                    <span class="mt-0.5 block font-mono text-[11px] text-ink-3">
                                        {{ $user->last_login_ip ?? 'IP tidak tercatat' }}
                                    </span>
                                @else
                                    <span class="text-xs text-ink-3">Belum pernah</span>
                                @endif
                            </td>

                            <td data-label="Status">
                                <x-active-badge :active="$user->is_active" />
                            </td>

                            <td data-label="Aksi">
                                @if ($isSelf)
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.users.edit', $user) }}" title="Ubah data pengguna"
                                           aria-label="Ubah data pengguna {{ $user->name }}"
                                           class="rounded-lg p-2 text-ink-3 transition hover:bg-surface-2 hover:text-ink">
                                            <x-icon name="pencil" class="h-4 w-4" />
                                        </a>
                                        <span class="text-[11px] text-ink-3">Akun Anda sendiri tidak dapat dinonaktifkan atau dihapus</span>
                                    </div>
                                @else
                                    @php
                                        // Judul konfirmasi bergantung pada status saat ini,
                                        // jadi payload dirakit di PHP lalu dikirim lewat
                                        // prop `confirm` (tidak ada direksi Blade di
                                        // dalam daftar atribut komponen).
                                        $toggleConfirm = [
                                            'action' => route('admin.users.toggle', $user),
                                            'method' => 'PATCH',
                                            'title' => $user->is_active
                                                ? 'Nonaktifkan akun '.$user->name.'?'
                                                : 'Aktifkan akun '.$user->name.'?',
                                            'message' => $user->is_active
                                                ? 'Akun '.$user->username.' tidak dapat masuk ke sistem sampai diaktifkan kembali.'
                                                : 'Akun '.$user->username.' akan dapat masuk ke sistem kembali.',
                                            'confirmLabel' => $user->is_active ? 'Nonaktifkan' : 'Aktifkan',
                                        ];
                                    @endphp

                                    <div class="flex flex-wrap items-center justify-end gap-1">
                                        <a href="{{ route('admin.users.edit', $user) }}" title="Ubah data pengguna"
                                           aria-label="Ubah data pengguna {{ $user->name }}"
                                           class="rounded-lg p-2 text-ink-3 transition hover:bg-surface-2 hover:text-ink">
                                            <x-icon name="pencil" class="h-4 w-4" />
                                        </a>

                                        <x-button type="button" size="sm" variant="secondary" icon="refresh"
                                                  :confirm="$toggleConfirm">
                                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </x-button>

                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" size="sm" variant="danger" icon="trash"
                                                      x-on:click.prevent="$dispatch('confirm', {
                                                          action: $el.form.action,
                                                          method: 'DELETE',
                                                          title: 'Hapus pengguna {{ $user->name }}?',
                                                          message: 'Akun {{ $user->username }} akan dihapus permanen dan tidak dapat digunakan kembali.',
                                                          confirmLabel: 'Hapus Pengguna',
                                                      })">
                                                Hapus
                                            </x-button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-data-table>

            @if ($users->hasPages())
                {{ $users->links() }}
            @endif
        </x-card>
    </div>
@endsection
