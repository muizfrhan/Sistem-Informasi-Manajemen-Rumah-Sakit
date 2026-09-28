@extends('layouts.app')

@section('title', 'Detail Audit')
@section('heading', 'Detail Aktivitas')
@section('subheading', $log->created_at?->translatedFormat('l, d F Y H:i:s'))

@php
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

    // Kolom yang nilainya tidak boleh ditampilkan apa adanya. NIK pasien
    // disimpan terenkripsi, namun salinan nilainya tetap ikut masuk ke audit
    // log, sehingga harus disamarkan di layar.
    $maskedKeys = ['nik', 'nik_hash', 'password', 'remember_token', 'api_token', 'payload'];

    $eventLabel = $eventLabels[$log->event] ?? \Illuminate\Support\Str::headline((string) $log->event);
    $eventVariant = $eventVariants[$log->event] ?? 'slate';
    $changes = $log->changed_keys;
    $changeCount = count($changes);
    $actorName = $log->user_name ?: 'Sistem';
    $actorRole = $log->user?->role?->name ?? '-';

    /**
     * Format satu nilai audit menjadi teks aman untuk ditampilkan.
     */
    $formatValue = function ($value, string $key) use ($maskedKeys) {
        if (in_array($key, $maskedKeys, true)) {
            if ($key === 'password' || $key === 'remember_token' || $key === 'api_token' || $key === 'payload') {
                return '***';
            }

            return '***'.substr((string) $value, -4);
        }

        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }

        if ($value === null) {
            return '(kosong)';
        }

        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $value = (string) $value;

        return \Illuminate\Support\Str::limit($value, 80) ?: '(kosong)';
    };

    $rawSnapshot = json_encode(
        collect([
            'nilai_sebelum' => collect($log->old_values ?? [])->map(fn ($v, $k) => in_array($k, $maskedKeys, true) ? '***' : $v)->all(),
            'nilai_sesudah' => collect($log->new_values ?? [])->map(fn ($v, $k) => in_array($k, $maskedKeys, true) ? '***' : $v)->all(),
        ]),
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
    );
@endphp

