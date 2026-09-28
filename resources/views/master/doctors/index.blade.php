@extends('layouts.app')

@section('title', 'Data Dokter')
@section('heading', 'Data Dokter')
@section('subheading', $doctors->total().' dokter terdaftar')

@php
    $search = trim((string) request('q', ''));
    $polyId = trim((string) request('poly_id', ''));
    $hasFilter = $search !== '' || $polyId !== '';

    $emptyCta = $hasFilter
        ? ['label' => 'Reset Filter', 'href' => route('master.doctors.index'), 'variant' => 'secondary', 'icon' => 'refresh', 'permission' => null]
        : ['label' => 'Tambah Dokter', 'href' => route('master.doctors.create'), 'variant' => 'primary', 'icon' => 'user-plus', 'permission' => 'master.manage'];

    $pageDescription = 'Total '.number_format($doctors->total(), 0, ',', '.').' dokter'
        .($search !== '' ? ' untuk pencarian '.$search : '');

    $activeCount = $doctors->getCollection()->where('is_active', true)->count();
@endphp

@section('content')
    <div class="space-y-5">
        <x-page-header title="Data Dokter" :description="$pageDescription">
            <x-slot:actions>
                <x-button permission="master.manage" :href="route('master.doctors.create')" icon="user-plus">
                    Tambah Dokter
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            <x-stat-card label="Total Dokter" :value="number_format($doctors->total(), 0, ',', '.')"
                         unit="dokter" tone="brand" icon="stethoscope" compact />
            <x-stat-card label="Dokter Aktif" :value="number_format($activeCount, 0, ',', '.')"
                         unit="dokter" tone="success" icon="check-circle"
                         hint="Dapat dipilih pada jam praktik" compact />
            <x-stat-card label="Jumlah Poli" :value="number_format($polies->count(), 0, ',', '.')"
                         unit="poli" tone="info" icon="building"
                         hint="Poli tujuan penempatan dokter" compact />
        </div>

        <x-card padding="p-0">
            <x-filter-bar :action="route('master.doctors.index')" :reset="route('master.doctors.index')">
                <x-slot:fields>
                    <div>
                        <label for="q" class="field-label">Pencarian</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-ink-3">
                                <x-icon name="search" class="h-4 w-4" />
                            </span>
                            <input id="q" name="q" type="search" value="{{ $search }}"
                                   placeholder="Nama dokter" class="field-control pl-9">
                        </div>
                    </div>

                    <div>
                        <label for="poly_id" class="field-label">Poli</label>
                        <select id="poly_id" name="poly_id" class="field-control">
                            <option value="">Semua poli</option>
                            @foreach ($polies as $poly)
                                <option value="{{ $poly->id }}" @selected((string) $poly->id === $polyId)>
                                    {{ $poly->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </x-slot:fields>

                <x-slot:default>
                    <span class="text-xs text-ink-3">
                        Menampilkan {{ $doctors->firstItem() ?? 0 }}&ndash;{{ $doctors->lastItem() ?? 0 }}
                        dari {{ number_format($doctors->total(), 0, ',', '.') }} dokter
                    </span>
                </x-slot:default>
            </x-filter-bar>

            <x-data-table :cols="$doctors->count()" min-width="58rem"
                          empty="Dokter tidak ditemukan"
                          empty-message="Belum ada dokter yang cocok dengan filter ini. Ubah pencarian atau tambahkan dokter baru."
                          empty-icon="stethoscope">
                <x-slot:emptyAction>
                    <x-button :href="$emptyCta['href']" :variant="$emptyCta['variant']" size="sm"
                              :icon="$emptyCta['icon']" :permission="$emptyCta['permission']">
                        {{ $emptyCta['label'] }}
                    </x-button>
                </x-slot:emptyAction>

                <x-slot:head>
                    <tr>
                        <th scope="col">Kode</th>
                        <th scope="col">Nama Dokter</th>
                        <th scope="col" class="hidden sm:table-cell">NIP</th>
                        <th scope="col">Spesialisasi</th>
                        <th scope="col">Poli</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-right">Aksi</th>
                    </tr>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($doctors as $doctor)
                        <tr>
                            <td data-label="Kode">
                                <span class="font-mono text-xs font-bold text-ink">{{ $doctor->code }}</span>
                            </td>

                            <td data-label="Nama Dokter">
                                <span class="font-semibold text-ink">{{ $doctor->name }}</span>
                                <span class="mt-0.5 block text-xs text-ink-3 sm:hidden">
                                    {{ $doctor->poly?->name ?? 'Belum ditempatkan' }}
                                </span>
                            </td>

                            <td data-label="NIP" class="hidden sm:table-cell">
                                <span class="font-mono text-xs text-ink-2">{{ $doctor->nip ?: '-' }}</span>
                            </td>

                            <td data-label="Spesialisasi">
                                @if ($doctor->specialty)
                                    <x-badge variant="info">{{ $doctor->specialty }}</x-badge>
                                @else
                                    <span class="text-xs text-ink-3">Umum</span>
                                @endif
                            </td>

                            <td data-label="Poli">
                                @if ($doctor->poly)
                                    <a href="{{ route('master.doctors.index', ['poly_id' => $doctor->poly_id]) }}"
                                       class="text-xs font-medium text-brand hover:underline">
                                        {{ $doctor->poly->name }}
                                    </a>
                                @else
                                    <span class="text-xs text-ink-3">Belum ditempatkan</span>
                                @endif
                            </td>

                            <td data-label="Status">
                                <x-active-badge :active="$doctor->is_active" />
                            </td>

                            <td data-label="Aksi">
                                @if ($canManage)
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('master.doctors.edit', $doctor) }}" title="Ubah data dokter"
                                           aria-label="Ubah data dokter {{ $doctor->name }}"
                                           class="rounded-lg p-2 text-ink-3 transition hover:bg-surface-2 hover:text-ink">
                                            <x-icon name="pencil" class="h-4 w-4" />
                                        </a>

                                        <form method="POST" action="{{ route('master.doctors.destroy', $doctor) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" size="sm" variant="danger" icon="trash"
                                                      x-on:click.prevent="$dispatch('confirm', {
                                                          action: $el.form.action,
                                                          method: 'DELETE',
                                                          title: 'Hapus data dokter {{ $doctor->name }}?',
                                                          message: 'Data dokter {{ $doctor->name }} akan dihapus permanen. Bila dokter sudah memiliki riwayat pasien, data tidak dapat dihapus dan hanya dapat dinonaktifkan.',
                                                          confirmLabel: 'Hapus Dokter',
                                                      })">
                                                Hapus
                                            </x-button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-ink-3">&mdash;</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-data-table>

            @if ($doctors->hasPages())
                {{ $doctors->links() }}
            @endif
        </x-card>
    </div>
@endsection
