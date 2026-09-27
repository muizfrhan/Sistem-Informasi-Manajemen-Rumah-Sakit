@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', now()->translatedFormat('l, d F Y'))

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Pendaftaran Hari Ini" :value="$stats['registrationsToday']"
                     :hint="collect([$stats['registrationsActive'], 'pasien aktif'])->join(' ')" tone="teal"
                     icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        <x-stat-card label="Pasien Baru" :value="$stats['patientsToday']"
                     :hint="$stats['patientsTotal'].' pasien aktif terdaftar'" tone="blue"
                     icon="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        <x-stat-card label="Poli Aktif" :value="$perPoly->count()"
                     :hint="$perPoly->sum('today_count').' kunjungan tersebar hari ini'" tone="purple"
                     icon="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        <x-stat-card label="Pengguna Aktif" :value="$stats['usersActive']"
                     hint="Termasuk seluruh peran" tone="green"
                     icon="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6-8a4 4 0 11-4-4m7 20h-3a6 6 0 00-4 5"/>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card title="Kunjungan 7 Hari Terakhir" subtitle="Jumlah pendaftaran per hari" class="lg:col-span-2">
            <div class="flex h-48 items-end gap-2">
                @foreach ($chart['data'] as $day)
                    @php $height = round(($day['count'] / $chart['max']) * 100); @endphp
                    <div class="group flex flex-1 flex-col items-center gap-2">
                        <span class="text-xs font-semibold text-slate-600 opacity-0 transition group-hover:opacity-100">{{ $day['count'] }}</span>
                        <div class="flex w-full flex-1 items-end">
                            <div class="w-full rounded-t bg-teal-500/80 transition group-hover:bg-teal-600"
                                 style="height: {{ max($height, $day['count'] > 0 ? 4 : 1) }}%"
                                 title="{{ $day['count'] }} kunjungan"></div>
                        </div>
                        <span class="text-[10px] text-slate-500">{{ $day['date'] }}</span>
                    </div>
                @endforeach
            </div>
        </x-card>

        <x-card title="Status Antrean Hari Ini">
            @php $total = max(1, $stats['registrationsToday']); @endphp
            <ul class="space-y-3">
                @foreach ($statuses as $status)
                    @php $count = $statusCounts[$status->value] ?? 0; @endphp
                    <li>
                        <div class="mb-1 flex items-center justify-between text-xs">
                            <span class="font-medium text-slate-600">{{ $status->label() }}</span>
                            <span class="font-semibold text-slate-800">{{ $count }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-teal-500" style="width: {{ round($count / $total * 100) }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-card>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card title="Pendaftaran Terbaru" subtitle="8 antrean terakhir" class="lg:col-span-2" padding="p-0">
            @if ($recent->isEmpty())
                <x-empty-state title="Belum ada pendaftaran hari ini"
                               message="Daftarkan pasien baru untuk memulai antrean.">
                    @can('registration.manage')
                        <x-button :href="route('registrations.create')">Buat Pendaftaran</x-button>
                    @endcan
                </x-empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-2.5 font-medium">Antrean</th>
                                <th class="px-5 py-2.5 font-medium">Pasien</th>
                                <th class="px-5 py-2.5 font-medium">Poli</th>
                                <th class="px-5 py-2.5 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($recent as $registration)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3">
                                        <a href="{{ route('registrations.show', $registration) }}"
                                           class="font-mono text-sm font-semibold text-teal-700 hover:underline">{{ $registration->queue_code }}</a>
                                    </td>
                                    <td class="px-5 py-3">
                                        <a href="{{ route('patients.show', $registration->patient) }}" class="font-medium text-slate-800 hover:underline">{{ $registration->patient->name }}</a>
                                        <p class="text-xs text-slate-500">{{ $registration->patient->mr_number }}</p>
                                    </td>
                                    <td class="px-5 py-3 text-slate-600">{{ $registration->poly->name }}</td>
                                    <td class="px-5 py-3">
                                        <x-badge :class="$registration->status->badgeClass()">{{ $registration->status->label() }}</x-badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>

        <div class="space-y-6">
            <x-card title="Kunjungan per Poli" subtitle="Hari ini">
                <ul class="space-y-2.5">
                    @forelse ($perPoly as $poly)
                        <li class="flex items-center justify-between gap-3 text-sm">
                            <a href="{{ route('queue.index', ['poly_id' => $poly->id]) }}" class="truncate text-slate-700 hover:underline">{{ $poly->name }}</a>
                            <x-badge :variant="$poly->today_count > 0 ? 'teal' : 'slate'">{{ $poly->today_count }}</x-badge>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">Belum ada poli terdaftar.</li>
                    @endforelse
                </ul>
            </x-card>

            @if (auth()->user()->hasPermission(\App\Enums\Permission::VIEW_MASTER))
                <x-card title="Stok Obat Menipis" subtitle="Di bawah stok minimum">
                    @if ($lowStockDrugs->isEmpty())
                        <p class="text-sm text-slate-500">Semua stok obat aman.</p>
                    @else
                        <ul class="space-y-2.5">
                            @foreach ($lowStockDrugs as $drug)
                                <li class="flex items-center justify-between gap-3 text-sm">
                                    <span class="truncate text-slate-700">{{ $drug->display_name }}</span>
                                    <x-badge variant="amber">{{ $drug->stock }} {{ $drug->unit }}</x-badge>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-card>
            @endif
        </div>
    </div>
@endsection
