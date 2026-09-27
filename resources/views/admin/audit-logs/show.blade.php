@extends('layouts.app')

@section('title', 'Detail Audit')
@section('heading', 'Detail Aktivitas')
@section('subheading', $log->created_at?->translatedFormat('l, d F Y H:i:s'))

@section('content')
    <a href="{{ route('admin.audit-logs.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-800">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke audit trail
    </a>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card title="Perubahan Nilai">
                @php $changes = $log->changed_keys; @endphp

                @if ($log->event === 'viewed' || $changes === [])
                    <p class="text-sm text-slate-500">Tidak ada perubahan nilai pada aktivitas ini.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-4 py-2.5 font-medium">Field</th>
                                    <th class="px-4 py-2.5 font-medium">Sebelum</th>
                                    <th class="px-4 py-2.5 font-medium">Sesudah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($changes as $key)
                                    <tr>
                                        <td class="whitespace-nowrap px-4 py-2.5 font-mono text-xs text-slate-600">{{ $key }}</td>
                                        <td class="px-4 py-2.5 text-slate-500 line-through">
                                            {{ \Illuminate\Support\Str::limit($log->old_values[$key] ?? '-', 60) }}
                                        </td>
                                        <td class="px-4 py-2.5 font-medium text-slate-800">
                                            {{ \Illuminate\Support\Str::limit($log->new_values[$key] ?? '-', 60) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>

            <x-card title="Data Mentah" subtitle="JSON snapshot saat aktivitas terjadi">
                <pre class="max-h-96 overflow-auto rounded-lg bg-slate-900 p-4 text-xs leading-relaxed text-slate-300">{{ json_encode(['old_values' => $log->old_values, 'new_values' => $log->new_values], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card title="Konteks">
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-xs text-slate-500">Aktivitas</dt><dd class="mt-0.5 text-slate-800">{{ ucfirst(str_replace('_', ' ', $log->event)) }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Objek</dt><dd class="mt-0.5 text-slate-800">{{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">URL</dt><dd class="mt-0.5 break-all font-mono text-xs text-slate-600">{{ $log->url ?? '-' }}</dd></div>
                </dl>
            </x-card>

            <x-card title="Pelaku">
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-xs text-slate-500">Pengguna</dt><dd class="mt-0.5 text-slate-800">{{ $log->user_name ?? 'Sistem' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Peran</dt><dd class="mt-0.5 text-slate-800">{{ $log->user?->role?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">IP Address</dt><dd class="mt-0.5 font-mono text-xs text-slate-600">{{ $log->ip_address ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Waktu</dt><dd class="mt-0.5 text-slate-800">{{ $log->created_at?->translatedFormat('d F Y H:i:s') }}</dd></div>
                </dl>
            </x-card>

            <x-card title="User Agent">
                <p class="break-all font-mono text-[11px] leading-relaxed text-slate-500">{{ $log->user_agent ?? '-' }}</p>
            </x-card>
        </div>
    </div>
@endsection
