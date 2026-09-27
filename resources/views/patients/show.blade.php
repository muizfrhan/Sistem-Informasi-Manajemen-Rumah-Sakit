@extends('layouts.app')

@section('title', 'Detail Pasien')
@section('heading', $patient->name)
@section('subheading', 'No. Rekam Medis '.$patient->mr_number)

@section('content')
    @php $canManage = auth()->user()->hasPermission(\App\Enums\Permission::MANAGE_PATIENT); @endphp

    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('patients.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-800">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke daftar
        </a>
    </div>

    <div class="mt-4 flex flex-wrap gap-2">
        @if (auth()->user()->hasPermission(\App\Enums\Permission::MANAGE_REGISTRATION))
            <x-button :href="route('registrations.create', ['patient_id' => $patient->id])">Daftarkan ke Poli</x-button>
        @endif
        @if ($canManage)
            <x-button :href="route('patients.edit', $patient)" variant="secondary">Ubah Data</x-button>
            <form method="POST" action="{{ route('patients.toggle', $patient) }}">
                @csrf @method('PATCH')
                <x-button type="submit" variant="secondary">{{ $patient->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</x-button>
            </form>
        @endif
        <x-badge :variant="$patient->is_active ? 'green' : 'red'" class="self-center">{{ $patient->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card title="Identitas Diri">
                <dl class="grid grid-cols-1 gap-x-6 gap-y-3.5 text-sm sm:grid-cols-2">
                    <div><dt class="text-xs text-slate-500">Nama Lengkap</dt><dd class="mt-0.5 font-medium text-slate-800">{{ $patient->name }}</dd></div>
                    <div><dt class="text-xs text-slate-500">NIK</dt><dd class="mt-0.5 font-mono text-slate-800">{{ $patient->masked_nik }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Jenis Kelamin</dt><dd class="mt-0.5 text-slate-800">{{ $patient->gender->label() }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Tanggal Lahir</dt><dd class="mt-0.5 text-slate-800">{{ $patient->birth_date->translatedFormat('d F Y') }} <span class="text-slate-500">({{ $patient->age_years }})</span></dd></div>
                    <div><dt class="text-xs text-slate-500">Tempat Lahir</dt><dd class="mt-0.5 text-slate-800">{{ $patient->birth_place ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Golongan Darah</dt><dd class="mt-0.5 text-slate-800">{{ $patient->blood_type ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Status Perkawinan</dt><dd class="mt-0.5 text-slate-800">{{ $patient->marital_status ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Telepon</dt><dd class="mt-0.5 text-slate-800">{{ $patient->phone ?? '-' }}</dd></div>
                </dl>
            </x-card>

            <x-card title="Alamat & Jaminan">
                <dl class="grid grid-cols-1 gap-x-6 gap-y-3.5 text-sm sm:grid-cols-2">
                    <div class="sm:col-span-2"><dt class="text-xs text-slate-500">Alamat</dt><dd class="mt-0.5 text-slate-800">{{ $patient->address ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Kelurahan / Desa</dt><dd class="mt-0.5 text-slate-800">{{ $patient->village ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Kecamatan</dt><dd class="mt-0.5 text-slate-800">{{ $patient->district ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Kota / Kabupaten</dt><dd class="mt-0.5 text-slate-800">{{ $patient->city ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Provinsi</dt><dd class="mt-0.5 text-slate-800">{{ $patient->province ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Jenis Jaminan</dt><dd class="mt-0.5"><x-badge :variant="$patient->insurance_type === \App\Enums\InsuranceType::BPJS ? 'teal' : 'slate'">{{ $patient->insurance_type->label() }}</x-badge></dd></div>
                    <div><dt class="text-xs text-slate-500">Nomor Peserta</dt><dd class="mt-0.5 font-mono text-slate-800">{{ $patient->insurance_number ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Penetap Asuransi</dt><dd class="mt-0.5 text-slate-800">{{ $patient->insurance_name ?? '-' }}</dd></div>
                </dl>
            </x-card>

            <x-card title="Riwayat Kunjungan" :subtitle="$patient->registrations_count.' kunjungan tercatat'" padding="p-0">
                @if ($patient->registrations->isEmpty())
                    <x-empty-state title="Belum ada riwayat kunjungan"
                                   message="Pasien ini belum pernah mendaftar di poli mana pun." />
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-2.5 font-medium">No. Registrasi</th>
                                    <th class="px-5 py-2.5 font-medium">Tanggal</th>
                                    <th class="px-5 py-2.5 font-medium">Antrean</th>
                                    <th class="px-5 py-2.5 font-medium">Poli / Dokter</th>
                                    <th class="px-5 py-2.5 font-medium">Keluhan</th>
                                    <th class="px-5 py-2.5 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($patient->registrations as $registration)
                                    <tr class="hover:bg-slate-50">
                                        <td class="whitespace-nowrap px-5 py-3">
                                            <a href="{{ route('registrations.show', $registration) }}" class="font-mono text-xs font-semibold text-teal-700 hover:underline">{{ $registration->registration_number }}</a>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3 text-slate-600">{{ $registration->visit_date->translatedFormat('d M Y') }}</td>
                                        <td class="whitespace-nowrap px-5 py-3 font-mono text-slate-700">{{ $registration->queue_code }}</td>
                                        <td class="px-5 py-3">
                                            <p class="text-slate-800">{{ $registration->poly->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $registration->doctor?->name ?? 'Dokter belum ditentukan' }}</p>
                                        </td>
                                        <td class="max-w-xs px-5 py-3 text-slate-600">{{ \Illuminate\Support\Str::limit($registration->complaint, 40) }}</td>
                                        <td class="whitespace-nowrap px-5 py-3"><x-badge :class="$registration->status->badgeClass()">{{ $registration->status->label() }}</x-badge></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card title="Informasi RekMedis">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">No. Rekam Medis</dt><dd class="font-mono font-semibold text-slate-800">{{ $patient->mr_number }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Terdaftar</dt><dd class="text-slate-800">{{ $patient->created_at?->translatedFormat('d M Y H:i') }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Oleh</dt><dd class="text-right text-slate-800">{{ $patient->creator?->name ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Terakhir diubah</dt><dd class="text-slate-800">{{ $patient->updated_at?->diffForHumans() }}</dd></div>
                </dl>
            </x-card>

            <x-card title="Kontak Darurat">
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-xs text-slate-500">Nama</dt><dd class="mt-0.5 text-slate-800">{{ $patient->emergency_contact_name ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Telepon</dt><dd class="mt-0.5 text-slate-800">{{ $patient->emergency_contact_phone ?? '-' }}</dd></div>
                </dl>
            </x-card>

            <x-card title="Privasi Data">
                <p class="text-xs leading-relaxed text-slate-600">
                    NIK disimpan dalam bentuk terenkripsi (AES-256). Pencarian tetap dimungkinkan melalui
                    <span class="font-mono">blind index</span> tanpa membuka nilai asli. Setiap akses dan
                    perubahan data ini tercatat pada audit trail sistem.
                </p>
            </x-card>
        </div>
    </div>
@endsection
