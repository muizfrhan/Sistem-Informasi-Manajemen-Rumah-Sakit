@extends('layouts.app')

@section('title', 'Audit Trail')
@section('heading', 'Audit Trail')
@section('subheading', $logs->total().' catatan aktivitas sistem')

@section('content')
    <x-card padding="p-0">
        <x-slot:title>Jejak Audit Sistem</x-slot:title>
        <x-slot:subtitle>Setiap perubahan data dan aktivitas pengguna tercatat otomatis untuk kepatuhan hukum</x-slot:subtitle>

        <form method="GET" class="grid grid-cols-1 gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4 sm:grid-cols-2 lg:grid-cols-5">
            <div><x-input name="q" :value="request('q')" placeholder="Nama pengguna / tabel..." /></div>
            <div><x-select name="user_id" :value="request('user_id')" :options="$users->pluck('name', 'id')->all()" placeholder="Semua pengguna" /></div>
            <div><x-select name="event" :value="request('event')" :options="collect($events)->mapWithKeys(fn ($e) => [$e => ucfirst($e)])->all()" placeholder="Semua aktivitas" /></div>
            <div><x-input name="from" type="date" :value="request('from')" /></div>
            <div><x-input name="to" type="date" :value="request('to')" /></div>
            <div class="flex items-center gap-2 lg:col-span-5">
                <x-button type="submit" variant="secondary">Terapkan Filter</x-button>
                @if (request()->hasAny(['q', 'user_id', 'event', 'from', 'to']))
                    <a href="{{ route('admin.audit-logs.index') }}" class="text-xs text-slate-500 hover:text-slate-700">Reset</a>
                @endif
            </div>
        </form>

        @if ($logs->isEmpty())
            <x-empty-state title="Belum ada catatan audit"
                           message="Catatan akan muncul otomatis saat ada aktivitas pengguna pada data pasien, pendaftaran, dan master data."
                           icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">Waktu</th>
                            <th class="px-5 py-3 font-medium">Pengguna</th>
                            <th class="px-5 py-3 font-medium">Aktivitas</th>
                            <th class="px-5 py-3 font-medium">Objek</th>
                            <th class="px-5 py-3 font-medium">IP</th>
                            <th class="px-5 py-3 text-right font-medium">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($logs as $log)
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-3 text-xs text-slate-500">{{ $log->created_at?->translatedFormat('d M Y H:i:s') }}</td>
                                <td class="px-5 py-3">
                                    <p class="text-slate-800">{{ $log->user_name ?? 'Sistem' }}</p>
                                </td>
                                <td class="px-5 py-3">
                                    <x-badge :variant="match ($log->event) { 'created' => 'green', 'updated' => 'blue', 'deleted' => 'red', 'viewed' => 'slate', 'login' => 'teal', 'login_failed' => 'red', 'logout' => 'slate', default => 'slate' }">
                                        {{ ucfirst(str_replace('_', ' ', $log->event)) }}
                                    </x-badge>
                                </td>
                                <td class="px-5 py-3 text-xs text-slate-600">
                                    @if ($log->auditable_type)
                                        {{ class_basename($log->auditable_type) }} <span class="text-slate-400">#{{ $log->auditable_id }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-slate-500">{{ $log->ip_address ?? '-' }}</td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('admin.audit-logs.show', $log) }}" class="text-xs font-medium text-teal-700 hover:underline">Lihat</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())<div class="border-t border-slate-200 px-5 py-3">{{ $logs->links() }}</div>@endif
        @endif
    </x-card>
@endsection
