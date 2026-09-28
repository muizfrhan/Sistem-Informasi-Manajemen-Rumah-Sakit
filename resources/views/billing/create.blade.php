@extends('layouts.app')

@section('title', 'Buat Tagihan')
@section('heading', 'Buat Tagihan')
@section('subheading', 'Pilih rekam medis yang telah difinalisasi')

@section('content')
    <div class="space-y-5">
        <x-page-header title="Buat Tagihan"
                       description="Tagihan disusun otomatis dari tindakan dan obat pada rekam medis yang telah difinalisasi.">
            <x-slot:actions>
                <x-button :href="route('billing.index')" variant="ghost" icon="arrow-left">Kembali</x-button>
            </x-slot:actions>
        </x-page-header>

        @if ($record)
            {{-- ============ Pratinjau tagihan ============ --}}
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <x-card title="Pratinjau Tagihan" :subtitle="$record->patient->name . ' - ' . $record->patient->mr_number"
                            padding="p-0">
                        <x-slot:actions>
                            <a href="{{ route('medical-records.show', $record) }}" class="btn btn-sm btn-ghost">
                                Lihat Rekam Medis
                                <x-icon name="chevron-right" class="h-3.5 w-3.5" />
                            </a>
                        </x-slot:actions>

                        <div class="divide-y divide-line">
                            <div class="p-5">
                                <p class="text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Diagnosis</p>
                                <p class="mt-1 text-sm text-ink">{{ $record->diagnosis ?: 'Tidak diisi' }}</p>
                            </div>

                            <div class="p-5">
                                <p class="text-[11px] font-semibold tracking-wide text-ink-3 uppercase">
                                    Tindakan ({{ $record->procedures->count() }})
                                </p>

                                @if ($record->procedures->isEmpty())
                                    <p class="mt-1 text-sm text-ink-3">Tidak ada tindakan yang dicatat.</p>
                                @else
                                    <ul class="mt-2 space-y-1.5">
                                        @foreach ($record->procedures as $procedure)
                                            <li class="flex items-baseline justify-between gap-3 text-sm">
                                                <span class="text-ink">{{ $procedure->name }}</span>
                                                <span @class([
                                                    'text-xs tabular-nums',
                                                    'font-semibold text-ink' => $procedure->fee > 0,
                                                    'text-ink-3' => $procedure->fee === 0,
                                                ])>
                                                    {{ $procedure->fee > 0 ? 'Rp ' . number_format($procedure->fee, 0, ',', '.') : 'Gratis' }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="p-5">
                                <p class="text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Obat Diresepkan</p>

                                @if ($record->prescriptions->isEmpty())
                                    <p class="mt-1 text-sm text-ink-3">Tidak ada resep pada rekam medis ini.</p>
                                @else
                                    @foreach ($record->prescriptions as $prescription)
                                        <div class="mt-2">
                                            <p class="font-mono text-xs font-semibold text-ink-3">
                                                {{ $prescription->prescription_number }}
                                                <x-badge :class="$prescription->status->badgeClass()" class="ml-1">
                                                    {{ $prescription->status->label() }}
                                                </x-badge>
                                            </p>
                                            <ul class="mt-1.5 space-y-1">
                                                @foreach ($prescription->items as $item)
                                                    <li class="flex items-baseline justify-between gap-3 text-sm">
                                                        <span class="text-ink">
                                                            {{ $item->drug?->display_name ?? 'Obat' }}
                                                            <span class="text-xs text-ink-3">&times; {{ $item->quantity }}</span>
                                                        </span>
                                                        <span class="text-xs font-semibold tabular-nums">
                                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </x-card>
                </div>

                <div class="space-y-4">
                    <x-card title="Konfirmasi Tagihan">
                        <form method="POST" action="{{ route('billing.store') }}" class="space-y-4">
                            @csrf
                            <input type="hidden" name="medical_record_id" value="{{ $record->id }}">

                            <x-input name="consultation_fee" label="Biaya Konsultasi" type="number" min="0" max="100000000"
                                     :value="old('consultation_fee', 0)" prefix="Rp" required
                                     hint="Masukkan 0 bila tidak ada biaya konsultasi." />

                            <x-button type="submit" class="w-full" icon="receipt">Buat Tagihan</x-button>
                        </form>
                    </x-card>

                    <x-card title="Ringkasan Pasien">
                        <dl class="space-y-3 text-sm">
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="text-xs text-ink-3">Nama</dt>
                                <dd class="text-right text-xs font-semibold text-ink">{{ $record->patient->name }}</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="text-xs text-ink-3">Penjamin</dt>
                                <dd class="text-right text-xs font-medium text-ink">
                                    {{ $record->patient->insurance_type->label() }}
                                </dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="text-xs text-ink-3">Dokter</dt>
                                <dd class="text-right text-xs font-medium text-ink">
                                    {{ $record->doctor?->name ?? 'Tidak diisi' }}
                                </dd>
                            </div>
                        </dl>
                    </x-card>
                </div>
            </div>
        @else
            {{-- ============ Pemilihan rekam medis ============ --}}
            <x-card padding="p-0" title="Rekam Medis Siap Ditagih"
                    :subtitle="$records->count() . ' rekam medis menunggu tagihan'">
                <x-data-table :cols="$records->count()" min-width="54rem"
                              empty="Tidak ada rekam medis yang siap ditagih"
                              empty-message="Semua rekam medis yang telah difinalisasi sudah memiliki tagihan."
                              empty-icon="stethoscope">
                    <x-slot:head>
                        <tr>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Pasien</th>
                            <th scope="col" class="hidden sm:table-cell">Dokter</th>
                            <th scope="col" class="hidden lg:table-cell">Rincian</th>
                            <th scope="col" class="text-right">Aksi</th>
                        </tr>
                    </x-slot:head>

                    <x-slot:body>
                        @foreach ($records as $item)
                            <tr>
                                <td data-label="Tanggal">
                                    <span class="font-medium text-ink">
                                        {{ $item->finalized_at?->translatedFormat('d M Y') ?? $item->created_at->translatedFormat('d M Y') }}
                                    </span>
                                </td>

                                <td data-label="Pasien">
                                    <span class="font-semibold text-ink">{{ $item->patient->name }}</span>
                                    <span class="block font-mono text-xs text-ink-3">{{ $item->patient->mr_number }}</span>
                                </td>

                                <td data-label="Dokter" class="hidden sm:table-cell text-xs">
                                    {{ $item->doctor?->name ?? '-' }}
                                </td>

                                <td data-label="Rincian" class="hidden lg:table-cell text-xs text-ink-2">
                                    {{ $item->procedures->count() }} tindakan,
                                    {{ $item->prescriptions->sum(fn ($p) => $p->items->count()) }} item obat
                                </td>

                                <td data-label="Aksi">
                                    <div class="flex justify-end">
                                        <x-button :href="route('billing.create', ['medical_record_id' => $item->id])"
                                                  size="sm" icon="receipt">
                                            Buat Tagihan
                                        </x-button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </x-slot:body>
                </x-data-table>
            </x-card>
        @endif
    </div>
@endsection
