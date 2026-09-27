@extends('layouts.app')

@section('title', $doctor->exists ? 'Ubah Dokter' : 'Tambah Dokter')
@section('heading', $doctor->exists ? 'Ubah Data Dokter' : 'Tambah Dokter Baru')
@section('subheading', $doctor->exists ? $doctor->name : 'Lengkapi informasi dokter')

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <form method="POST"
              action="{{ $doctor->exists ? route('master.doctors.update', $doctor) : route('master.doctors.store') }}"
              class="lg:col-span-2">
            @csrf
            @if ($doctor->exists) @method('PUT') @endif

            <x-card title="Identitas Dokter">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-input name="name" label="Nama & gelar" :value="$doctor->name" required maxlength="150"
                             class="sm:col-span-2" placeholder="dr. Nama Dokter, Sp.B" />
                    <x-input name="code" label="Kode Dokter" :value="$doctor->code" maxlength="20"
                             placeholder="DR001" hint="Kosongkan untuk generate otomatis" />
                    <x-input name="nip" label="NIP" :value="$doctor->nip" maxlength="30" />
                    <x-input name="specialty" label="Spesialisasi" :value="$doctor->specialty" maxlength="100" placeholder="Bedah Umum" />
                    <x-input name="sip_number" label="Nomor SIP" :value="$doctor->sip_number" maxlength="50" />
                    <x-input name="phone" label="Telepon" :value="$doctor->phone" maxlength="25" />
                </div>
            </x-card>

            <div class="mt-6">
                <x-card title="Penugasan">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-select name="poly_id" label="Poli Penempatan" :value="$doctor->poly_id"
                                  :options="$polies->pluck('name', 'id')->all()" placeholder="Belum ditempatkan" />
                        <x-select name="user_id" label="Akun Login Sistem" :value="$doctor->user_id"
                                  :options="$users->pluck('name', 'id')->all()"
                                  placeholder="Tidak dikaitkan"                                   hint="Digunakan saat dokter login ke sistem" />
                        <x-checkbox name="is_active" label="Dokter Aktif"
                                    :value="$doctor->exists ? $doctor->is_active : true" class="sm:col-span-2" />
                    </div>
                </x-card>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-button :href="route('master.doctors.index')" variant="secondary">Batal</x-button>
                <x-button type="submit">{{ $doctor->exists ? 'Simpan Perubahan' : 'Tambah Dokter' }}</x-button>
            </div>
        </form>

        <div class="space-y-6">
            <x-card title="Catatan">
                <ul class="space-y-2 text-xs text-slate-600">
                    <li>Kode dokter di-generate otomatis dari inisial nama bila dikosongkan.</li>
                    <li>Poli penempatan menentukan dokter mana yang dipilih otomatis saat pendaftaran.</li>
                    <li>Nomor SIP wajib diisi sesuai Surat Izin Praktik yang berlaku.</li>
                </ul>
            </x-card>
        </div>
    </div>
@endsection
