@extends('layouts.app')

@section('title', 'Resep ' . $prescription->prescription_number)
@section('heading', 'Resep ' . $prescription->prescription_number)
@section('subheading', $prescription->patient->name . ' - ' . $prescription->created_at->translatedFormat('d F Y'))

@php
    // Subtitle dirangkai di PHP: atribut komponen Blade tidak boleh memuat
    // karakter kutip ganda di dalam nilai atributnya.
    $prescriptionTotal = $prescription->items->sum('subtotal');
    $totalSubtitle = 'Nilai total Rp '.number_format($prescriptionTotal, 0, ',', '.');
@endphp

@section('content')
    <div class="space-y-5">

        {{-- ===================== Header ===================== --}}
        <x-card>
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-badge :class="$prescription->status->badgeClass()" dot>
                            {{ $prescription->status->label() }}
                        </x-badge>
                        <span class="text-xs text-ink-3">
                            {{ $prescription->items->count() }} jenis obat
                        </span>
                    </div>

                    <h2 class="mt-3 text-lg font-bold text-ink">{{ $prescription->patient->name }}</h2>
                    <p class="mt-0.5 text-sm text-ink-3">
                        <span class="font-mono">{{ $prescription->patient->mr_number }}</span>
                        &middot; {{ $prescription->patient->gender->label() }}
                        &middot; {{ $prescription->patient->age }} th
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    @if ($canManage && $prescription->status === \App\Enums\PrescriptionStatus::DRAFT)
                        <form method="POST" action="{{ route('pharmacy.prescriptions.dispense', $prescription) }}">
                            @csrf
                            <x-button type="submit" icon="check"
                                      x-on:click.prevent="$dispatch('confirm', {
                                          action: $el.form.action,
                                          method: 'POST',
                                          title: 'Serahkan resep ini?',
                                          message: 'Stok obat akan berkurang sesuai jumlah resep. Tindakan ini tidak dapat dibatalkan.',
                                          confirmLabel: 'Serahkan',
                                      })">
                                Serahkan Resep
                            </x-button>
                        </form>

                        <form method="POST" action="{{ route('pharmacy.prescriptions.cancel', $prescription) }}">
                            @csrf
                            <x-button type="submit" variant="danger" icon="x-circle"
                                      x-on:click.prevent="$dispatch('confirm', {
                                          action: $el.form.action,
                                          method: 'POST',
                                          title: 'Batalkan resep ini?',
                                          message: 'Resep {{ $prescription->prescription_number }} akan dibatalkan dan tidak dapat diserahkan.',
                                          confirmLabel: 'Batalkan Resep',
                                      })">
                                Batalkan
                            </x-button>
                        </form>
                    @endif

                    <x-button :href="route('patients.show', $prescription->patient)" variant="ghost" icon="users" size="sm">
                        Profil Pasien
                    </x-button>
                </div>
            </div>
        </x-card>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <div class="space-y-5 lg:col-span-2">
                {{-- ===================== Item resep ===================== --}}
                <x-card title="Item Resep" :subtitle="$totalSubtitle" padding="p-0">
                    <x-data-table :cols="$prescription->items->count()" min-width="44rem">
                        <x-slot:head>
                            <tr>
                                <th scope="col">Obat</th>
                                <th scope="col" class="hidden sm:table-cell">Aturan Pakai</th>
                                <th scope="col">Jumlah</th>
                                <th scope="col" class="text-right">Subtotal</th>
                            </tr>
                        </x-slot:head>

                        <x-slot:body>
                            @foreach ($prescription->items as $item)
                                <tr>
                                    <td data-label="Obat">
                                        <span class="font-semibold text-ink">
                                            {{ $item->drug?->display_name ?? 'Obat tidak tersedia' }}
                                        </span>
                                        <span class="mt-0.5 block font-mono text-xs text-ink-3">
                                            {{ $item->drug?->code ?? '-' }} &middot;
                                            Rp {{ number_format($item->unit_price, 0, ',', '.') }}/{{ $item->drug?->unit }}
                                        </span>
                                        @if ($item->drug && $item->drug->stock < $item->quantity)
                                            <x-badge variant="danger" class="mt-1">
                                                Stok kurang: {{ $item->drug->stock }} {{ $item->drug->unit }}
                                            </x-badge>
                                        @endif
                                        @if ($item->drug?->isExpired())
                                            <x-badge variant="danger" class="mt-1">Kedaluwarsa</x-badge>
                                        @endif
                                    </td>

                                    <td data-label="Aturan Pakai" class="hidden sm:table-cell text-xs text-ink-2">
                                        {{ $item->sig ?: '-' }}
                                        @if ($item->instructions)
                                            <span class="mt-0.5 block text-ink-3">{{ $item->instructions }}</span>
                                        @endif
                                    </td>

                                    <td data-label="Jumlah" class="text-sm font-semibold tabular-nums">
                                        {{ $item->quantity }} {{ $item->drug?->unit ?? '' }}
                                    </td>

                                    <td data-label="Subtotal" class="text-right font-semibold tabular-nums">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </x-slot:body>
                    </x-data-table>

                    <div class="flex items-center justify-end gap-6 border-t border-line bg-surface-2 px-5 py-4">
                        <div class="text-right">
                            <p class="text-xs text-ink-3">Total Nilai Resep</p>
                            <p class="text-xl font-bold text-ink tabular-nums">
                                Rp {{ number_format($prescriptionTotal, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </x-card>

                @if ($prescription->notes)
                    <x-card title="Catatan Resep">
                        <p class="text-sm leading-relaxed whitespace-pre-line text-ink-2">{{ $prescription->notes }}</p>
                    </x-card>
                @endif
            </div>

            {{-- ===================== Panel samping ===================== --}}
            <div class="space-y-4">
                <x-card title="Informasi Resep">
                    <dl class="space-y-3 text-sm">
                        @foreach ([
                            ['Nomor Resep', $prescription->prescription_number, true],
                            ['Dibuat', $prescription->created_at->translatedFormat('d F Y H:i'), false],
                            ['Dokter', $prescription->doctor?->name ?? 'Tidak diisi', false],
                            ['Rekam Medis', $prescription->medicalRecord?->record_type?->label() ?? '-', false],
                            ['Diserahkan', $prescription->dispensed_at?->translatedFormat('d F Y H:i') ?? 'Belum', false],
                            ['Apoteker', $prescription->dispenser?->name ?? '-', false],
                        ] as [$label, $value, $mono])
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="shrink-0 text-xs text-ink-3">{{ $label }}</dt>
                                <dd @class([
                                    'text-right text-xs font-medium text-ink',
                                    'font-mono' => $mono,
                                ])>{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </x-card>

                <x-card title="Ketersediaan Stok" padding="p-4">
                    @if ($prescription->items->isEmpty())
                        <p class="text-xs text-ink-3">Resep tidak memiliki item obat.</p>
                    @else
                        <ul class="space-y-2.5">
                            @foreach ($prescription->items as $item)
                                @php
                                    $stock = $item->drug?->stock ?? 0;
                                    $ratio = $item->quantity > 0 ? min(100, (int) round($stock / $item->quantity * 100)) : 100;
                                    $sufficient = $stock >= $item->quantity;
                                @endphp
                                <li>
                                    <div class="mb-1 flex items-center justify-between gap-2 text-xs">
                                        <span class="truncate text-ink-2">{{ $item->drug?->name ?? 'Obat dihapus' }}</span>
                                        <span @class([
                                            'font-semibold tabular-nums',
                                            'text-success-ink' => $sufficient,
                                            'text-danger-ink' => ! $sufficient,
                                        ])>
                                            {{ $stock }} / {{ $item->quantity }} {{ $item->drug?->unit ?? '' }}
                                        </span>
                                    </div>
                                    <div class="h-1.5 overflow-hidden rounded-full bg-surface-3">
                                        <div @class([
                                            'h-full rounded-full',
                                            'bg-emerald-500' => $sufficient,
                                            'bg-rose-500' => ! $sufficient,
                                        ]) style="width: {{ $ratio }}%"></div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-card>

                <x-card title="Navigasi">
                    <div class="space-y-2">
                        <a href="{{ route('pharmacy.prescriptions.index') }}" class="nav-link w-full">
                            <x-icon name="arrow-left" class="h-4 w-4" />
                            Semua Resep
                        </a>
                        <a href="{{ route('patients.show', $prescription->patient) }}" class="nav-link w-full">
                            <x-icon name="users" class="h-4 w-4" />
                            Profil Pasien
                        </a>
                        @if ($prescription->medicalRecord)
                            <a href="{{ route('medical-records.show', $prescription->medicalRecord) }}" class="nav-link w-full">
                                <x-icon name="stethoscope" class="h-4 w-4" />
                                Rekam Medis
                            </a>
                        @endif
                    </div>
                </x-card>
            </div>
        </div>
    </div>
@endsection
