@extends('layouts.app')

@section('title', 'Pilih Kunjungan')
@section('heading', 'Buat Rekam Medis')
@section('subheading', 'Pilih kunjungan yang belum memiliki rekam medis')

@section('content')
    <div class="space-y-5">
        <x-page-header title="Buat Rekam Medis"
                       description="Pilih kunjungan pasien yang akan diperiksa. Hanya kunjungan tanpa rekam medis yang ditampilkan.">
            <x-slot:actions>
                <x-button :href="route('medical-records.index')" variant="ghost" icon="arrow-left">Kembali</x-button>
            </x-slot:actions>
        </x-page-header>

        @if ($selectedPatient)
            <div class="card flex flex-wrap items-center gap-3 p-4">
                <x-icon name="users" class="h-5 w-5 shrink-0 text-brand" />
                <p class="text-sm text-ink-2">
                    Menampilkan kunjungan milik
                    <span class="font-semibold text-ink">{{ $selectedPatient->name }}</span>
                    <span class="font-mono text-xs text-ink-3">({{ $selectedPatient->mr_number }})</span>
                </p>
            </div>
        @endif

        <x-card padding="p-0" title="Kunjungan Tersedia" :subtitle="$candidates->count() . ' kunjungan belum memiliki rekam medis'">
            <x-data-table :cols="$candidates->count()" min-width="52rem"
                          empty="Tidak ada kunjungan tersedia"
                          empty-message="Semua kunjungan sudah memiliki rekam medis. Daftarkan pasien baru terlebih dahulu."
                          empty-icon="clipboard">
                <x-slot:head>
                    <tr>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Pasien</th>
                        <th scope="col" class="hidden sm:table-cell">Poli</th>
                        <th scope="col" class="hidden lg:table-cell">Keluhan</th>
                        <th scope="col" class="text-right">Aksi</th>
                    </tr>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($candidates as $registration)
                        <tr>
                            <td data-label="Tanggal">
                                <span class="font-medium text-ink">
                                    {{ $registration->visit_date->translatedFormat('d M Y') }}
                                </span>
                            </td>

                            <td data-label="Pasien">
                                <span class="font-semibold text-ink">{{ $registration->patient->name }}</span>
                                <span class="block font-mono text-xs text-ink-3">
                                    {{ $registration->patient->mr_number }} &middot; {{ $registration->patient->age }} th
                                </span>
                            </td>

                            <td data-label="Poli" class="hidden sm:table-cell">{{ $registration->poly->name }}</td>

                            <td data-label="Keluhan" class="hidden lg:table-cell">
                                <span class="line-clamp-1 text-xs text-ink-2">
                                    {{ $registration->complaint ?: '-' }}
                                </span>
                            </td>

                            <td data-label="Aksi">
                                <div class="flex justify-end">
                                    <x-button :href="route('medical-records.create', ['registration_id' => $registration->id])"
                                              size="sm" icon="plus">
                                        Buat Rekam Medis
                                    </x-button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-data-table>
        </x-card>
    </div>
@endsection
