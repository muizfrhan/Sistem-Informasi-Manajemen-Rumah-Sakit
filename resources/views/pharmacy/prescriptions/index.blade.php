@extends('layouts.app')

@section('title', 'Resep & Stok Obat')
@section('heading', 'Resep & Stok Obat')
@section('subheading', $prescriptions->total() . ' resep tercatat')

@section('content')
    <div class="space-y-5">
        <x-page-header title="Resep & Stok Obat"
                       :description="'Total ' . number_format($prescriptions->total(), 0, ',', '.') . ' resep'">
            <x-slot:actions>
                <x-button :href="route('master.drugs.index')" variant="secondary" icon="cube">Data Obat</x-button>
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <x-stat-card label="Menunggu Apoteker" :value="$pendingCount" unit="resep"
                         tone="warning" icon="clock" />
            <x-stat-card label="Total Resep" :value="$prescriptions->total()" unit="resep"
                         tone="brand" icon="pill" />
            <x-stat-card label="Diserahkan" :value="$prescriptions->total() - $pendingCount" unit="resep"
                         tone="success" icon="check-circle" />
        </div>

        <x-card padding="p-0">
            <x-filter-bar :action="route('pharmacy.prescriptions.index')" :reset="route('pharmacy.prescriptions.index')">
                <x-slot:fields>
                    <div>
                        <label for="q" class="field-label">Pasien</label>
                        <input id="q" name="q" type="search" value="{{ request('q') }}"
                               placeholder="Nama, No. RM, atau NIK" class="field-control">
                    </div>

                    <div>
                        <label for="status" class="field-label">Status Resep</label>
                        <select id="status" name="status" class="field-control">
                            <option value="">Semua status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="from" class="field-label">Dari Tanggal</label>
                        <input id="from" name="from" type="date" value="{{ request('from') }}" class="field-control">
                    </div>

                    <div>
                        <label for="to" class="field-label">Sampai Tanggal</label>
                        <input id="to" name="to" type="date" value="{{ request('to') }}" class="field-control">
                    </div>
                </x-slot:fields>
            </x-filter-bar>

            <x-data-table :cols="$prescriptions->count()" min-width="62rem"
                          empty="Belum ada resep"
                          empty-message="Belum ada resep yang cocok dengan filter ini. Resep dibuat dokter dari rekam medis."
                          empty-icon="pill">
                <x-slot:emptyAction>
                    <x-button :href="route('pharmacy.prescriptions.index')" size="sm" variant="secondary" icon="refresh">Reset Filter</x-button>
                </x-slot:emptyAction>

                <x-slot:head>
                    <tr>
                        <th scope="col">No. Resep</th>
                        <th scope="col">Pasien</th>
                        <th scope="col" class="hidden md:table-cell">Dokter</th>
                        <th scope="col">Item</th>
                        <th scope="col" class="hidden sm:table-cell">Nilai</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-right">Aksi</th>
                    </tr>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($prescriptions as $prescription)
                        <tr>
                            <td data-label="No. Resep">
                                <a href="{{ route('pharmacy.prescriptions.show', $prescription) }}"
                                   class="font-mono text-xs font-bold text-brand hover:underline">
                                    {{ $prescription->prescription_number }}
                                </a>
                                <span class="mt-0.5 block text-xs text-ink-3">
                                    {{ $prescription->created_at->translatedFormat('d M Y H:i') }}
                                </span>
                            </td>

                            <td data-label="Pasien">
                                <a href="{{ route('patients.show', $prescription->patient) }}"
                                   class="font-semibold text-ink hover:underline">
                                    {{ $prescription->patient->name }}
                                </a>
                                <span class="block font-mono text-xs text-ink-3">
                                    {{ $prescription->patient->mr_number }}
                                </span>
                            </td>

                            <td data-label="Dokter" class="hidden md:table-cell text-xs">
                                {{ $prescription->doctor?->name ?? '-' }}
                            </td>

                            <td data-label="Item">
                                <span class="block text-xs font-semibold text-ink-2">
                                    {{ $prescription->items->count() }} jenis obat
                                </span>
                                <span class="mt-0.5 line-clamp-1 text-xs text-ink-3">
                                    {{ $prescription->items->take(2)->map(fn ($i) => $i->drug?->name)->filter()->join(', ') }}
                                    @if ($prescription->items->count() > 2) … @endif
                                </span>
                            </td>

                            <td data-label="Nilai" class="hidden sm:table-cell text-xs font-semibold tabular-nums">
                                Rp {{ number_format($prescription->items->sum('subtotal'), 0, ',', '.') }}
                            </td>

                            <td data-label="Status">
                                <x-badge :class="$prescription->status->badgeClass()">
                                    {{ $prescription->status->label() }}
                                </x-badge>
                            </td>

                            <td data-label="Aksi">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if ($canManage && $prescription->status === \App\Enums\PrescriptionStatus::DRAFT)
                                        <form method="POST" action="{{ route('pharmacy.prescriptions.dispense', $prescription) }}">
                                            @csrf
                                            <x-button type="submit" size="sm" variant="primary"
                                                      x-on:click.prevent="$dispatch('confirm', {
                                                          action: $el.form.action,
                                                          method: 'POST',
                                                          title: 'Serahkan resep {{ $prescription->prescription_number }}?',
                                                          message: 'Stok obat akan berkurang sesuai jumlah resep. Tindakan ini tidak dapat dibatalkan.',
                                                          confirmLabel: 'Serahkan',
                                                      })">
                                                Serahkan
                                            </x-button>
                                        </form>
                                    @endif

                                    <x-button :href="route('pharmacy.prescriptions.show', $prescription)"
                                              size="sm" variant="{{ $prescription->status === \App\Enums\PrescriptionStatus::DRAFT ? 'secondary' : 'ghost' }}">
                                        Detail
                                    </x-button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-data-table>

            @if ($prescriptions->hasPages())
                {{ $prescriptions->links() }}
            @endif
        </x-card>
    </div>
@endsection
