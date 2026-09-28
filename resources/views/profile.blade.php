@extends('layouts.app')

@section('title', 'Profil Saya')
@section('heading', 'Profil Saya')
@section('subheading', $user->name)

@php
    $isSuperAdmin = $user->isSuperAdmin();

    // Izin dikelompokkan berdasarkan modul agar mudah dipindai. tryFrom()
    // dipakai karena kode izin pada database bisa saja tidak sinkron dengan
    // enum Permission, dan from() akan melempar exception.
    $groupedPermissions = $user->permissions
        ->map(function ($permission) {
            $enum = \App\Enums\Permission::tryFrom($permission->code);

            return [
                'label' => $permission->name,
                'group' => $enum?->group() ?? (string) $permission->group,
                'group_label' => $enum?->groupLabel() ?? \Illuminate\Support\Str::headline((string) $permission->group),
            ];
        })
        ->groupBy('group');

    $canViewAudit = auth()->user()?->can('audit.view') ?? false;
    $canEditSelf = auth()->user()?->can('user.manage') ?? false;

    $activityLabels = [
        'created' => 'Data Dibuat',
        'updated' => 'Data Diperbarui',
        'deleted' => 'Data Dihapus',
        'restored' => 'Data Dipulihkan',
        'viewed' => 'Data Dilihat',
        'login' => 'Berhasil Masuk',
        'login_failed' => 'Gagal Masuk',
        'logout' => 'Keluar Sistem',
    ];

    $activityVariants = [
        'created' => 'success',
        'updated' => 'info',
        'deleted' => 'danger',
        'restored' => 'warning',
        'viewed' => 'slate',
        'login' => 'brand',
        'login_failed' => 'danger',
        'logout' => 'slate',
    ];

    $sessionAge = $user->last_login_at?->diffForHumans();
@endphp

