@extends('layouts.app')

@section('title', 'Data Pasien')
@section('heading', 'Data Pasien')
@section('subheading', $patients->total().' pasien terdaftar')

@php
    // Empty state hanya menampilkan satu tombol, ditentukan di sini agar
    // tidak ada kondisi Blade di dalam named slot (tidak didukung Blade).
    $hasFilter = $search !== '' || request()->filled('insurance_type') || request()->filled('status');

    $emptyCta = $hasFilter
        ? ['label' => 'Reset Filter', 'href' => route('patients.index'), 'variant' => 'secondary', 'icon' => 'refresh', 'permission' => null]
        : ['label' => 'Tambah Pasien', 'href' => route('patients.create'), 'variant' => 'primary', 'icon' => 'user-plus', 'permission' => 'patient.manage'];

    // Dirakit di PHP karena tanda kutip di dalam nilai atribut komponen
    // membuat Blade gagal mengompilasi tag <x-page-header>.
    $pageDescription = 'Total '.number_format($patients->total(), 0, ',', '.').' pasien'
        .($search !== '' ? ' untuk pencarian "'.$search.'"' : '');
@endphp

@section('content')
    <div class="space-y-5">
        <x-page-header title="Data Pasien" :description="$pageDescription">
            <x-slot:actions>
                <x-button permission="patient.manage" :href="route('patients.create')" icon="user-plus">Pasien Baru</x-button>
                <x-button :href="route('registrations.create')" variant="secondary" icon="clipboard">Pendaftaran</x-button>
            </x-slot:actions>
        </x-page-header>

        <x-card padding="p-0">
            <x-filter-bar :action="route('patients.index')" :reset="route('patients.index')">
                <x-slot:fields>
                    <div>
                        <label for="q" class="field-label">Pencarian</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-ink-3">
                                <x-icon name="search" class="h-4 w-4" />
                            </span>
                            <input id="q" name="q" type="search" value="{{ $search }}"
                                   placeholder="No. RM, NIK, atau nama"
                                   class="field-control pl-9">
                        </div>
                    </div>

                    <div>
                        <label for="insurance_type" class="field-label">Jenis Asuransi</label>
                        <select id="insurance_type" name="insurance_type" class="field-control">
                            <option value="">Semua</option>
                            @foreach ($insuranceTypes as $type)
                                <option value="{{ $type->value }}"
                                    @selected(request('insurance_type') === $type->value)>
                                    {{ $type->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status" class="field-label">Status</label>
                        <select id="status" name="status" class="field-control">
                            <option value="">Semua</option>
                            <option value="active" @selected(request('status') === 'active')>Aktif</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                        </select>
                    </div>
                </x-slot:fields>

                <x-slot:default>
                    <span class="text-xs text-ink-3">
                        Halaman {{ $patients->firstItem() ?? 0 }}&ndash;{{ $patients->lastItem() ?? 0 }}
                    </span>
                </x-slot:default>
            </x-filter-bar>

            <x-data-table :cols="$patients->count()" min-width="52rem"
                          empty="Belum ada data pasien"
                          empty-message="Belum ada pasien yang cocok dengan filter ini. Ubah pencarian atau tambahkan pasien baru."
                          empty-icon="users">
                <x-slot:emptyAction>
                    <x-button :href="$emptyCta['href']" :variant="$emptyCta['variant']" size="sm"
                              :icon="$emptyCta['icon']" :permission="$emptyCta['permission']">
                        {{ $emptyCta['label'] }}
                    </x-button>
                </x-slot:emptyAction>

                <x-slot:head>
                    <tr>
                        <th scope="col">No. RM</th>
                        <th scope="col">Nama Pasien</th>
                        <th scope="col" class="hidden md:table-cell">NIK</th>
                        <th scope="col" class="hidden lg:table-cell">Asuransi</th>
                        <th scope="col" class="hidden sm:table-cell">Jk / Usia</th>
                        <th scope="col" class="hidden sm:table-cell">Kunjungan</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-right">Aksi</th>
                    </tr>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($patients as $patient)
                        <tr>
                            <td data-label="No. RM">
                                <a href="{{ route('patients.show', $patient) }}"
                                   class="font-mono text-xs font-bold text-brand hover:underline">
                                    {{ $patient->mr_number }}
                                </a>
                            </td>

                            <td data-label="Nama">
                                <a href="{{ route('patients.show', $patient) }}"
                                   class="font-semibold text-ink hover:underline">
                                    {{ $patient->name }}
                                </a>
                                <span class="block text-xs text-ink-3 md:hidden">
                                    {{ $patient->gender->label() }} &middot; {{ $patient->age }} th
                                </span>
                            </td>

                            <td data-label="NIK" class="hidden md:table-cell">
                                <span class="font-mono text-xs text-ink-2">{{ $patient->masked_nik }}</span>
                            </td>

                            <td data-label="Asuransi" class="hidden lg:table-cell">
                                <x-badge :variant="$patient->insurance_type->value === 'NONE' ? 'slate' : 'info'">
                                    {{ $patient->insurance_type->label() }}
                                </x-badge>
                            </td>

                            <td data-label="Jk / Usia" class="hidden sm:table-cell text-xs text-ink-2">
                                {{ $patient->gender->label() }} &middot; {{ $patient->age }} th
                            </td>

                            <td data-label="Kunjungan" class="hidden sm:table-cell">
                                <span class="text-xs font-semibold text-ink-2 tabular-nums">
                                    {{ $patient->registrations_count }}
                                </span>
                            </td>

                            <td data-label="Status">
                                <x-active-badge :active="$patient->is_active" />
                            </td>

                            <td data-label="Aksi">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('patients.show', $patient) }}" title="Lihat detail"
                                       aria-label="Lihat detail {{ $patient->name }}"
                                       class="rounded-lg p-2 text-ink-3 transition hover:bg-surface-2 hover:text-ink">
                                        <x-icon name="eye" class="h-4 w-4" />
                                    </a>

                                    <x-danger-action
                                        :action="route('patients.toggle', $patient)"
                                        method="PATCH"
                                        icon="refresh"
                                        variant="subtle"
                                        label="Ubah status aktif"
                                        :tooltip="$patient->is_active ? 'Nonaktifkan pasien' : 'Aktifkan pasien'"
                                        :title="$patient->is_active ? 'Nonaktifkan pasien ini?' : 'Aktifkan pasien ini?'"
                                        :message="$patient->is_active
                                            ? 'Pasien tidak akan muncul di pilihan pencarian sampai diaktifkan kembali.'
                                            : 'Pasien akan muncul kembali di pilihan pencarian.'"
                                        confirm-label="Ya, Ubah Status"
                                        permission="patient.manage"
                                    />

                                    <a href="{{ route('patients.edit', $patient) }}" title="Ubah data"
                                       aria-label="Ubah data {{ $patient->name }}"
                                       class="rounded-lg p-2 text-ink-3 transition hover:bg-surface-2 hover:text-ink">
                                        <x-icon name="pencil" class="h-4 w-4" />
                                    </a>

                                    <x-danger-action
                                        :action="route('patients.destroy', $patient)"
                                        :tooltip="'Hapus ' . $patient->name"
                                        :title="'Hapus data ' . $patient->name . '?'"
                                        :message="'Nomor rekam medis ' . $patient->mr_number . ' akan dihapus dari daftar. Pasien yang sudah punya riwayat kunjungan, rekam medis, tagihan, atau rawat inap tidak dapat dihapus.'"
                                        permission="patient.manage"
                                    />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-data-table>

            @if ($patients->hasPages())
                {{ $patients->links() }}
            @endif
        </x-card>
    </div>
@endsection
