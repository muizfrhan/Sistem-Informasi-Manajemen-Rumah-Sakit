@extends('layouts.app')

@section('title', 'Data Poli')
@section('heading', 'Data Poli')
@section('subheading', $polies->total().' poli terdaftar')

@php
    $search = trim((string) request('q', ''));
    $status = trim((string) request('status', ''));
    $hasFilter = $search !== '' || $status !== '';

    // Slot emptyAction hanya boleh berisi satu tombol. Pilihan tombol
    // ditentukan di sini karena Blade tidak mendukung direksi di dalam
    // daftar atribut komponen maupun di dalam named slot.
    $emptyCta = $hasFilter
        ? ['label' => 'Reset Filter', 'href' => route('master.polies.index'), 'variant' => 'secondary', 'icon' => 'refresh', 'permission' => null]
        : ['label' => 'Tambah Poli', 'href' => route('master.polies.create'), 'variant' => 'primary', 'icon' => 'plus', 'permission' => 'master.manage'];

    $pageDescription = 'Total '.number_format($polies->total(), 0, ',', '.').' poli terdaftar'
        .($search !== '' ? ' untuk pencarian '.$search : '');

    $activeCount = $polies->getCollection()->where('is_active', true)->count();
@endphp

@section('content')
    <div class="space-y-5">
        <x-page-header title="Data Poli" :description="$pageDescription">
            <x-slot:actions>
                <x-button permission="master.manage" :href="route('master.polies.create')" icon="plus">
                    Tambah Poli
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            <x-stat-card label="Total Poli" :value="number_format($polies->total(), 0, ',', '.')"
                         unit="poli" tone="brand" icon="building" compact />
            <x-stat-card label="Poli Aktif" :value="number_format($activeCount, 0, ',', '.')"
                         unit="poli" tone="success" icon="check-circle"
                         hint="Tersedia untuk pendaftaran pasien" compact />
            <x-stat-card label="Poli Nonaktif" :value="number_format(max(0, $polies->total() - $activeCount), 0, ',', '.')"
                         unit="poli" tone="slate" icon="x-circle"
                         hint="Tidak muncul di pilihan pendaftaran" compact />
        </div>

        <x-card padding="p-0">
            <x-filter-bar :action="route('master.polies.index')" :reset="route('master.polies.index')">
                <x-slot:fields>
                    <div>
                        <label for="q" class="field-label">Pencarian</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-ink-3">
                                <x-icon name="search" class="h-4 w-4" />
                            </span>
                            <input id="q" name="q" type="search" value="{{ $search }}"
                                   placeholder="Kode atau nama poli" class="field-control pl-9">
                        </div>
                    </div>

                    <div>
                        <label for="status" class="field-label">Status</label>
                        <select id="status" name="status" class="field-control">
                            <option value="">Semua status</option>
                            <option value="active" @selected($status === 'active')>Aktif</option>
                            <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
                        </select>
                    </div>
                </x-slot:fields>

                <x-slot:default>
                    <span class="text-xs text-ink-3">
                        Menampilkan {{ $polies->firstItem() ?? 0 }}&ndash;{{ $polies->lastItem() ?? 0 }}
                        dari {{ number_format($polies->total(), 0, ',', '.') }} poli
                    </span>
                </x-slot:default>
            </x-filter-bar>

            <x-data-table :cols="$polies->count()" min-width="48rem"
                          empty="Belum ada poli"
                          empty-message="Belum ada poli yang cocok dengan filter ini. Ubah pencarian atau tambahkan poli baru."
                          empty-icon="building">
                <x-slot:emptyAction>
                    <x-button :href="$emptyCta['href']" :variant="$emptyCta['variant']" size="sm"
                              :icon="$emptyCta['icon']" :permission="$emptyCta['permission']">
                        {{ $emptyCta['label'] }}
                    </x-button>
                </x-slot:emptyAction>

                <x-slot:head>
                    <tr>
                        <th scope="col">Kode</th>
                        <th scope="col">Nama Poli</th>
                        <th scope="col">Prefiks Antrean</th>
                        <th scope="col" class="text-center">Dokter</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-right">Aksi</th>
                    </tr>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($polies as $poly)
                        <tr>
                            <td data-label="Kode">
                                <span class="font-mono text-xs font-bold text-ink">{{ $poly->code }}</span>
                            </td>

                            <td data-label="Nama Poli">
                                <span class="font-semibold text-ink">{{ $poly->name }}</span>
                                @if ($poly->description)
                                    <span class="mt-0.5 block text-xs text-ink-3">{{ $poly->description }}</span>
                                @else
                                    <span class="mt-0.5 block text-xs text-ink-3 md:hidden">Tanpa deskripsi</span>
                                @endif
                            </td>

                            <td data-label="Prefiks Antrean">
                                <span class="font-mono text-sm font-bold text-brand">{{ $poly->queue_prefix }}</span>
                                <span class="mt-0.5 block font-mono text-[11px] text-ink-3">
                                    {{ $poly->queue_prefix }}-001
                                </span>
                            </td>

                            <td data-label="Jumlah Dokter" class="text-center">
                                @if ($poly->doctors_count > 0)
                                    <a href="{{ route('master.doctors.index', ['poly_id' => $poly->id]) }}"
                                       class="text-xs font-semibold text-brand hover:underline tabular-nums">
                                        {{ $poly->doctors_count }}
                                    </a>
                                @else
                                    <span class="text-xs text-ink-3 tabular-nums">0</span>
                                @endif
                            </td>

                            <td data-label="Status">
                                <x-active-badge :active="$poly->is_active" />
                            </td>

                            <td data-label="Aksi">
                                @if ($canManage)
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('master.polies.edit', $poly) }}" title="Ubah data poli"
                                           aria-label="Ubah data poli {{ $poly->name }}"
                                           class="rounded-lg p-2 text-ink-3 transition hover:bg-surface-2 hover:text-ink">
                                            <x-icon name="pencil" class="h-4 w-4" />
                                        </a>

                                        <form method="POST" action="{{ route('master.polies.destroy', $poly) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" size="sm" variant="danger" icon="trash"
                                                      x-on:click.prevent="$dispatch('confirm', {
                                                          action: $el.form.action,
                                                          method: 'DELETE',
                                                          title: 'Hapus poli {{ $poly->name }}?',
                                                          message: 'Poli {{ $poly->name }} beserta prefiks antrean {{ $poly->queue_prefix }} akan dihapus permanen. Bila poli sudah memiliki data pendaftaran, poli tidak dapat dihapus dan hanya dapat dinonaktifkan.',
                                                          confirmLabel: 'Hapus Poli',
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

            @if ($polies->hasPages())
                {{ $polies->links() }}
            @endif
        </x-card>
    </div>
@endsection
