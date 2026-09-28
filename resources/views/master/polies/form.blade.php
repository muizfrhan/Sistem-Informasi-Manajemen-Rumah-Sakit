@extends('layouts.app')

@section('title', $poly->exists ? 'Ubah Poli' : 'Tambah Poli')
@section('heading', $poly->exists ? 'Ubah Data Poli' : 'Tambah Poli Baru')
@section('subheading', $poly->exists ? $poly->name : 'Lengkapi informasi poli')

@php
    $editing = $poly->exists;
    $action = $editing ? route('master.polies.update', $poly) : route('master.polies.store');

    $pageTitle = $editing ? 'Ubah Data Poli' : 'Tambah Poli Baru';
    $pageDescription = $editing
        ? 'Perbarui informasi poli '.$poly->name.' sesuai ketentuan layanan terbaru.'
        : 'Lengkapi informasi poli agar dapat dipilih pada pendaftaran pasien dan papan antrean.';
@endphp

@section('content')
    <div class="space-y-5">
        <x-page-header :title="$pageTitle" :description="$pageDescription">
            <x-slot:actions>
                <x-button :href="route('master.polies.index')" variant="secondary" icon="arrow-left">Kembali</x-button>
            </x-slot:actions>
        </x-page-header>

        <form method="POST" action="{{ $action }}" class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="space-y-5 lg:col-span-2">
                <x-card title="Informasi Poli" subtitle="Keterangan dasar poli yang tampil pada formulir pendaftaran">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-input name="code" label="Kode Poli" required :maxlength="20"
                                 :value="old('code', $poly->code)"
                                 placeholder="P001"
                                 hint="Kode unik tanpa spasi, maksimal 20 karakter."
                                 x-on:input="$el.value = $el.value.toUpperCase().replace(/[^A-Z0-9_-]/g, '')" />

                        <x-input name="queue_prefix" label="Prefiks Antrean" required :maxlength="10"
                                 :value="old('queue_prefix', $poly->queue_prefix ?? 'A')"
                                 placeholder="A"
                                 hint="Huruf saja dan harus unik antar poli, contoh: A, B, C."
                                 x-on:input="$el.value = $el.value.toUpperCase().replace(/[^A-Z]/g, '')" />

                        <x-input name="name" label="Nama Poli" required :maxlength="100" class="sm:col-span-2"
                                 :value="old('name', $poly->name)"
                                 placeholder="Poli Umum"
                                 hint="Nama akan tampil pada pilihan poli pendaftaran dan papan antrean." />

                        <x-textarea name="description" label="Deskripsi" :rows="3" :maxlength="255" class="sm:col-span-2"
                                    :value="old('description', $poly->description)"
                                    placeholder="Keterangan singkat layanan yang ditangani poli ini." />

                        <x-checkbox name="is_active" label="Poli Aktif"
                                    hint="Poli nonaktif tidak muncul pada pilihan pendaftaran pasien."
                                    :value="old('is_active', $editing ? $poly->is_active : true)" />
                    </div>
                </x-card>

                <div class="flex flex-wrap items-center justify-end gap-2">
                    <x-button :href="route('master.polies.index')" variant="ghost">Batal</x-button>
                    <x-button type="submit" icon="check">
                        {{ $editing ? 'Simpan Perubahan' : 'Tambah Poli' }}
                    </x-button>
                </div>
            </div>

            <div class="space-y-4">
                <x-card title="Aturan Prefiks Antrean">
                    <p class="text-xs leading-relaxed text-ink-2">
                        Prefiks adalah huruf yang menempel di depan nomor antrean, misalnya
                        <span class="font-mono font-bold text-brand">{{ old('queue_prefix', $poly->queue_prefix ?? 'A') }}-001</span>.
                        Angka dimulai dari 001 dan dilanjutkan pada hari berikutnya.
                    </p>

                    <ul class="mt-4 space-y-2.5 text-xs leading-relaxed text-ink-2">
                        <li class="flex gap-2">
                            <x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand" />
                            <span>Prefiks harus berupa <strong>huruf saja</strong>, maksimal 10 karakter.</span>
                        </li>
                        <li class="flex gap-2">
                            <x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand" />
                            <span>Setiap poli harus memiliki prefiks <strong>berbeda</strong> agar nomor antrean tidak ambigu.</span>
                        </li>
                        <li class="flex gap-2">
                            <x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand" />
                            <span>Perubahan prefiks tidak mengubah nomor antrean yang sudah terbit.</span>
                        </li>
                        <li class="flex gap-2">
                            <x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand" />
                            <span>Poli yang sudah memiliki pendaftaran tidak dapat dihapus, cukup dinonaktifkan.</span>
                        </li>
                    </ul>
                </x-card>

                @if ($editing)
                    <x-card title="Ringkasan" padding="p-0">
                        <dl class="divide-y divide-line text-sm">
                            <div class="flex items-center justify-between gap-3 px-5 py-3">
                                <dt class="text-ink-3">Dibuat</dt>
                                <dd class="font-medium text-ink">
                                    {{ $poly->created_at?->translatedFormat('d M Y') ?? '-' }}
                                </dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 px-5 py-3">
                                <dt class="text-ink-3">Terakhir diubah</dt>
                                <dd class="font-medium text-ink">
                                    {{ $poly->updated_at?->translatedFormat('d M Y') ?? '-' }}
                                </dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 px-5 py-3">
                                <dt class="text-ink-3">Jumlah dokter</dt>
                                <dd class="font-semibold text-ink tabular-nums">
                                    {{ $poly->doctors()->count() }}
                                </dd>
                            </div>
                        </dl>
                    </x-card>
                @endif
            </div>
        </form>
    </div>
@endsection
