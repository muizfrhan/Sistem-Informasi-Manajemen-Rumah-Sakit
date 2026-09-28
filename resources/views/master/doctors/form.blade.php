@extends('layouts.app')

@section('title', $doctor->exists ? 'Ubah Dokter' : 'Tambah Dokter')
@section('heading', $doctor->exists ? 'Ubah Data Dokter' : 'Tambah Dokter Baru')
@section('subheading', $doctor->exists ? $doctor->name : 'Lengkapi informasi dokter')

@php
    $editing = $doctor->exists;
    $action = $editing ? route('master.doctors.update', $doctor) : route('master.doctors.store');

    $pageTitle = $editing ? 'Ubah Data Dokter' : 'Tambah Dokter Baru';
    $pageDescription = $editing
        ? 'Perbarui identitas, penempatan poli, dan keaktifan dokter '.$doctor->name.'.'
        : 'Lengkapi identitas dokter agar dapat dipilih pada jam praktik dan penugasan poli.';

    $polyOptions = $polies->pluck('name', 'id')->all();
    $userOptions = $users->pluck('name', 'id')->all();
@endphp

@section('content')
    <div class="space-y-5">
        <x-page-header :title="$pageTitle" :description="$pageDescription">
            <x-slot:actions>
                <x-button :href="route('master.doctors.index')" variant="secondary" icon="arrow-left">Kembali</x-button>
            </x-slot:actions>
        </x-page-header>

        <form method="POST" action="{{ $action }}" class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="space-y-5 lg:col-span-2">
                <x-card title="Identitas Dokter" subtitle="Nama, gelar, dan dokumen resmi praktik">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-input name="name" label="Nama dan Gelar" required :maxlength="150" class="sm:col-span-2"
                                 :value="old('name', $doctor->name)"
                                 placeholder="dr. Nama Dokter, Sp.B"
                                 hint="Sertakan gelar agar konsisten pada daftar dokter." />

                        <x-input name="code" label="Kode Dokter" :maxlength="20"
                                 :value="old('code', $doctor->code)"
                                 placeholder="DR001"
                                 hint="Kosongkan untuk dibuat otomatis dari inisial nama."
                                 x-on:input="$el.value = $el.value.toUpperCase().replace(/[^A-Z0-9_-]/g, '')" />

                        <x-input name="nip" label="NIP" :maxlength="30"
                                 :value="old('nip', $doctor->nip)"
                                 placeholder="Nomor induk pegawai"
                                 inputmode="numeric" />

                        <x-input name="specialty" label="Spesialisasi" :maxlength="100"
                                 :value="old('specialty', $doctor->specialty)"
                                 placeholder="Bedah Umum"
                                 hint="Kosongkan bila dokter tidak memiliki spesialisasi khusus." />

                        <x-input name="sip_number" label="Nomor SIP" :maxlength="50"
                                 :value="old('sip_number', $doctor->sip_number)"
                                 placeholder="Nomor Surat Izin Praktik"
                                 hint="Sesuai Surat Izin Praktik yang masih berlaku." />

                        <x-input name="phone" label="Nomor Telepon" :maxlength="25" inputmode="tel"
                                 :value="old('phone', $doctor->phone)"
                                 placeholder="08xxxxxxxxxx" />
                    </div>
                </x-card>

                <x-card title="Penugasan" subtitle="Tentukan poli penempatan dan akses login sistem">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-select name="poly_id" label="Poli Penempatan"
                                  :value="old('poly_id', $doctor->poly_id)"
                                  :options="$polyOptions" placeholder="Belum ditempatkan" />

                        <x-select name="user_id" label="Akun Login Sistem"
                                  :value="old('user_id', $doctor->user_id)"
                                  :options="$userOptions" placeholder="Tidak dikaitkan"
                                  hint="Dipakai untuk mencatat rekam medis atas nama dokter." />

                        <div class="sm:col-span-2">
                            <x-checkbox name="is_active" label="Dokter Aktif"
                                        hint="Dokter nonaktif tidak dapat dipilih pada pendaftaran dan penjadwalan."
                                        :value="old('is_active', $editing ? $doctor->is_active : true)" />
                        </div>
                    </div>
                </x-card>

                <div class="flex flex-wrap items-center justify-end gap-2">
                    <x-button :href="route('master.doctors.index')" variant="ghost">Batal</x-button>
                    <x-button type="submit" icon="check">
                        {{ $editing ? 'Simpan Perubahan' : 'Tambah Dokter' }}
                    </x-button>
                </div>
            </div>

            <div class="space-y-4">
                <x-card title="Petunjuk Pengisian">
                    <ul class="space-y-2.5 text-xs leading-relaxed text-ink-2">
                        <li class="flex gap-2">
                            <x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand" />
                            <span>Kode dokter dibuat otomatis dari inisial nama bila dikosongkan, dan harus tetap unik.</span>
                        </li>
                        <li class="flex gap-2">
                            <x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand" />
                            <span>Poli penempatan menentukan dokter yang terpilih otomatis pada pendaftaran pasien.</span>
                        </li>
                        <li class="flex gap-2">
                            <x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand" />
                            <span>Nomor SIP diisi sesuai Surat Izin Praktik yang masih berlaku.</span>
                        </li>
                        <li class="flex gap-2">
                            <x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand" />
                            <span>Hubungkan akun login agar rekam medis tercatat atas nama dokter.</span>
                        </li>
                    </ul>
                </x-card>

                @if ($editing)
                    <x-card title="Ringkasan" padding="p-0">
                        <dl class="divide-y divide-line text-sm">
                            <div class="flex items-center justify-between gap-3 px-5 py-3">
                                <dt class="text-ink-3">Jadwal Terdaftar</dt>
                                <dd class="font-semibold text-ink tabular-nums">
                                    {{ $doctor->schedules()->count() }}
                                </dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 px-5 py-3">
                                <dt class="text-ink-3">Terakhir diubah</dt>
                                <dd class="font-medium text-ink">
                                    {{ $doctor->updated_at?->translatedFormat('d M Y') ?? '-' }}
                                </dd>
                            </div>
                        </dl>
                    </x-card>
                @endif
            </div>
        </form>
    </div>
@endsection
