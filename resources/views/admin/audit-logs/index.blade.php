@extends('layouts.app')

@section('title', 'Audit Trail')
@section('heading', 'Audit Trail')
@section('subheading', $logs->total().' catatan aktivitas sistem')

@php
    $search = trim((string) request('q', ''));
    $userId = trim((string) request('user_id', ''));
    $event = trim((string) request('event', ''));
    $from = trim((string) request('from', ''));
    $to = trim((string) request('to', ''));
    $hasFilter = $search !== '' || $userId !== '' || $event !== '' || $from !== '' || $to !== '';

    // Nama event pada database disimpan dalam bahasa Inggris. Seluruh label
    // ditampilkan dalam bahasa Indonesia agar konsisten dengan antarmuka.
    $eventLabels = [
        'created' => 'Data Dibuat',
        'updated' => 'Data Diperbarui',
        'deleted' => 'Data Dihapus',
        'restored' => 'Data Dipulihkan',
        'viewed' => 'Data Dilihat',
        'login' => 'Berhasil Masuk',
        'login_failed' => 'Gagal Masuk',
        'logout' => 'Keluar Sistem',
    ];

    $eventVariants = [
        'created' => 'success',
        'updated' => 'info',
        'deleted' => 'danger',
        'restored' => 'warning',
        'viewed' => 'slate',
        'login' => 'brand',
        'login_failed' => 'danger',
        'logout' => 'slate',
    ];

    $eventOptions = collect($events)
        ->mapWithKeys(fn ($name) => [$name => $eventLabels[$name] ?? \Illuminate\Support\Str::headline($name)])
        ->all();

    $pageDescription = 'Total '.number_format($logs->total(), 0, ',', '.').' catatan aktivitas'
        .($search !== '' ? ' untuk pencarian '.$search : '');

    $resetUrl = route('admin.audit-logs.index');
    $emptyCta = $hasFilter
        ? ['label' => 'Reset Filter', 'href' => $resetUrl, 'variant' => 'secondary', 'icon' => 'refresh', 'click' => "location.href = '".$resetUrl."'"]
        : ['label' => 'Belum ada aktivitas', 'href' => null, 'variant' => 'secondary', 'icon' => 'inbox', 'click' => 'noop'];
@endphp

@section('content')
    <div class="space-y-5">
        <x-page-header title="Jejak Audit Sistem" :description="$pageDescription">
            <x-slot:actions>
                <x-button type="button" size="sm" variant="secondary" icon="printer"
                          x-on:click="window.print()">
                    Cetak
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <x-card padding="p-0">
            <x-slot:title>Daftar Aktivitas</x-slot:title>
            <x-slot:subtitle>Setiap perubahan data dan aktivitas pengguna tercatat otomatis untuk kepatuhan hukum</x-slot:subtitle>

            <x-filter-bar :action="route('admin.audit-logs.index')" :reset="route('admin.audit-logs.index')">
                <x-slot:fields>
                    <div>
                        <label for="audit_q" class="field-label">Pencarian</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-ink-3">
                                <x-icon name="search" class="h-4 w-4" />
                            </span>
                            <input id="audit_q" name="q" type="search" value="{{ $search }}"
                                   placeholder="Nama pengguna atau nama model" class="field-control pl-9">
                        </div>
                    </div>

                    <div>
                        <label for="audit_user_id" class="field-label">Pengguna</label>
                        <select id="audit_user_id" name="user_id" class="field-control">
                            <option value="">Semua pengguna</option>
                            @foreach ($users as $auditUser)
                                <option value="{{ $auditUser->id }}" @selected((string) $auditUser->id === $userId)>
                                    {{ $auditUser->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="audit_event" class="field-label">Jenis Aktivitas</label>
                        <select id="audit_event" name="event" class="field-control">
                            <option value="">Semua aktivitas</option>
                            @foreach ($eventOptions as $value => $label)
                                <option value="{{ $value }}" @selected($event === (string) $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="audit_from" class="field-label">Dari Tanggal</label>
                            <input id="audit_from" name="from" type="date" value="{{ $from }}" class="field-control">
                        </div>
                        <div>
                            <label for="audit_to" class="field-label">Sampai Tanggal</label>
                            <input id="audit_to" name="to" type="date" value="{{ $to }}" class="field-control"
                                   min="{{ $from !== '' ? $from : '' }}">
                        </div>
                    </div>
                </x-slot:fields>

                <x-slot:default>
                    <span class="text-xs text-ink-3">
                        Menampilkan {{ $logs->firstItem() ?? 0 }}&ndash;{{ $logs->lastItem() ?? 0 }}
                        dari {{ number_format($logs->total(), 0, ',', '.') }} catatan
                    </span>
                </x-slot:default>
            </x-filter-bar>

            @if ($hasFilter)
                <div class="flex items-start gap-2.5 border-b border-line bg-warning-soft/50 px-4 py-3">
                    <x-icon name="info" class="mt-0.5 h-4 w-4 shrink-0 text-warning-ink" />
                    <p class="text-xs text-ink-2">
                        Filter sedang aktif. Klik <strong>Reset</strong> pada bilah filter di atas untuk melihat seluruh catatan audit.
                    </p>
                </div>
            @endif

            <x-data-table :cols="$logs->count()" min-width="58rem"
                          empty="Belum ada catatan audit"
                          empty-message="Catatan akan muncul otomatis saat ada aktivitas pengguna pada data pasien, pendaftaran, dan master data."
                          empty-icon="document">
                <x-slot:emptyAction>
                    <x-button type="button" :href="$emptyCta['href']" :variant="$emptyCta['variant']" size="sm"
                              :icon="$emptyCta['icon']" x-on:click="{{ $emptyCta['click'] }}">
                        {{ $emptyCta['label'] }}
                    </x-button>
                </x-slot:emptyAction>

                <x-slot:head>
                    <tr>
                        <th scope="col">Waktu</th>
                        <th scope="col">Pengguna</th>
                        <th scope="col">Aktivitas</th>
                        <th scope="col" class="hidden sm:table-cell">Objek</th>
                        <th scope="col" class="hidden lg:table-cell">IP</th>
                        <th scope="col" class="text-right">Detail</th>
                    </tr>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($logs as $log)
                        <tr>
                            <td data-label="Waktu">
                                <span class="text-xs font-medium text-ink-2 tabular-nums">
                                    {{ $log->created_at?->translatedFormat('d M Y H:i') }}
                                </span>
                            </td>

                            <td data-label="Pengguna">
                                <span class="text-sm font-medium text-ink">{{ $log->user_name ?? 'Sistem' }}</span>
                            </td>

                            <td data-label="Aktivitas">
                                <x-badge :variant="$eventVariants[$log->event] ?? 'slate'" dot>
                                    {{ $eventLabels[$log->event] ?? \Illuminate\Support\Str::headline((string) $log->event) }}
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

                            <td data-label="IP" class="hidden lg:table-cell">
                                <span class="font-mono text-xs text-ink-2">{{ $log->ip_address ?? '-' }}</span>
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

            @if ($logs->hasPages())
                {{ $logs->links() }}
            @endif
        </x-card>
    </div>
@endsection
