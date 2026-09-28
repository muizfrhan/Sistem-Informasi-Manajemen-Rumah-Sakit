@extends('layouts.app')

@section('title', 'Rekam Medis')
@section('heading', 'Rekam Medis')
@section('subheading', $records->total() . ' rekam medis tercatat')

@section('content')
    <div class="space-y-5">
        <x-page-header title="Rekam Medis"
                       :description="'Total ' . number_format($records->total(), 0, ',', '.') . ' rekam medis'">
            <x-slot:actions>
                <x-button permission="emr.manage" :href="route('medical-records.create')" icon="plus">
                    Rekam Medis Baru
                </x-button>
            </x-slot:actions>
        </x-page-header>

        @if ($patient)
            <div class="card flex flex-wrap items-center justify-between gap-3 p-4">
                <div class="flex items-center gap-3">
                    <x-icon name="users" class="h-5 w-5 shrink-0 text-brand" />
                    <div>
                        <p class="text-sm font-semibold text-ink">Riwayat {{ $patient->name }}</p>
                        <p class="font-mono text-xs text-ink-3">{{ $patient->mr_number }}</p>
                    </div>
                </div>
                <x-button :href="route('patients.show', $patient)" size="sm" variant="secondary">Lihat Profil</x-button>
            </div>
        @endif

        <x-card padding="p-0">
            <x-filter-bar :action="route('medical-records.index')" :reset="route('medical-records.index')">
                <x-slot:fields>
                    <div>
                        <label for="q" class="field-label">Pencarian Diagnosis</label>
                        <input id="q" name="q" type="search" value="{{ request('q') }}"
                               placeholder="Kata kunci pada ringkasan diagnosis" class="field-control">
                    </div>

                    <div>
                        <label for="status" class="field-label">Status</label>
                        <select id="status" name="status" class="field-control">
                            <option value="">Semua status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="doctor_id" class="field-label">Dokter</label>
                        <select id="doctor_id" name="doctor_id" class="field-control">
                            <option value="">Semua dokter</option>
                            @foreach ($doctors as $doctor)
                                <option value="{{ $doctor->id }}" @selected(request('doctor_id') == $doctor->id)>
                                    {{ $doctor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label for="from" class="field-label">Dari</label>
                            <input id="from" name="from" type="date" value="{{ request('from') }}" class="field-control">
                        </div>
                        <div>
                            <label for="to" class="field-label">Sampai</label>
                            <input id="to" name="to" type="date" value="{{ request('to') }}" class="field-control">
                        </div>
                    </div>
                </x-slot:fields>
            </x-filter-bar>

            <x-data-table :cols="$records->count()" min-width="60rem"
                          empty="Belum ada rekam medis"
                          empty-message="Belum ada rekam medis yang cocok dengan filter ini. Rekam medis dibuat dokter saat pemeriksaan pasien."
                          empty-icon="stethoscope">
                <x-slot:emptyAction>
                    <x-button :href="route('medical-records.index')" size="sm" variant="secondary" icon="refresh">Reset Filter</x-button>
                </x-slot:emptyAction>

                <x-slot:head>
                    <tr>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Pasien</th>
                        <th scope="col" class="hidden sm:table-cell">Jenis</th>
                        <th scope="col" class="hidden lg:table-cell">Dokter</th>
                        <th scope="col">Diagnosis</th>
                        <th scope="col">Status</th>
                    </tr>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($records as $record)
                        <tr>
                            <td data-label="Tanggal">
                                <a href="{{ route('medical-records.show', $record) }}"
                                   class="text-sm font-semibold text-brand hover:underline">
                                    {{ $record->created_at->translatedFormat('d M Y') }}
                                </a>
                                <span class="block text-xs text-ink-3">{{ $record->created_at->format('H:i') }}</span>
                            </td>

                            <td data-label="Pasien">
                                <a href="{{ route('patients.show', $record->patient) }}"
                                   class="font-medium text-ink hover:underline">
                                    {{ $record->patient->name }}
                                </a>
                                <span class="block font-mono text-xs text-ink-3">{{ $record->patient->mr_number }}</span>
                            </td>

                            <td data-label="Jenis" class="hidden sm:table-cell">
                                <x-badge :class="$record->record_type->badgeClass()">
                                    {{ $record->record_type->label() }}
                                </x-badge>
                            </td>

                            <td data-label="Dokter" class="hidden lg:table-cell text-xs">
                                {{ $record->doctor?->name ?? '-' }}
                            </td>

                            <td data-label="Diagnosis">
                                <span class="line-clamp-2 text-xs text-ink-2">
                                    {{ $record->diagnosis ?: '-' }}
                                </span>
                            </td>

                            <td data-label="Status">
                                <x-badge :class="$record->status->badgeClass()">{{ $record->status->label() }}</x-badge>
                            </td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-data-table>

            @if ($records->hasPages())
                {{ $records->links() }}
            @endif
        </x-card>
    </div>
@endsection
