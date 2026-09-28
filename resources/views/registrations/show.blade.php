@extends('layouts.app')

@section('title', 'Detail Kunjungan')
@section('heading', 'Kunjungan ' . $registration->queue_code)
@section('subheading', $registration->patient->name . ' - ' . $registration->poly->name)

@section('content')
    <div class="space-y-5">

        {{-- ===================== Ringkasan ===================== --}}
        <x-card>
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-lg bg-surface-2 px-3 py-1.5 font-mono text-lg font-bold text-ink">
                            {{ $registration->queue_code }}
                        </span>
                        <x-badge :class="$registration->status->badgeClass()" dot>
                            {{ $registration->status->label() }}
                        </x-badge>
                        <x-badge variant="info">{{ $registration->visit_type->label() }}</x-badge>
                        @if ($registration->is_emergency)
                            <x-badge variant="danger">Gawat Darurat</x-badge>
                        @endif
                    </div>

                    <h2 class="mt-3 text-lg font-bold text-ink">{{ $registration->patient->name }}</h2>
                    <p class="mt-0.5 text-sm text-ink-3">
                        <span class="font-mono">{{ $registration->patient->mr_number }}</span>
                        &middot; {{ $registration->patient->gender->label() }}
                        &middot; {{ $registration->patient->age }} th
                        &middot; {{ $registration->visit_date->translatedFormat('l, d F Y') }}
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap gap-2">
                    <x-button :href="route('patients.show', $registration->patient)" variant="secondary" icon="user" size="sm">
                        Profil Pasien
                    </x-button>
                    <x-button permission="emr.manage"
                              :href="route('medical-records.create', ['registration_id' => $registration->id])"
                              size="sm" icon="stethoscope">
                        Rekam Medis
                    </x-button>
                </div>
            </div>

            {{-- Timeline status --}}
            @php
                $timeline = [
                    \App\Enums\RegistrationStatus::REGISTERED,
                    \App\Enums\RegistrationStatus::CALLED,
                    \App\Enums\RegistrationStatus::IN_EXAMINATION,
                    \App\Enums\RegistrationStatus::DONE,
                ];
                $timestamps = [
                    $registration->registered_at,
                    $registration->called_at,
                    $registration->examined_at,
                    $registration->finished_at,
                ];
                $isAbnormal = in_array($registration->status, [
                    \App\Enums\RegistrationStatus::CANCELLED,
                    \App\Enums\RegistrationStatus::NO_SHOW,
                ], true);
                $currentIndex = $isAbnormal ? -1 : array_search($registration->status, $timeline, true);
            @endphp

            <div class="mt-6 border-t border-line pt-5">
                @if ($isAbnormal)
                    <div class="flex items-center gap-3 rounded-lg bg-danger-soft px-4 py-3 text-danger-ink">
                        <x-icon name="x-circle" class="h-5 w-5 shrink-0" />
                        <div>
                            <p class="text-sm font-semibold">Kunjungan {{ $registration->status->label() }}</p>
                            <p class="text-xs opacity-90">
                                Alur pemeriksaan tidak dilanjutkan. Status akhir: {{ $registration->status->label() }}.
                            </p>
                        </div>
                    </div>
                @else
                    <ol class="flex flex-col gap-4 sm:flex-row sm:items-start">
                        @foreach ($timeline as $index => $step)
                            @php
                                $reached = $currentIndex >= $index;
                                $timestamp = $timestamps[$index];
                            @endphp

                            <li class="relative flex flex-1 items-start gap-3">
                                @if ($index < count($timeline) - 1)
                                    <span @class([
                                        'absolute top-3.5 left-3 h-0.5 w-full',
                                        'bg-brand' => $currentIndex > $index,
                                        'bg-line' => $currentIndex <= $index,
                                    ]) aria-hidden="true"></span>
                                @endif

                                <span @class([
                                    'relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full ring-4 ring-surface',
                                    $reached ? 'bg-brand text-brand-fg' : 'bg-surface-3 text-ink-3',
                                ])>
                                    @if ($reached)
                                        <x-icon name="check" class="h-3.5 w-3.5" />
                                    @else
                                        <span class="text-[11px] font-bold">{{ $index + 1 }}</span>
                                    @endif
                                </span>

                                <span class="min-w-0">
                                    <span @class([
                                        'block text-xs font-semibold',
                                        'text-ink' => $reached,
                                        'text-ink-3' => ! $reached,
                                    ])>{{ $step->label() }}</span>
                                    <span class="mt-0.5 block text-[11px] text-ink-3">
                                        {{ $timestamp?->translatedFormat('d M H:i') ?? 'Menunggu' }}
                                    </span>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </x-card>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <div class="space-y-5 lg:col-span-2">
                {{-- ===================== Informasi kunjungan ===================== --}}
                <x-card title="Informasi Kunjungan">
                    <dl class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
                        @foreach ([
                            ['Nomor Pendaftaran', $registration->registration_number, true],
                            ['Nomor Antrean', $registration->queue_code, true],
                            ['Tanggal Kunjungan', $registration->visit_date->translatedFormat('d F Y')],
                            ['Jenis Kunjungan', $registration->visit_type->label()],
                            ['Poli', $registration->poly->name],
                            ['Dokter', $registration->doctor?->name ?? 'Belum ditentukan'],
                            ['Pendaftaran Oleh', $registration->registrar?->name ?? '-'],
                            ['Waktu Daftar', $registration->registered_at?->translatedFormat('d F Y H:i') ?? '-'],
                        ] as $field)
                            @php [$label, $value, $mono] = [$field[0], $field[1], $field[2] ?? false]; @endphp
                            <div>
                                <dt class="text-xs text-ink-3">{{ $label }}</dt>
                                <dd @class(['mt-0.5 font-medium text-ink', 'font-mono text-sm' => $mono])>{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    @if ($registration->complaint)
                        <div class="mt-5 border-t border-line pt-4">
                            <p class="text-xs font-semibold tracking-wide text-ink-3 uppercase">Keluhan Utama</p>
                            <p class="mt-1.5 text-sm leading-relaxed text-ink">{{ $registration->complaint }}</p>
                        </div>
                    @endif
                </x-card>

                {{-- ===================== Rekam medis terkait ===================== --}}
                <x-card title="Rekam Medis Kunjungan Ini" padding="p-0">
                    @if ($registration->medicalRecords->isEmpty())
                        <x-empty-state icon="stethoscope"
                                       title="Belum ada rekam medis"
                                       message="Buat rekam medis untuk mencatat hasil pemeriksaan pasien pada kunjungan ini.">
                            <x-button permission="emr.manage"
                                      :href="route('medical-records.create', ['registration_id' => $registration->id])"
                                      size="sm" icon="plus">
                                Buat Rekam Medis
                            </x-button>
                        </x-empty-state>
                    @else
                        <ul class="divide-y divide-line">
                            @foreach ($registration->medicalRecords as $record)
                                <li class="p-5">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <a href="{{ route('medical-records.show', $record) }}"
                                           class="text-sm font-bold text-brand hover:underline">
                                            {{ $record->record_type->label() }}
                                        </a>
                                        <x-badge :class="$record->status->badgeClass()">{{ $record->status->label() }}</x-badge>
                                    </div>
                                    <p class="mt-1 text-xs text-ink-3">
                                        {{ $record->doctor?->name ?? 'Tanpa dokter' }}
                                        &middot; {{ $record->created_at->translatedFormat('d F Y H:i') }}
                                    </p>
                                    @if ($record->diagnosis)
                                        <p class="mt-2 text-sm text-ink-2">{{ $record->diagnosis }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-card>
            </div>

            {{-- ===================== Panel samping ===================== --}}
            <div class="space-y-4">
                @if (auth()->user()->can('queue.manage') && $registration->status->allowedTransitions() !== [])
                    <x-card title="Ubah Status" subtitle="Status yang dapat dilakukan saat ini">
                        <div class="space-y-2">
                            @foreach ($registration->status->allowedTransitions() as $target)
                                @php
                                    $needsConfirm = in_array($target, [
                                        \App\Enums\RegistrationStatus::DONE,
                                        \App\Enums\RegistrationStatus::CANCELLED,
                                        \App\Enums\RegistrationStatus::NO_SHOW,
                                    ], true);

                                    // Payload lewat prop `confirm` supaya tidak ada kondisi Blade
                                    // di dalam daftar atribut <x-button>.
                                    $advanceConfirm = $needsConfirm ? [
                                        'action' => route('queue.advance', [$registration, $target->value]),
                                        'method' => 'PATCH',
                                        'title' => 'Ubah status menjadi '.$target->label().'?',
                                        'message' => 'Status kunjungan '.$registration->queue_code
                                            .' akan menjadi '.$target->label().'. Status ini tidak dapat dibatalkan.',
                                    ] : null;
                                @endphp

                                <form method="POST" action="{{ route('queue.advance', [$registration, $target->value]) }}">
                                    @csrf
                                    @method('PATCH')

                                    <x-button type="submit" class="w-full"
                                              :variant="$target === \App\Enums\RegistrationStatus::DONE ? 'primary' : 'secondary'"
                                              :confirm="$advanceConfirm">
                                        {{ $target->label() }}
                                    </x-button>
                                </form>
                            @endforeach
                        </div>
                    </x-card>
                @endif

                <x-card title="Navigasi">
                    <div class="space-y-2">
                        <a href="{{ route('queue.index', ['poly_id' => $registration->poly_id]) }}"
                           class="nav-link w-full">
                            <x-icon name="ticket" class="h-4 w-4" />
                            Papan Antrean {{ $registration->poly->name }}
                        </a>
                        <a href="{{ route('registrations.index') }}" class="nav-link w-full">
                            <x-icon name="clipboard" class="h-4 w-4" />
                            Semua Pendaftaran
                        </a>
                        <a href="{{ route('patients.show', $registration->patient) }}" class="nav-link w-full">
                            <x-icon name="users" class="h-4 w-4" />
                            Data Pasien
                        </a>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
@endsection
