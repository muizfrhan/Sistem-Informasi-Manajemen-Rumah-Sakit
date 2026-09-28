@extends('layouts.app')

@section('title', 'Papan Antrean')
@section('heading', 'Papan Antrean')
@section('subheading', $selectedPoly ? $selectedPoly->name : 'Semua Poli')

@section('content')
    <div class="space-y-5">

        <x-page-header title="Papan Antrean"
                       description="Kelola urutan panggilan pasien per poli. Status diperbarui secara langsung.">
            <x-slot:actions>
                <x-button permission="registration.manage" :href="route('registrations.create')" size="sm" icon="plus">
                    Pendaftaran
                </x-button>
            </x-slot:actions>
        </x-page-header>

        @if ($polies->isEmpty())
            {{-- Tidak ada poli sama sekali: jelaskan, jangan tampilkan angka nol membingungkan. --}}
            <x-card>
                <x-empty-state icon="building"
                               title="Belum ada poli terdaftar"
                               message="Papan antrean memerlukan minimal satu poli yang aktif. Tambahkan poli terlebih dahulu.">
                    <x-button permission="master.manage" :href="route('master.polies.create')" size="sm" icon="plus">
                        Tambah Poli
                    </x-button>
                </x-empty-state>
            </x-card>
        @else
            {{-- ===================== Pemilihan poli ===================== --}}
            <div class="scroll-slim -mx-1 flex gap-2 overflow-x-auto px-1 pb-1">
                <a href="{{ route('queue.index') }}"
                   @class([
                       'shrink-0 rounded-lg border px-3.5 py-2 text-sm font-semibold whitespace-nowrap transition',
                       'border-brand bg-brand text-brand-fg' => ! $selectedPoly,
                       'border-line bg-surface text-ink-2 hover:border-line-2 hover:bg-surface-2' => $selectedPoly,
                   ])>
                    Semua Poli
                </a>

                @foreach ($polies as $poly)
                    <a href="{{ route('queue.index', ['poly_id' => $poly->id]) }}"
                       @class([
                           'shrink-0 rounded-lg border px-3.5 py-2 text-sm font-semibold whitespace-nowrap transition',
                           'border-brand bg-brand text-brand-fg' => $selectedPoly?->id === $poly->id,
                           'border-line bg-surface text-ink-2 hover:border-line-2 hover:bg-surface-2' => $selectedPoly?->id !== $poly->id,
                       ])
                       @if ($selectedPoly?->id === $poly->id) aria-current="page" @endif>
                        {{ $poly->name }}
                    </a>
                @endforeach
            </div>

            {{-- ===================== Statistik ===================== --}}
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <x-stat-card label="Menunggu Panggilan" :value="$waiting" unit="pasien" tone="warning" icon="clock" compact />
                <x-stat-card label="Dipanggil" :value="$called" unit="pasien" tone="info" icon="bell" compact />
                <x-stat-card label="Dalam Pemeriksaan" :value="$inExamination" unit="pasien" tone="brand" icon="stethoscope" compact />
                <x-stat-card label="Selesai" :value="$done" unit="pasien" tone="success" icon="check-circle" compact />
            </div>

            {{-- ===================== Panel.display ===================== --}}
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-4">
                    <x-card padding="p-0"
                            :title="'Antrean ' . ($selectedPoly?->name ?? 'Semua Poli')"
                            :subtitle="$registrations->count() . ' pasien dalam antrean aktif'">
                        <x-slot:actions>
                            @if ($selectedPoly && auth()->user()->can('queue.manage'))
                                <form method="POST" action="{{ route('queue.call', $selectedPoly) }}">
                                    @csrf
                                    <x-button type="submit" size="sm" icon="bell"
                                              x-on:click.prevent="$dispatch('confirm', {
                                                  action: $el.form.action,
                                                  method: 'POST',
                                                  title: 'Panggil pasien berikutnya?',
                                                  message: 'Pasien berikutnya dalam antrean {{ $selectedPoly->name }} akan dipanggil. Pastikan petugas kehadiran sudah siap.',
                                                  confirmLabel: 'Panggil',
                                              })">
                                        Panggil Berikutnya
                                    </x-button>
                                </form>
                            @endif
                        </x-slot:actions>

                        <x-data-table :cols="$registrations->count()" min-width="52rem"
                                      empty="Antrean kosong"
                                      empty-message="Belum ada pasien yang menunggu di poli ini hari ini."
                                      empty-icon="ticket">
                            <x-slot:emptyAction>
                                <x-button permission="registration.manage" :href="route('registrations.create')" size="sm" icon="plus">
                                    Buat Pendaftaran
                                </x-button>
                            </x-slot:emptyAction>

                            <x-slot:head>
                                <tr>
                                    <th scope="col">Antrean</th>
                                    <th scope="col">Pasien</th>
                                    <th scope="col" class="hidden sm:table-cell">Poli</th>
                                    <th scope="col" class="hidden lg:table-cell">Keluhan</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-right">Aksi</th>
                                </tr>
                            </x-slot:head>

                            <x-slot:body>
                                @foreach ($registrations as $registration)
                                    <tr @class(['bg-danger-soft/30' => $registration->is_emergency])>
                                        <td data-label="Antrean">
                                            <a href="{{ route('registrations.show', $registration) }}"
                                               class="font-mono text-sm font-bold text-brand hover:underline">
                                                {{ $registration->queue_code }}
                                            </a>
                                            @if ($registration->is_emergency)
                                                <x-badge variant="danger" class="ml-1" title="Gawat darurat">UGD</x-badge>
                                            @endif
                                        </td>

                                        <td data-label="Pasien">
                                            <a href="{{ route('patients.show', $registration->patient) }}"
                                               class="font-semibold text-ink hover:underline">
                                                {{ $registration->patient->name }}
                                            </a>
                                            <span class="block font-mono text-xs text-ink-3">
                                                {{ $registration->patient->mr_number }} &middot; {{ $registration->patient->age }} th
                                            </span>
                                        </td>

                                        <td data-label="Poli" class="hidden sm:table-cell">
                                            {{ $registration->poly->name }}
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

                                        <td data-label="Aksi">
                                            @if (auth()->user()->can('queue.manage'))
                                                <div class="flex flex-wrap items-center justify-end gap-1.5">
                                                    @foreach ($registration->status->allowedTransitions() as $target)
                                                        @php
                                                            // Konfirmasi hanya untuk status akhir/batal.
                                                            // Transisi maju (Dipanggil, Dalam Pemeriksaan) cukup sekali klik.
                                                            // Payload lewat prop `confirm` supaya tidak ada kondisi Blade
                                                            // di dalam daftar atribut <x-button>.
                                                            $needsConfirm = in_array($target, [
                                                                \App\Enums\RegistrationStatus::DONE,
                                                                \App\Enums\RegistrationStatus::CANCELLED,
                                                                \App\Enums\RegistrationStatus::NO_SHOW,
                                                            ], true);

                                                            $advanceConfirm = $needsConfirm ? [
                                                                'action' => route('queue.advance', [$registration, $target->value]),
                                                                'method' => 'PATCH',
                                                                'title' => 'Ubah status menjadi '.$target->label().'?',
                                                                'message' => 'Antrean '.$registration->queue_code.' atas nama '.$registration->patient->name
                                                                    .' akan berstatus '.$target->label().'. Status ini tidak dapat dibatalkan.',
                                                            ] : null;
                                                        @endphp

                                                        <form method="POST" class="inline"
                                                              action="{{ route('queue.advance', [$registration, $target->value]) }}">
                                                            @csrf
                                                            @method('PATCH')

                                                            <x-button type="submit" size="sm"
                                                                      :variant="$target === \App\Enums\RegistrationStatus::DONE ? 'primary' : 'secondary'"
                                                                      :confirm="$advanceConfirm">
                                                                {{ $target->label() }}
                                                            </x-button>
                                                        </form>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-xs text-ink-3">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </x-slot:body>
                        </x-data-table>
                    </x-card>
                </div>

                {{-- ===================== Panel samping ===================== --}}
                <div class="space-y-4">
                    <div class="rounded-card bg-ink p-5 text-canvas shadow-e2">
                        <p class="text-[11px] font-semibold tracking-wider uppercase opacity-70">Sedang Diperiksa</p>
                        @if ($nowServing)
                            <p class="mt-2 font-mono text-4xl leading-none font-bold tabular-nums">
                                {{ $nowServing->queue_code }}
                            </p>
                            <p class="mt-2 text-sm font-semibold">{{ $nowServing->patient->name }}</p>
                            <p class="mt-0.5 text-xs opacity-70">
                                {{ $nowServing->doctor?->name ?? 'Tanpa dokter' }}
                                @if ($nowServing->examined_at)
                                    &middot; sejak {{ $nowServing->examined_at->format('H:i') }}
                                @endif
                            </p>
                        @else
                            <p class="mt-2 text-3xl font-bold opacity-30">--</p>
                            <p class="mt-1 text-sm opacity-60">Tidak ada pasien dalam pemeriksaan</p>
                        @endif
                    </div>

                    <div class="rounded-card border border-line bg-surface p-5 shadow-e1">
                        <p class="text-[11px] font-semibold tracking-wider text-ink-3 uppercase">Berikutnya</p>
                        @if ($nextUp)
                            <p class="mt-2 font-mono text-4xl leading-none font-bold text-brand tabular-nums">
                                {{ $nextUp->queue_code }}
                            </p>
                            <p class="mt-2 text-sm font-semibold text-ink">{{ $nextUp->patient->name }}</p>
                            <p class="mt-0.5 text-xs text-ink-3">{{ $nextUp->poly->name }}</p>
                        @else
                            <p class="mt-2 text-3xl font-bold text-ink-3/40">--</p>
                            <p class="mt-1 text-sm text-ink-3">Tidak ada pasien menunggu</p>
                        @endif
                    </div>

                    <x-card title="Distribusi Status" padding="p-4">
                        @if ($registrations->isEmpty())
                            <p class="py-2 text-center text-xs text-ink-3">Belum ada data.</p>
                        @else
                            <ul class="space-y-2">
                                @foreach ($statuses as $status)
                                    <li class="flex items-center justify-between gap-2 text-xs">
                                        <x-badge :class="$status->badgeClass()">{{ $status->label() }}</x-badge>
                                        <span class="font-bold text-ink tabular-nums">{{ $counts->get($status->value, 0) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </x-card>
                </div>
            </div>
        @endif
    </div>
@endsection
