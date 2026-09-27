@extends('layouts.app')

@section('title', $poly->exists ? 'Ubah Poli' : 'Tambah Poli')
@section('heading', $poly->exists ? 'Ubah Data Poli' : 'Tambah Poli Baru')
@section('subheading', $poly->exists ? $poly->name : 'Lengkapi informasi poli')

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <form method="POST"
              action="{{ $poly->exists ? route('master.polies.update', $poly) : route('master.polies.store') }}"
              class="lg:col-span-2">
            @csrf
            @if ($poly->exists) @method('PUT') @endif

            <x-card title="Informasi Poli">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-input name="code" label="Kode Poli" :value="$poly->code" required maxlength="20"
                             placeholder="P001" hint="Kode unik, tanpa spasi" />
                    <x-input name="queue_prefix" label="Prefiks Antrean" :value="$poly->queue_prefix ?? 'A'" required maxlength="10"
                             placeholder="A" hint="Satu huruf, contoh: A, B, C" oninput="this.value=this.value.replace(/\W/g,'').toUpperCase()" />
                    <x-input name="name" label="Nama Poli" :value="$poly->name" required maxlength="100" class="sm:col-span-2"
                             placeholder="Poli Umum" />
                    <x-textarea name="description" label="Deskripsi" :value="$poly->description" :rows="3"
                                class="sm:col-span-2" placeholder="Keterangan singkat layanan poli" maxlength="255" />
                    <x-checkbox name="is_active" label="Poli Aktif"
                                :value="$poly->exists ? $poly->is_active : true" />
                </div>
            </x-card>

            <div class="mt-6 flex justify-end gap-3">
                <x-button :href="route('master.polies.index')" variant="secondary">Batal</x-button>
                <x-button type="submit">{{ $poly->exists ? 'Simpan Perubahan' : 'Tambah Poli' }}</x-button>
            </div>
        </form>

        <div class="space-y-6">
            <x-card title="Aturan Prefiks Antrean">
                <p class="text-xs leading-relaxed text-slate-600">
                    Prefiks dipakai pada nomor antrean, misalnya <span class="font-mono font-semibold text-teal-700">A-001</span>.
                    Prefiks harus unik antar poli agar nomor antrean tidak ambigu.
                </p>
            </x-card>
        </div>
    </div>
@endsection