@section('content')
    <div class="space-y-5">
        <x-button :href="route('admin.audit-logs.index')" variant="ghost" size="sm" icon="arrow-left">
            Kembali ke jejak audit
        </x-button>

        <x-page-header :title="$eventLabel"
                       :description="$actorName.' · '.($log->created_at?->translatedFormat('d F Y H:i:s') ?? '-')">
            <x-slot:actions>
                <x-badge :variant="$eventVariant" dot class="px-3 py-1 text-xs">{{ $eventLabel }}</x-badge>
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <div class="space-y-5 lg:col-span-2">
                <x-card title="Perubahan Nilai" subtitle="Perbandingan nilai kolom sebelum dan sesudah aktivitas" padding="p-0">
                    @if ($changes === [])
                        <x-empty-state compact icon="check-circle"
                                       title="Tidak ada perubahan nilai"
                                       message="Aktivitas ini tidak mengubah kolom apa pun pada objek yang tercatat." />
                    @else
                        <x-data-table :cols="$changeCount" min-width="38rem">
                            <x-slot:head>
                                <tr>
                                    <th scope="col">Kolom</th>
                                    <th scope="col">Sebelum</th>
                                    <th scope="col">Sesudah</th>
                                </tr>
                            </x-slot:head>

                            <x-slot:body>
                                @foreach ($changes as $key)
                                    @php
                                        $isSensitive = in_array($key, $maskedKeys, true);
                                        $before = $formatValue($log->old_values[$key] ?? null, $key);
                                        $after = $formatValue($log->new_values[$key] ?? null, $key);
                                    @endphp
                                    <tr>
                                        <td data-label="Kolom">
                                            <span class="font-mono text-xs font-semibold text-ink">{{ $key }}</span>
                                            @if ($isSensitive)
                                                <span class="mt-0.5 block">
                                                    <x-badge variant="warning">
                                                        <x-icon name="lock" class="h-3 w-3" />
                                                        Disamarkan
                                                    </x-badge>
                                                </span>
                                            @endif
                                        </td>
                                        <td data-label="Sebelum">
                                            <span class="text-xs text-ink-3 line-through decoration-ink-3/50">
                                                {{ $before }}
                                            </span>
                                        </td>
                                        <td data-label="Sesudah">
                                            <span class="text-xs font-semibold text-ink">{{ $after }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </x-slot:body>
                        </x-data-table>
                    @endif
                </x-card>

                @if ($log->old_values || $log->new_values)
                    <x-card title="Salinan Data Mentah" subtitle="Rekam nilai kolom pada saat aktivitas terjadi">
                        <pre class="scroll-slim max-h-96 overflow-auto rounded-lg bg-ink px-4 py-3 text-[11px] leading-relaxed text-canvas">{{ $rawSnapshot }}</pre>
                        <p class="mt-2.5 flex items-start gap-1.5 text-xs text-ink-3">
                            <x-icon name="lock" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                            <span>Nilai sensitif seperti NIK, kata sandi, dan token ditampilkan sebagai <span class="font-mono">***</span>.</span>
                        </p>
                    </x-card>
                @endif
            </div>

            <div class="space-y-4">
                <x-card title="Konteks" padding="p-0">
                    <dl class="divide-y divide-line text-sm">
                        <div class="px-5 py-3">
                            <dt class="text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Aktivitas</dt>
                            <dd class="mt-1">
                                <x-badge :variant="$eventVariant" dot>{{ $eventLabel }}</x-badge>
                            </dd>
                        </div>
                        <div class="px-5 py-3">
                            <dt class="text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Objek</dt>
                            <dd class="mt-1 font-medium text-ink">
                                @if ($log->auditable_type)
                                    <span class="font-mono text-xs">{{ class_basename($log->auditable_type) }}</span>
                                    <span class="text-ink-3">#{{ $log->auditable_id }}</span>
                                @else
                                    <span class="text-ink-3">-</span>
                                @endif
                            </dd>
                        </div>
                        <div class="px-5 py-3">
                            <dt class="text-[11px] font-semibold tracking-wide text-ink-3 uppercase">URL</dt>
                            <dd class="mt-1 font-mono text-xs break-all text-ink-2">{{ $log->url ?? '-' }}</dd>
                        </div>
                        <div class="px-5 py-3">
                            <dt class="text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Waktu</dt>
                            <dd class="mt-1 font-medium text-ink">
                                {{ $log->created_at?->translatedFormat('d F Y H:i:s') ?? '-' }}
                            </dd>
                        </div>
                    </dl>
                </x-card>

                <x-card title="Pelaku" padding="p-0">
                    <dl class="divide-y divide-line text-sm">
                        <div class="px-5 py-3">
                            <dt class="text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Pengguna</dt>
                            <dd class="mt-1 flex items-center gap-2">
                                @if ($log->user)
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-soft text-[10px] font-bold text-brand-ink">
                                        {{ $log->user->initials }}
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate font-medium text-ink">{{ $actorName }}</span>
                                        <span class="block truncate font-mono text-[11px] text-ink-3">{{ $log->user->username }}</span>
                                    </span>
                                @else
                                    <span class="font-medium text-ink">{{ $actorName }}</span>
                                @endif
                            </dd>
                        </div>
                        <div class="px-5 py-3">
                            <dt class="text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Peran Saat Aktif</dt>
                            <dd class="mt-1 text-ink-2">{{ $actorRole }}</dd>
                        </div>
                        <div class="px-5 py-3">
                            <dt class="text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Hak Akses</dt>
                            <dd class="mt-1 flex flex-wrap gap-1">
                                @if ($log->user?->isSuperAdmin())
                                    <x-badge variant="brand">Akses penuh</x-badge>
                                @else
                                    @forelse ($log->user?->permissionCodes() ?? [] as $code)
                                        @php $enum = \App\Enums\Permission::tryFrom($code); @endphp
                                        <x-badge variant="slate">{{ $enum?->label() ?? $code }}</x-badge>
                                    @empty
                                        <span class="text-xs text-ink-3">Tidak ada hak akses tercatat</span>
                                    @endforelse
                                @endif
                            </dd>
                        </div>
                        <div class="px-5 py-3">
                            <dt class="text-[11px] font-semibold tracking-wide text-ink-3 uppercase">IP Address</dt>
                            <dd class="mt-1 font-mono text-xs text-ink-2">{{ $log->ip_address ?? '-' }}</dd>
                        </div>
                    </dl>
                </x-card>

                <x-card title="Perangkat Pengguna">
                    <p class="font-mono text-[11px] leading-relaxed break-all text-ink-2">
                        {{ $log->user_agent ?? 'Perangkat tidak tercatat' }}
                    </p>
                </x-card>
            </div>
        </div>
    </div>
@endsection
