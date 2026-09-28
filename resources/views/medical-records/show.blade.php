@extends('layouts.app')

@section('title', 'Rekam Medis')
@section('heading', 'Rekam Medis ' . $record->record_type->shortLabel())
@section('subheading', $record->patient->name . ' - ' . $record->created_at->translatedFormat('d F Y H:i'))

@section('content')
    <div class="space-y-5">

        {{-- ===================== Header ===================== --}}
        <x-card>
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-badge :class="$record->record_type->badgeClass()">{{ $record->record_type->label() }}</x-badge>
                        <x-badge :class="$record->status->badgeClass()" dot>{{ $record->status->label() }}</x-badge>
                        <a href="{{ route('registrations.show', $record->registration) }}"
                           class="font-mono text-xs font-bold text-brand hover:underline">
                            {{ $record->registration->queue_code }}
                        </a>
                    </div>

                    <h2 class="mt-3 text-lg font-bold text-ink">{{ $record->patient->name }}</h2>
                    <p class="mt-0.5 text-sm text-ink-3">
                        <span class="font-mono">{{ $record->patient->mr_number }}</span>
                        &middot; {{ $record->patient->gender->label() }}
                        &middot; {{ $record->patient->age }} th
                        &middot; {{ $record->patient->insurance_type->label() }}
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    @if ($record->status->isEditable() && auth()->user()->can('emr.manage'))
                        <x-button :href="route('medical-records.edit', $record)" size="sm" variant="secondary" icon="pencil">
                            Ubah
                        </x-button>

                        <form method="POST" action="{{ route('medical-records.finalize', $record) }}">
                            @csrf
                            <x-button type="submit" size="sm" icon="lock"
                                      x-on:click.prevent="$dispatch('confirm', {
                                          action: $el.form.action,
                                          method: 'POST',
                                          title: 'Finalisasi rekam medis?',
                                          message: 'Setelah difinalisasi, rekam medis tidak dapat diubah atau dihapus. Pastikan diagnosis dan tindakan sudah lengkap.',
                                          confirmLabel: 'Finalisasi',
                                      })">
                                Finalisasi
                            </x-button>
                        </form>
                    @endif

                    <x-button :href="route('patients.show', $record->patient)" size="sm" variant="ghost" icon="users">
                        Profil Pasien
                    </x-button>
                </div>
            </div>

            @if ($record->vitals_summary)
                <dl class="mt-5 grid grid-cols-2 gap-3 border-t border-line pt-4 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach ([
                        'Tekanan Darah' => [$record->blood_pressure, 'mmHg'],
                        'Nadi' => [$record->pulse, '/mnt'],
                        'Suhu' => [$record->temperature, '°C'],
                        'Napas' => [$record->respiratory_rate, '/mnt'],
                        'Berat' => [$record->weight, 'kg'],
                        'Tinggi' => [$record->height, 'cm'],
                    ] as $label => [$value, $unit])
                        <div class="rounded-lg bg-surface-2 px-3 py-2">
                            <dt class="text-[10px] font-semibold tracking-wide text-ink-3 uppercase">{{ $label }}</dt>
                            <dd class="mt-0.5 text-sm font-bold text-ink">
                                @if ($value !== null)
                                    {{ $value }} <span class="text-[11px] font-medium text-ink-3">{{ $unit }}</span>
                                @else
                                    <span class="text-ink-3">-</span>
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            @endif
        </x-card>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <div class="space-y-5 lg:col-span-2">

                {{-- ===================== Keluhan & diagnosis ===================== --}}
                <x-card title="Keluhan dan Diagnosis">
                    <div class="space-y-4">
                        @if ($record->chief_complaint)
                            <div>
                                <p class="text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Keluhan Utama</p>
                                <p class="mt-1 text-sm leading-relaxed text-ink">{{ $record->chief_complaint }}</p>
                            </div>
                        @endif

                        @if ($record->diagnosis)
                            <div>
                                <p class="text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Ringkasan Diagnosis</p>
                                <p class="mt-1 text-sm leading-relaxed text-ink">{{ $record->diagnosis }}</p>
                            </div>
                        @endif

                        @if ($record->diagnoses->isNotEmpty())
                            <div>
                                <p class="text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Kode Diagnosis (ICD-10)</p>
                                <div class="mt-2 overflow-x-auto">
                                    <table class="w-full text-left text-sm">
                                        <thead>
                                            <tr class="border-b border-line">
                                                <th scope="col" class="pb-2 pr-4 text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Kode</th>
                                                <th scope="col" class="pb-2 pr-4 text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Jenis</th>
                                                <th scope="col" class="pb-2 text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Deskripsi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($record->diagnoses as $diagnosis)
                                                <tr class="border-b border-line last:border-0">
                                                    <td class="py-2 pr-4 font-mono font-bold text-brand">
                                                        {{ $diagnosis->icd10Code?->code ?? '-' }}
                                                    </td>
                                                    <td class="py-2 pr-4">
                                                        <x-badge :class="$diagnosis->diagnosis_type->badgeClass()">
                                                            {{ $diagnosis->diagnosis_type->label() }}
                                                        </x-badge>
                                                    </td>
                                                    <td class="py-2 text-ink-2">
                                                        {{ $diagnosis->description ?? $diagnosis->icd10Code?->description ?? '-' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="rounded-lg border border-dashed border-line-2 px-4 py-6 text-center">
                                <x-icon name="exclamation" class="mx-auto h-6 w-6 text-warning-ink" />
                                <p class="mt-2 text-sm font-semibold text-ink">Kode diagnosis belum diisi</p>
                                <p class="mt-0.5 text-xs text-ink-3">
                                    Diagnosis ICD-10 wajib diisi sebelum rekam medis dapat difinalisasi.
                                </p>
                            </div>
                        @endif
                    </div>
                </x-card>

                {{-- ===================== Anamnesis & pemeriksaan ===================== --}}
                <x-card title="Anamnesis dan Pemeriksaan">
                    <div class="space-y-4">
                        @foreach ([
                            'Anamnesis' => $record->anamnesis,
                            'Pemeriksaan Fisik' => $record->physical_exam,
                            'Rencana Terapi' => $record->treatment_plan,
                            'Catatan Dokter' => $record->notes,
                        ] as $label => $value)
                            @if ($value)
                                <div>
                                    <p class="text-[11px] font-semibold tracking-wide text-ink-3 uppercase">{{ $label }}</p>
                                    <p class="mt-1 text-sm leading-relaxed whitespace-pre-line text-ink">{{ $value }}</p>
                                </div>
                            @endif
                        @endforeach

                        @if (blank($record->anamnesis) && blank($record->physical_exam)
                            && blank($record->treatment_plan) && blank($record->notes))
                            <p class="text-sm text-ink-3">Belum ada catatan klinis pada rekam medis ini.</p>
                        @endif
                    </div>
                </x-card>

                {{-- ===================== Tindakan ===================== --}}
                <x-card title="Tindakan" :subtitle="$record->procedures->count() . ' tindakan tercatat'" padding="p-0">
                    @if ($record->procedures->isEmpty())
                        <x-empty-state compact icon="clipboard" title="Belum ada tindakan"
                                       message="Tidak ada tindakan yang dicatat pada kunjungan ini." />
                    @else
                        <div class="table-wrap">
                            <table class="table table-stack" style="min-width: 34rem">
                                <thead>
                                    <tr>
                                        <th scope="col">Tindakan</th>
                                        <th scope="col" class="hidden sm:table-cell">Kode</th>
                                        <th scope="col" class="text-right">Biaya</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($record->procedures as $procedure)
                                        <tr>
                                            <td data-label="Tindakan">
                                                <span class="font-medium text-ink">{{ $procedure->name }}</span>
                                                @if ($procedure->notes)
                                                    <span class="mt-0.5 block text-xs text-ink-3">{{ $procedure->notes }}</span>
                                                @endif
                                            </td>
                                            <td data-label="Kode" class="hidden sm:table-cell font-mono text-xs text-ink-2">
                                                {{ $procedure->code ?? '-' }}
                                            </td>
                                            <td data-label="Biaya" class="text-right font-semibold tabular-nums">
                                                @if ($procedure->fee > 0)
                                                    Rp {{ number_format($procedure->fee, 0, ',', '.') }}
                                                @else
                                                    <span class="text-ink-3">Tidak Berbayar</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </x-card>

                {{-- ===================== Resep ===================== --}}
                <x-card title="Resep" :subtitle="$record->prescriptions->count() . ' resep'" padding="p-0">
                    <x-slot:actions>
                        @if ($record->status->isEditable() && auth()->user()->can('emr.manage') && $record->prescriptions->isEmpty())
                            <x-button size="sm" variant="secondary" icon="plus"
                                      x-on:click="$dispatch('open-prescription')">
                                Tambah Resep
                            </x-button>
                        @endif
                    </x-slot:actions>

                    @if ($record->prescriptions->isEmpty())
                        <x-empty-state compact icon="pill" title="Belum ada resep"
                                       message="Resep dapat ditambahkan oleh dokter pada rekam medis yang masih berstatus draft." />
                    @else
                        <ul class="divide-y divide-line">
                            @foreach ($record->prescriptions as $prescription)
                                <li class="p-5">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <a href="{{ route('pharmacy.prescriptions.show', $prescription) }}"
                                           class="font-mono text-sm font-bold text-brand hover:underline">
                                            {{ $prescription->prescription_number }}
                                        </a>
                                        <x-badge :class="$prescription->status->badgeClass()">
                                            {{ $prescription->status->label() }}
                                        </x-badge>
                                    </div>

                                    <ul class="mt-2.5 space-y-1.5">
                                        @foreach ($prescription->items as $item)
                                            <li class="flex flex-wrap items-baseline justify-between gap-2 text-sm">
                                                <span class="text-ink">
                                                    {{ $item->drug?->display_name ?? 'Obat tidak tersedia' }}
                                                    @if ($item->sig)
                                                        <span class="text-xs text-ink-3">&mdash; {{ $item->sig }}</span>
                                                    @endif
                                                </span>
                                                <span class="text-xs text-ink-3 tabular-nums">
                                                    {{ $item->quantity }} {{ $item->drug?->unit ?? '' }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-card>
            </div>

            {{-- ===================== Panel samping ===================== --}}
            <div class="space-y-4">
                <x-card title="Metadata">
                    <dl class="space-y-3 text-sm">
                        @foreach ([
                            ['Dokter', $record->doctor?->name ?? 'Tidak diisi'],
                            ['Poli', $record->registration?->poly?->name ?? '-'],
                            ['Tanggal Kunjungan', $record->registration?->visit_date?->translatedFormat('d F Y')],
                            ['Ditulis Oleh', $record->author?->name ?? 'Sistem'],
                            ['Dibuat', $record->created_at->translatedFormat('d F Y H:i')],
                            ['Diperbarui', $record->updated_at->translatedFormat('d F Y H:i')],
                        ] as [$label, $value])
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="shrink-0 text-xs text-ink-3">{{ $label }}</dt>
                                <dd class="text-right text-xs font-medium text-ink">{{ $value }}</dd>
                            </div>
                        @endforeach

                        @if ($record->finalized_at)
                            <div class="flex items-baseline justify-between gap-3 border-t border-line pt-3">
                                <dt class="shrink-0 text-xs text-ink-3">Difinalisasi</dt>
                                <dd class="text-right text-xs font-semibold text-success-ink">
                                    {{ $record->finalized_at->translatedFormat('d F Y H:i') }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </x-card>

                <x-card title="Navigasi">
                    <div class="space-y-2">
                        <a href="{{ route('registrations.show', $record->registration)" class="nav-link w-full">
                            <x-icon name="clipboard" class="h-4 w-4" />
                            Detail Kunjungan
                        </a>
                        <a href="{{ route('patients.show', $record->patient)" class="nav-link w-full">
                            <x-icon name="users" class="h-4 w-4" />
                            Profil Pasien
                        </a>
                        <a href="{{ route('medical-records.index') }}" class="nav-link w-full">
                            <x-icon name="stethoscope" class="h-4 w-4" />
                            Semua Rekam Medis
                        </a>
                    </div>
                </x-card>
            </div>
        </div>
    </div>

    {{-- ===================== Modal tambah resep ===================== --}}
    @if ($record->status->isEditable() && auth()->user()->can('emr.manage'))
        @php
            $prescriptionDrugs = \App\Models\Drug::active()
                ->orderBy('name')
                ->get(['id', 'name', 'strength', 'form', 'unit', 'price', 'stock', 'min_stock'])
                ->map(fn ($d) => [
                    'value' => $d->id,
                    'label' => $d->display_name.' - '.$d->stock.' '.$d->unit.' - Rp '.number_format($d->price, 0, ',', '.'),
                ])->all();
        @endphp

        <div x-data="{
                open: false,
                items: [{ drug_id: '', dosage: '', route: '', frequency: '', duration: '', quantity: 1 }],
                addItem() { this.items.push({ drug_id: '', dosage: '', route: '', frequency: '', duration: '', quantity: 1 }) },
                removeItem(index) { this.items.splice(index, 1) },
            }"
             x-on:open-prescription.window="open = true">

            <x-modal title="Tambah Resep" icon="pill" maxWidth="3xl">
                <form method="POST" action="{{ route('medical-records.prescriptions.store', $record) }}">
                    @csrf

                    <div class="space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="rounded-lg border border-line p-3">
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-12">
                                    <div class="sm:col-span-6">
                                        <label :for="'drug_' + index" class="field-label">Obat</label>
                                        <select :id="'drug_' + index" :name="'items[' + index + '][drug_id]'"
                                                x-model="item.drug_id" class="field-control" required>
                                            <option value="">Pilih obat</option>
                                            @foreach ($prescriptionDrugs as $drug)
                                                <option value="{{ $drug['value'] }}">{{ $drug['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="sm:col-span-3">
                                        <label :for="'dosage_' + index" class="field-label">Dosis</label>
                                        <input :id="'dosage_' + index" type="text" :name="'items[' + index + '][dosage]'"
                                               x-model="item.dosage" maxlength="100" class="field-control"
                                               placeholder="1 tablet">
                                    </div>

                                    <div class="sm:col-span-3">
                                        <label :for="'qty_' + index" class="field-label">Jumlah</label>
                                        <input :id="'qty_' + index" type="number" :name="'items[' + index + '][quantity]'"
                                               x-model.number="item.quantity" min="1" max="10000" class="field-control" required>
                                    </div>
                                </div>

                                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
                                    <div>
                                        <label :for="'route_' + index" class="field-label">Cara Pakai</label>
                                        <input :id="'route_' + index" type="text" :name="'items[' + index + '][route]'"
                                               x-model="item.route" maxlength="60" class="field-control"
                                               placeholder=" orally">
                                    </div>

                                    <div>
                                        <label :for="'freq_' + index" class="field-label">Frekuensi</label>
                                        <input :id="'freq_' + index" type="text" :name="'items[' + index + '][frequency]'"
                                               x-model="item.frequency" maxlength="100" class="field-control"
                                               placeholder="3x sehari">
                                    </div>

                                    <div>
                                        <label :for="'dur_' + index" class="field-label">Durasi</label>
                                        <input :id="'dur_' + index" type="text" :name="'items[' + index + '][duration]'"
                                               x-model="item.duration" maxlength="100" class="field-control"
                                               placeholder="7 hari">
                                    </div>
                                </div>

                                <button type="button" x-on:click="removeItem(index)" class="btn btn-sm btn-danger mt-3">
                                    Hapus Item
                                </button>
                            </div>
                        </template>

                        <button type="button" x-on:click="addItem()" class="btn btn-sm btn-secondary">
                            Tambah Item Obat
                        </button>

                        <x-textarea name="notes" label="Catatan Resep" :rows="2" :maxlength="1000"
                                    placeholder="Catatan untuk apoteker (opsional)" />
                    </div>

                    <x-slot:footer>
                        <button type="button" x-on:click="open = false" class="btn btn-md btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-md btn-primary" x-bind:disabled="items.length === 0">
                            Simpan Resep
                        </button>
                    </x-slot:footer>
                </form>
            </x-modal>
        </div>
    @endif
@endsection
