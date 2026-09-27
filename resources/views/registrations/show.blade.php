@extends('layouts.app')

@section('title', 'Detail Pendaftaran')
@section('heading', 'Detail Pendaftaran')
@section('subheading', $registration->registration_number)

@section('content')
    <a href="{{ route('registrations.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-800">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke daftar pendaftaran
    </a>

    <div class="mt-4 flex flex-wrap items-center gap-3">
        <x-badge :class="$registration->status->badgeClass()" class="!px-3 !py-1 !text-sm">{{ $registration->status->label() }}</x-badge>
        @if ($registration->is_emergency)
            <x-badge variant="red" class="!px-3 !py-1 !text-sm">Gawat Darurat</x-badge>
        @endif
        <x-badge variant="slate" class="!px-3 !py-1 !text-sm">{{ $registration->visit_type->label() }}</x-badge>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card title="Data Pasien">
                <dl class="grid grid-cols-1 gap-x-6 gap-y-3.5 text-sm sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-slate-500">Nama</dt>
                        <dd class="mt-0.5">
                            <a href="{{ route('patients.show', $registration->patient) }}" class="font-semibold text-teal-700 hover:underline">{{ $registration->patient->name }}</a>
                            <span class="ml-2 font-mono text-xs text-slate-500">{{ $registration->patient->mr_number }}</span>
                        </dd>
                    </div>
                    <div><dt class="text-xs text-slate-500">Jenis Kelamin / Umur</dt><dd class="mt-0.5 text-slate-800">{{ $registration->patient->gender->label() }} / {{ $registration->patient->age_years }}</dd></div>
                    <div><dt class="text-xs text-slate-500">NIK</dt><dd class="mt-0.5 font-mono text-slate-800">{{ $registration->patient->masked_nik }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Jaminan</dt><dd class="mt-0.5 text-slate-800">{{ $registration->patient->insurance_type->label() }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Telepon</dt><dd class="mt-0.5 text-slate-800">{{ $registration->patient->phone ?? '-' }}</dd></div>
                </dl>
            </x-card>

            <x-card title="Kunjungan">
                <dl class="grid grid-cols-1 gap-x-6 gap-y-3.5 text-sm sm:grid-cols-2">
                    <div><dt class="text-xs text-slate-500">Poli</dt><dd class="mt-0.5 text-slate-800">{{ $registration->poly->name }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Dokter</dt><dd class="mt-0.5 text-slate-800">{{ $registration->doctor?->name ?? 'Belum ditentukan' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Tanggal Kunjungan</dt><dd class="mt-0.5 text-slate-800">{{ $registration->visit_date->translatedFormat('l, d F Y') }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Nomor Antrean</dt><dd class="mt-0.5 font-mono font-bold text-slate-900">{{ $registration->queue_code }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs text-slate-500">Keluhan Utama</dt><dd class="mt-0.5 text-slate-800">{{ $registration->complaint ?? 'Tidak dicatat' }}</dd></div>
                </dl>
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card title="Riwayat Status">
                <ol class="relative space-y-4 border-l border-slate-200 pl-5">
                    @foreach ([
                        ['Terdaftar', $registration->registered_at],
                        ['Dipanggil', $registration->called_at],
                        ['Dalam Pemeriksaan', $registration->examined_at],
                        ['Selesai', $registration->finished_at],
                    ] as [$label, $time])
                        <li class="relative">
                            <span @class([
                                'absolute -left-[1.6rem] top-1 h-2.5 w-2.5 rounded-full ring-4 ring-white',
                                'bg-teal-500' => $time,
                                'bg-slate-200' => ! $time,
                            ])></span>
                            <p @class(['text-sm font-medium', 'text-slate-800' => $time, 'text-slate-400' => ! $time])>{{ $label }}</p>
                            <p class="text-xs text-slate-500">{{ $time?->translatedFormat('d M Y H:i') ?? '-' }}</p>
                        </li>
                    @endforeach
                </ol>
            </x-card>

            <x-card title="Informasi Pendaftaran">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Nomor Registrasi</dt><dd class="font-mono font-semibold text-slate-800">{{ $registration->registration_number }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Didaftarkan oleh</dt><dd class="text-right text-slate-800">{{ $registration->registrar?->name ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Waktu Daftar</dt><dd class="text-right text-slate-800">{{ $registration->registered_at?->translatedFormat('d M Y H:i') ?? '-' }}</dd></div>
                </dl>
            </x-card>

            @if (auth()->user()->hasPermission(\App\Enums\Permission::MANAGE_QUEUE) && $registration->status->allowedTransitions())
                <x-card title="Ubah Status">
                    <div class="flex flex-wrap gap-2">
                        @foreach ($registration->status->allowedTransitions() as $target)
                            <form method="POST" action="{{ route('queue.advance', ['registration' => $registration, 'status' => $target->value]) }}">
                                @csrf @method('PATCH')
                                <x-button type="submit" size="sm"
                                          variant="match ($target) { \App\Enums\RegistrationStatus::CALLED => 'primary', \App\Enums\RegistrationStatus::IN_EXAMINATION => 'primary', \App\Enums\RegistrationStatus::DONE => 'success', default => 'secondary' }">
                                    {{ $target->label() }}
                                </x-button>
                            </form>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>
    </div>
@endsection
