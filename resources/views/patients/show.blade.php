@extends('layouts.app')

@section('title', $patient->name)
@section('heading', $patient->name)
@section('subheading', 'No. Rekam Medis ' . $patient->mr_number)

@section('content')
    <div class="space-y-5">

        {{-- ===================== Header profil ===================== --}}
        <x-card>
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start">
                {{-- Avatar + identitas ringkas --}}
                <div class="flex items-start gap-4 lg:w-72">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand-soft text-lg font-bold text-brand-ink">
                        {{ \Illuminate\Support\Str::of($patient->name)->substr(0, 1)->upper() }}
                    </span>

                    <div class="min-w-0">
                        <h2 class="truncate text-base font-bold text-ink">{{ $patient->name }}</h2>
                        <p class="mt-0.5 font-mono text-xs text-ink-3">{{ $patient->mr_number }}</p>
                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                            <x-active-badge :active="$patient->is_active" />
                            <x-badge :variant="$patient->insurance_type->value === 'NONE' ? 'slate' : 'info'">
                                {{ $patient->insurance_type->label() }}
                            </x-badge>
                        </div>
                    </div>
                </div>

                {{-- Ringkasan angka --}}
                <div class="grid flex-1 grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach ([
                        ['Kunjungan', $patient->registrations_count, 'clipboard', 'registrations.index', 'registration.view'],
                        ['Rekam Medis', $patient->medical_records_count, 'stethoscope', 'medical-records.index', 'emr.view'],
                        ['Tagihan', $patient->invoices_count, 'receipt', 'billing.index', 'billing.view'],
                        ['Rawat Inap', $patient->admissions_count, 'bed', 'inpatient.index', 'inpatient.view'],
                    ] as $tile)
                        @if (auth()->user()->can($tile[4]))
                            <a href="{{ route($tile[3], ['patient_id' => $patient->id]) }}"
                               class="rounded-xl border border-line p-3 transition hover:border-line-2 hover:bg-surface-2">
                                <span class="flex items-center gap-1.5 text-[11px] font-semibold tracking-wide text-ink-3 uppercase">
                                    <x-icon :name="$tile[2]" class="h-3.5 w-3.5" />
                                    {{ $tile[0] }}
                                </span>
                                <span class="mt-1 block text-xl font-bold text-ink tabular-nums">{{ $tile[1] }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Aksi --}}
            <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-line pt-4">
                @if ($canRegister)
                    <x-button :href="route('registrations.create', ['patient_id' => $patient->id])" size="sm" icon="clipboard">
                        Daftarkan ke Poli
                    </x-button>
                @endif

                @if (auth()->user()->can('patient.manage'))
                    <x-button :href="route('patients.edit', $patient)" size="sm" variant="secondary" icon="pencil">
                        Ubah Data
                    </x-button>

                    <form method="POST" action="{{ route('patients.toggle', $patient) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <x-button type="submit" size="sm" variant="ghost" icon="refresh"
                                  x-on:click.prevent="$dispatch('confirm', {
                                      action: $el.form.action,
                                      method: 'PATCH',
                                      title: '{{ $patient->is_active ? 'Nonaktifkan' : 'Aktifkan' }} pasien?',
                                      message: 'Status pasien {{ $patient->name }} akan menjadi {{ $patient->is_active ? 'nonaktif' : 'aktif' }}. Riwayat medis tidak terpengaruh.',
                                  })">
                            {{ $patient->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </x-button>
                    </form>
                @endif

                <a href="{{ route('patients.index') }}" class="btn btn-sm btn-ghost ml-auto">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Kembali ke daftar
                </a>
            </div>
        </x-card>

        <div x-data="{ tab: 'profile' }" class="grid grid-cols-1 gap-5 lg:grid-cols-4">

            {{-- ===================== Navigasi tab ===================== --}}
            <nav class="lg:col-span-1" aria-label="Section profil pasien">
                <div class="card p-2 lg:sticky lg:top-20">
                    @php
                        $tabs = [
                            'profile' => ['Informasi Dasar', 'user', $patient->is_active],
                            'visits' => ['Riwayat Kunjungan', 'clipboard', $patient->registrations->isNotEmpty()],
                            'emr' => ['Rekam Medis', 'stethoscope', $patient->medicalRecords->isNotEmpty()],
                            'prescriptions' => ['Resep', 'pill', $patient->prescriptions->isNotEmpty()],
                            'billing' => ['Riwayat Pembayaran', 'receipt', $patient->invoices->isNotEmpty()],
                            'inpatient' => ['Rawat Inap', 'bed', $patient->admissions->isNotEmpty()],
                        ];
                    @endphp

                    <ul class="space-y-0.5">
                        @foreach ($tabs as $key => $tab)
                            <li>
                                <button type="button"
                                        @class([
                                            'nav-link w-full text-left',
                                            'nav-link-active' => $key === 'profile',
                                        ])
                                        x-bind:class="tab === '{{ $key }}' && 'nav-link-active'"
                                        x-on:click="tab = '{{ $key }}'"
                                        x-bind:aria-current="tab === '{{ $key }}' ? 'page' : false">
                                    <x-icon :name="$tab[1]" class="h-[18px] w-[18px] shrink-0" />
                                    <span class="flex-1 truncate">{{ $tab[0] }}</span>
                                    @if ($tab[2])
                                        <span class="text-[10px] font-bold text-ink-3 tabular-nums" aria-hidden="true">&#9679;</span>
                                    @endif
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </nav>

            {{-- ===================== Isi tab ===================== --}}
            <div class="space-y-4 lg:col-span-3">

                {{-- ---------- Informasi Dasar ---------- --}}
                <div x-show="tab === 'profile'" x-cloak>
                    <x-card title="Informasi Dasar">
                        <dl class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
                            @foreach ([
                                ['Nomor Rekam Medis', $patient->mr_number, true],
                                ['NIK', $patient->masked_nik, true],
                                ['Jenis Kelamin', $patient->gender->label()],
                                ['Usia', $patient->age_years],
                                ['Tanggal Lahir', $patient->birth_date?->translatedFormat('d F Y')],
                                ['Tempat Lahir', $patient->birth_place],
                                ['Golongan Darah', $patient->blood_type],
                                ['Status Kawin', $patient->marital_status],
                                ['Nomor Telepon', $patient->phone],
                                ['Jenis Asuransi', $patient->insurance_type->label()],
                                ['Nomor Asuransi', $patient->insurance_number],
                                ['Nama Penjamin', $patient->insurance_name],
                                ['Tanggal Daftar', $patient->created_at?->translatedFormat('d F Y H:i')],
                                ['Didaftarkan Oleh', $patient->creator?->name],
                            ] as $field)
                                @php [$label, $value, $mono] = [$field[0], $field[1], $field[2] ?? false]; @endphp
                                <div>
                                    <dt class="text-xs text-ink-3">{{ $label }}</dt>
                                    <dd @class([
                                        'mt-0.5 font-medium text-ink',
                                        'font-mono text-sm' => $mono,
                                    ])>
                                        {{ $value ?: '-' }}
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </x-card>

                    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <x-card title="Alamat">
                            <address class="text-sm leading-relaxed text-ink-2 not-italic">
                                @if ($patient->address)
                                    {{ $patient->address }}<br>
                                @endif
                                @if ($patient->village || $patient->district)
                                    {{ collect([$patient->village, $patient->district])->filter()->join(', ') }}<br>
                                @endif
                                @if ($patient->city || $patient->province)
                                    {{ collect([$patient->city, $patient->province])->filter()->join(', ') }}
                                    @if ($patient->postal_code) {{ $patient->postal_code }} @endif
                                @endif

                                @unless ($patient->address || $patient->city || $patient->village)
                                    <span class="text-ink-3">Alamat belum dilengkapi.</span>
                                @endunless
                            </address>
                        </x-card>

                        <x-card title="Kontak Darurat">
                            @if ($patient->emergency_contact_name || $patient->emergency_contact_phone)
                                <dl class="space-y-3 text-sm">
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-ink-3">Nama</dt>
                                        <dd class="text-right font-medium text-ink">{{ $patient->emergency_contact_name ?: '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt class="text-ink-3">Nomor Telepon</dt>
                                        <dd class="text-right font-medium text-ink">
                                            @if ($patient->emergency_contact_phone)
                                                <a href="tel:{{ $patient->emergency_contact_phone }}" class="text-brand hover:underline">
                                                    {{ $patient->emergency_contact_phone }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                            @else
                                <p class="text-sm text-ink-3">Kontak darurat belum diisi.</p>
                            @endif
                        </x-card>
                    </div>
                </div>

                {{-- ---------- Riwayat Kunjungan ---------- --}}
                <div x-show="tab === 'visits'" x-cloak>
                    <x-card title="Riwayat Kunjungan" :subtitle="'20 kunjungan terakhir'" padding="p-0">
                        <x-data-table :cols="$patient->registrations->count()" min-width="44rem"
                                      empty="Belum ada kunjungan"
                                      empty-message="Pasien ini belum pernah mendaftar di poli."
                                      empty-icon="clipboard">
                            <x-slot:emptyAction>
                                <x-button permission="registration.manage"
                                          :href="route('registrations.create', ['patient_id' => $patient->id])"
                                          size="sm" icon="plus">
                                    Daftarkan Sekarang
                                </x-button>
                            </x-slot:emptyAction>

                            <x-slot:head>
                                <tr>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">Antrean</th>
                                    <th scope="col">Poli</th>
                                    <th scope="col" class="hidden sm:table-cell">Dokter</th>
                                    <th scope="col" class="hidden lg:table-cell">Keluhan</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </x-slot:head>

                            <x-slot:body>
                                @foreach ($patient->registrations as $registration)
                                    <tr>
                                        <td data-label="Tanggal">
                                            <span class="font-medium text-ink">
                                                {{ $registration->visit_date->translatedFormat('d M Y') }}
                                            </span>
                                        </td>
                                        <td data-label="Antrean">
                                            <a href="{{ route('registrations.show', $registration) }}"
                                               class="font-mono text-xs font-bold text-brand hover:underline">
                                                {{ $registration->queue_code }}
                                            </a>
                                        </td>
                                        <td data-label="Poli">{{ $registration->poly->name }}</td>
                                        <td data-label="Dokter" class="hidden sm:table-cell">
                                            {{ $registration->doctor?->name ?? '-' }}
                                        </td>
                                        <td data-label="Keluhan" class="hidden lg:table-cell">
                                            <span class="line-clamp-1 text-xs text-ink-2">
                                                {{ $registration->complaint ?: '-' }}
                                            </span>
                                        </td>
                                        <td data-label="Status">
                                            <x-badge :class="$registration->status->badgeClass()">
                                                {{ $registration->status->label() }}
                                            </x-badge>
                                        </td>
                                    </tr>
                                @endforeach
                            </x-slot:body>
                        </x-data-table>
                    </x-card>
                </div>

                {{-- ---------- Rekam Medis (timeline) ---------- --}}
                <div x-show="tab === 'emr'" x-cloak>
                    <x-card title="Timeline Rekam Medis"
                            :subtitle="'20 rekam medis terakhir'"
                            padding="p-0">
                        <x-slot:actions>
                            <x-button permission="emr.manage"
                                      :href="route('medical-records.create', ['patient_id' => $patient->id])"
                                      size="sm" variant="secondary" icon="plus">
                                Rekam Medis Baru
                            </x-button>
                        </x-slot:actions>

                        @if ($patient->medicalRecords->isEmpty())
                            <x-empty-state title="Belum ada rekam medis"
                                           message="Rekam medis dibuat oleh dokter saat pemeriksaan pasien."
                                           icon="stethoscope" />
                        @else
                            <ol class="divide-y divide-line">
                                @foreach ($patient->medicalRecords as $record)
                                    <li class="p-5">
                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <a href="{{ route('medical-records.show', $record) }}"
                                                       class="text-sm font-bold text-brand hover:underline">
                                                        {{ $record->record_type->label() }}
                                                    </a>
                                                    <x-badge :class="$record->status->badgeClass()">
                                                        {{ $record->status->label() }}
                                                    </x-badge>
                                                    @if ($record->finalized_at)
                                                        <span class="text-xs text-ink-3">
                                                            {{ $record->finalized_at->translatedFormat('d M Y H:i') }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <p class="mt-1 text-sm text-ink-2">
                                                    <span class="text-ink-3">Dokter:</span>
                                                    {{ $record->doctor?->name ?? 'Tidak diisi' }}
                                                </p>
                                            </div>

                                            <a href="{{ route('medical-records.show', $record) }}"
                                               class="btn btn-sm btn-ghost shrink-0">Detail</a>
                                        </div>

                                        @if ($record->chief_complaint)
                                            <p class="mt-3 text-sm text-ink">
                                                <span class="text-xs font-semibold tracking-wide text-ink-3 uppercase">Keluhan</span><br>
                                                {{ $record->chief_complaint }}
                                            </p>
                                        @endif

                                        @if ($record->diagnosis)
                                            <p class="mt-2 text-sm text-ink">
                                                <span class="text-xs font-semibold tracking-wide text-ink-3 uppercase">Diagnosis</span><br>
                                                {{ $record->diagnosis }}
                                            </p>
                                        @endif

                                        @if ($record->diagnoses->isNotEmpty())
                                            <div class="mt-3 flex flex-wrap gap-1.5">
                                                @foreach ($record->diagnoses as $diagnosis)
                                                    <x-badge :class="$diagnosis->diagnosis_type->badgeClass()"
                                                             title="{{ $diagnosis->description }}">
                                                        {{ $diagnosis->icd10Code?->code }} &middot; {{ $diagnosis->diagnosis_type->label() }}
                                                    </x-badge>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if ($record->vitals_summary)
                                            <p class="mt-3 rounded-lg bg-surface-2 px-3 py-2 text-xs text-ink-2">
                                                <span class="font-semibold">Tanda vital:</span> {{ $record->vitals_summary }}
                                            </p>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </x-card>
                </div>

                {{-- ---------- Resep ---------- --}}
                <div x-show="tab === 'prescriptions'" x-cloak>
                    <x-card title="Riwayat Resep" :subtitle="$patient->prescriptions->count().' resep'" padding="p-0">
                        @if ($patient->prescriptions->isEmpty())
                            <x-empty-state title="Belum ada resep"
                                           message="Resep dibuat dokter sebagai bagian dari rekam medis."
                                           icon="pill" />
                        @else
                            <ul class="divide-y divide-line">
                                @foreach ($patient->prescriptions as $prescription)
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

                                        <p class="mt-1 text-xs text-ink-3">
                                            {{ $prescription->created_at->translatedFormat('d F Y H:i') }}
                                            &middot; {{ $prescription->doctor?->name ?? 'Tanpa dokter' }}
                                        </p>

                                        <ul class="mt-2.5 space-y-1">
                                            @foreach ($prescription->items as $item)
                                                <li class="flex flex-wrap items-baseline justify-between gap-2 text-sm">
                                                    <span class="text-ink">
                                                        {{ $item->drug?->display_name ?? 'Obat dihapus' }}
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

                {{-- ---------- Riwayat Pembayaran ---------- --}}
                <div x-show="tab === 'billing'" x-cloak>
                    <x-card title="Riwayat Pembayaran" padding="p-0">
                        @if ($patient->invoices->isEmpty())
                            <x-empty-state title="Belum ada tagihan"
                                           message="Tagihan dibuat dari rekam medis yang telah difinalisasi."
                                           icon="receipt" />
                        @else
                            <x-data-table :cols="$patient->invoices->count()" min-width="40rem">
                                <x-slot:head>
                                    <tr>
                                        <th scope="col">No. Tagihan</th>
                                        <th scope="col">Tanggal</th>
                                        <th scope="col">Total</th>
                                        <th scope="col" class="hidden sm:table-cell">Sisa</th>
                                        <th scope="col">Status</th>
                                    </tr>
                                </x-slot:head>

                                <x-slot:body>
                                    @foreach ($patient->invoices as $invoice)
                                        <tr>
                                            <td data-label="No. Tagihan">
                                                <a href="{{ route('billing.show', $invoice) }}"
                                                   class="font-mono text-xs font-bold text-brand hover:underline">
                                                    {{ $invoice->invoice_number }}
                                                </a>
                                            </td>
                                            <td data-label="Tanggal">{{ $invoice->issued_at?->translatedFormat('d M Y') ?? '-' }}</td>
                                            <td data-label="Total" class="font-semibold tabular-nums">
                                                Rp {{ number_format($invoice->total, 0, ',', '.') }}
                                            </td>
                                            <td data-label="Sisa" class="hidden sm:table-cell tabular-nums">
                                                Rp {{ number_format($invoice->outstanding, 0, ',', '.') }}
                                            </td>
                                            <td data-label="Status">
                                                <x-badge :class="$invoice->status->badgeClass()">
                                                    {{ $invoice->status->label() }}
                                                </x-badge>
                                            </td>
                                        </tr>
                                    @endforeach
                                </x-slot:body>
                            </x-data-table>
                        @endif
                    </x-card>
                </div>

                {{-- ---------- Rawat Inap ---------- --}}
                <div x-show="tab === 'inpatient'" x-cloak>
                    <x-card title="Riwayat Rawat Inap" padding="p-0">
                        @if ($patient->admissions->isEmpty())
                            <x-empty-state title="Belum ada riwayat rawat inap"
                                           message="Pasien ini belum pernah dirawat inap."
                                           icon="bed" />
                        @else
                            <x-data-table :cols="$patient->admissions->count()" min-width="42rem">
                                <x-slot:head>
                                    <tr>
                                        <th scope="col">No. Rawat</th>
                                        <th scope="col">Masuk</th>
                                        <th scope="col">Kamar</th>
                                        <th scope="col" class="hidden sm:table-cell">Keluar</th>
                                        <th scope="col">Status</th>
                                    </tr>
                                </x-slot:head>

                                <x-slot:body>
                                    @foreach ($patient->admissions as $admission)
                                        <tr>
                                            <td data-label="No. Rawat">
                                                <span class="font-mono text-xs font-bold text-ink">
                                                    {{ $admission->admission_number }}
                                                </span>
                                            </td>
                                            <td data-label="Masuk">{{ $admission->admitted_at->translatedFormat('d M Y') }}</td>
                                            <td data-label="Kamar">
                                                {{ $admission->room->code }}
                                                <span class="block text-xs text-ink-3">Bed {{ $admission->bed_number }}</span>
                                            </td>
                                            <td data-label="Keluar" class="hidden sm:table-cell">
                                                {{ $admission->discharged_at?->translatedFormat('d M Y') ?? '-' }}
                                            </td>
                                            <td data-label="Status">
                                                <x-badge :class="$admission->status->badgeClass()">
                                                    {{ $admission->status->label() }}
                                                </x-badge>
                                            </td>
                                        </tr>
                                    @endforeach
                                </x-slot:body>
                            </x-data-table>
                        @endif
                    </x-card>
                </div>
            </div>
        </div>
    </div>
@endsection
