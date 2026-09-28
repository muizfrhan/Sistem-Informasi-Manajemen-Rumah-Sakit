@extends('layouts.app')

@section('title', 'Pendaftaran')
@section('heading', 'Data Pendaftaran')
@section('subheading', $registrations->total() . ' kunjungan tercatat')

@section('content')
    <div class="space-y-5">
        <x-page-header title="Data Pendaftaran"
                       :description="'Total ' . number_format($registrations->total(), 0, ',', '.') . ' kunjungan'">
            <x-slot:actions>
                <x-button :href="route('registrations.create')" icon="plus">Pendaftaran Baru</x-button>
            </x-slot:actions>
        </x-page-header>

        <x-card padding="p-0">
            <x-filter-bar :action="route('registrations.index')" :reset="route('registrations.index')">
                <x-slot:fields>
                    <div>
                        <label for="poly_id" class="field-label">Poli</label>
                        <select id="poly_id" name="poly_id" class="field-control">
                            <option value="">Semua poli</option>
                            @foreach ($polies as $poly)
                                <option value="{{ $poly->id }}" @selected(request('poly_id') == $poly->id)>
                                    {{ $poly->name }}
                                </option>
                            @endforeach
                        </select>
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
                        <label for="date" class="field-label">Tanggal Kunjungan</label>
                        <input id="date" name="date" type="date" value="{{ request('date') }}" class="field-control">
                    </div>
                </x-slot:fields>
            </x-filter-bar>

            <x-data-table :cols="$registrations->count()" min-width="56rem"
                          empty="Belum ada pendaftaran"
                          empty-message="Belum ada kunjungan yang cocok dengan filter ini."
                          empty-icon="clipboard">
                <x-slot:emptyAction>
                    <x-button :href="route('registrations.index')" size="sm" variant="secondary" icon="refresh">Reset Filter</x-button>
                </x-slot:emptyAction>

                <x-slot:head>
                    <tr>
                        <th scope="col">Antrean</th>
                        <th scope="col">Pasien</th>
                        <th scope="col" class="hidden sm:table-cell">Poli</th>
                        <th scope="col" class="hidden lg:table-cell">Dokter</th>
                        <th scope="col" class="hidden xl:table-cell">Keluhan</th>
                        <th scope="col" class="hidden md:table-cell">Tanggal</th>
                        <th scope="col">Status</th>
                    </tr>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($registrations as $registration)
                        <tr>
                            <td data-label="Antrean">
                                <a href="{{ route('registrations.show', $registration) }}"
                                   class="font-mono text-sm font-bold text-brand hover:underline">
                                    {{ $registration->queue_code }}
                                </a>
                                @if ($registration->is_emergency)
                                    <x-badge variant="danger" class="ml-1" title="Gawat darurat">UGD</x-badge>
                                @endif
                            </td>

                            <td data-label="Pasien">
                                <a href="{{ route('patients.show', $registration->patient) }}"
                                   class="font-semibold text-ink hover:underline">
                                    {{ $registration->patient->name }}
                                </a>
                                <span class="block font-mono text-xs text-ink-3">{{ $registration->patient->mr_number }}</span>
                            </td>

                            <td data-label="Poli" class="hidden sm:table-cell">{{ $registration->poly->name }}</td>

                            <td data-label="Dokter" class="hidden lg:table-cell text-xs">
                                {{ $registration->doctor?->name ?? '-' }}
                            </td>

                            <td data-label="Keluhan" class="hidden xl:table-cell">
                                <span class="line-clamp-1 text-xs text-ink-2">{{ $registration->complaint ?: '-' }}</span>
                            </td>

                            <td data-label="Tanggal" class="hidden md:table-cell text-xs text-ink-2">
                                {{ $registration->visit_date->translatedFormat('d M Y') }}
                            </td>

                            <td data-label="Status">
                                <x-badge :class="$registration->status->badgeClass()">
                                    {{ $registration->status->label() }}
                                </x-badge>
                            </td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-data-table>

            @if ($registrations->hasPages())
                {{ $registrations->links() }}
            @endif
        </x-card>
    </div>
@endsection