@section('content')
    <div class="space-y-5">
        <x-page-header title="Profil Saya" :description="'Ringkasan akun, peran, dan hak akses '.$user->name.'.'">
            <x-slot:actions>
                @if ($canEditSelf)
                    <x-button :href="route('admin.users.edit', $user)" variant="secondary" size="sm" icon="pencil">
                        Ubah Data Akun
                    </x-button>
                @endif
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <div class="space-y-5 lg:col-span-2">
                <x-card title="Informasi Akun" subtitle="Data akun yang tercatat pada sistem" padding="p-0">
                    <dl class="divide-y divide-line text-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
                            <dt class="text-ink-3">Nama Lengkap</dt>
                            <dd class="font-semibold text-ink">{{ $user->name }}</dd>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
                            <dt class="text-ink-3">Username</dt>
                            <dd class="font-mono text-ink-2">{{ $user->username }}</dd>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
                            <dt class="text-ink-3">Email</dt>
                            <dd class="text-ink-2">{{ $user->email ?? 'Belum diisi' }}</dd>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
                            <dt class="text-ink-3">Nomor Telepon</dt>
                            <dd class="text-ink-2">{{ $user->phone ?? 'Belum diisi' }}</dd>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
                            <dt class="text-ink-3">Peran</dt>
                            <dd>
                                <x-badge :variant="$isSuperAdmin ? 'brand' : 'slate'">
                                    {{ $user->role?->name ?? 'Belum ada peran' }}
                                </x-badge>
                            </dd>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
                            <dt class="text-ink-3">Status Akun</dt>
                            <dd><x-active-badge :active="$user->is_active" /></dd>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
                            <dt class="text-ink-3">Login Terakhir</dt>
                            <dd class="text-ink-2">
                                @if ($user->last_login_at)
                                    {{ $user->last_login_at->translatedFormat('d F Y H:i') }}
                                    <span class="text-ink-3">({{ $sessionAge }})</span>
                                @else
                                    <span class="text-ink-3">Belum pernah masuk</span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
                            <dt class="text-ink-3">IP Terakhir</dt>
                            <dd class="font-mono text-xs text-ink-2">{{ $user->last_login_ip ?? 'Tidak tercatat' }}</dd>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
                            <dt class="text-ink-3">Akun Dibuat</dt>
                            <dd class="text-ink-2">{{ $user->created_at?->translatedFormat('d F Y') ?? '-' }}</dd>
                        </div>
                    </dl>
                </x-card>

                <x-card title="Hak Akses"
                        :subtitle="$isSuperAdmin ? 'Super Admin memiliki akses penuh ke seluruh modul' : count($groupedPermissions).' modul dengan '. $user->permissions->count().' izin aktif'">
                    @if ($isSuperAdmin)
                        <div class="flex items-start gap-3 rounded-lg bg-brand-soft p-4">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand text-brand-fg">
                                <x-icon name="shield" class="h-[18px] w-[18px]" />
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-brand-ink">Akses penuh seluruh modul</p>
                                <p class="mt-1 text-xs leading-relaxed text-ink-2">
                                    Akun Super Admin dapat membuka, mengubah, dan menghapus data pada semua modul tanpa batasan peran.
                                </p>
                            </div>
                        </div>
                    @elseif ($groupedPermissions->isEmpty())
                        <x-empty-state compact icon="lock"
                                       title="Belum ada hak akses"
                                       message="Peran Anda belum memiliki izin. Hubungi administrator untuk membuka akses modul." />
                    @else
                        <div class="space-y-4">
                            @foreach ($groupedPermissions as $group => $items)
                                <div>
                                    <p class="text-[11px] font-semibold tracking-wide text-ink-3 uppercase">
                                        {{ $items->first()['group_label'] }}
                                    </p>
                                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                                        @foreach ($items as $item)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-brand-soft px-2.5 py-0.5 text-xs font-medium text-brand-ink ring-1 ring-brand-ink/20 ring-inset">
                                                <x-icon name="check" class="h-3 w-3" />
                                                {{ $item['label'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-card>

                @if ($canViewAudit)
                    <x-card title="Aktivitas Terakhir" subtitle="Catatan aktivitas milik akun ini pada sistem"
                            padding="p-0">
                        <x-data-table :cols="$recentActivity->count()" min-width="46rem"
                                      empty="Belum ada aktivitas"
                                      empty-message="Belum ada aktivitas tercatat untuk akun ini."
                                      empty-icon="clock">
                            <x-slot:head>
                                <tr>
                                    <th scope="col">Waktu</th>
                                    <th scope="col">Aktivitas</th>
                                    <th scope="col" class="hidden sm:table-cell">Objek</th>
                                    <th scope="col" class="text-right">Detail</th>
                                </tr>
                            </x-slot:head>

                            <x-slot:body>
                                @foreach ($recentActivity as $log)
                                    <tr>
                                        <td data-label="Waktu">
                                            <span class="text-xs text-ink-2 tabular-nums">
                                                {{ $log->created_at?->translatedFormat('d M Y H:i') }}
                                            </span>
                                        </td>
                                        <td data-label="Aktivitas">
                                            <x-badge :variant="$activityVariants[$log->event] ?? 'slate'" dot>
                                                {{ $activityLabels[$log->event] ?? \Illuminate\Support\Str::headline((string) $log->event) }}
                                            </x-badge>
                                        </td>
                                        <td data-label="Objek" class="hidden sm:table-cell">
                                            @if ($log->auditable_type)
                                                <span class="font-mono text-xs text-ink-2">
                                                    {{ class_basename($log->auditable_type) }}
                                                </span>
                                                <span class="text-xs text-ink-3">#{{ $log->auditable_id }}</span>
                                            @else
                                                <span class="text-xs text-ink-3">-</span>
                                            @endif
                                        </td>
                                        <td data-label="Detail">
                                            <a href="{{ route('admin.audit-logs.show', $log) }}"
                                               class="inline-flex items-center gap-1 text-xs font-semibold text-brand hover:underline">
                                                <x-icon name="eye" class="h-3.5 w-3.5" />
                                                Lihat
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </x-slot:body>
                        </x-data-table>
                    </x-card>
                @endif
            </div>

            <div class="space-y-4">
                <div class="rounded-card bg-ink p-5 text-canvas shadow-e2">
                    <div class="flex items-center gap-3.5">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand text-xl font-bold text-brand-fg">
                            {{ $user->initials }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold">{{ $user->name }}</p>
                            <p class="truncate text-xs opacity-70">{{ $user->role?->name ?? 'Tanpa peran' }}</p>
                            <div class="mt-1.5">
                                <x-active-badge :active="$user->is_active" />
                            </div>
                        </div>
                    </div>
                </div>

                <x-card title="Ganti Kata Sandi">
                    <p class="text-xs leading-relaxed text-ink-2">
                        Pengubahan kata sandi dilakukan oleh administrator melalui menu
                        <span class="font-semibold text-ink">Manajemen Pengguna</span>. Hubungi administrator
                        bila kata sandi Anda lupa.
                    </p>
                </x-card>

                <x-card title="Tentang Halaman Ini">
                    <ul class="space-y-2.5 text-xs leading-relaxed text-ink-2">
                        <li class="flex gap-2">
                            <x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand" />
                            <span>Halaman ini hanya menampilkan data, tanpa formulir perubahan.</span>
                        </li>
                        <li class="flex gap-2">
                            <x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand" />
                            <span>Hak akses mengikuti peran yang ditetapkan administrator.</span>
                        </li>
                        <li class="flex gap-2">
                            <x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand" />
                            <span>Bila peran berubah, masuk ulang agar hak akses ikut diperbarui.</span>
                        </li>
                    </ul>
                </x-card>
            </div>
        </div>
    </div>
@endsection
