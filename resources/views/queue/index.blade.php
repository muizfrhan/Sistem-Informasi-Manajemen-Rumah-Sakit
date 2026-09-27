@extends('layouts.app')

@section('title', 'Antrean')
@section('heading', 'Papan Antrean')
@section('subheading', $selectedPoly ? $selectedPoly->name.' - '.now()->translatedFormat('d F Y') : now()->translatedFormat('d F Y'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center gap-2">
        @foreach ($polies as $poly)
            <a href="{{ route('queue.index', ['poly_id' => $poly->id]) }}"
               @class([
                   'rounded-lg px-3 py-2 text-sm font-medium transition ring-1',
                   'bg-teal-600 text-white ring-teal-600' => $selectedPoly?->id === $poly->id,
                   'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50' => $selectedPoly?->id !== $poly->id,
               ])>
                {{ $poly->name }}
                <span class="ml-1 text-xs opacity-75">({{ $poly->queue_prefix }})</span>
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        <div class="lg:col-span-3">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <x-stat-card label="Menunggu" :value="$waiting" tone="amber" icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                <x-stat-card label="Dipanggil" :value="$called" tone="blue" icon="M15 11a3 3 0 11-6 0 3 3 0 016 0zM19 21a7 7 0 00-14 0"/>
                <x-stat-card label="Diperiksa" :value="$inExamination" tone="indigo" icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                <x-stat-card label="Selesai" :value="$done" tone="green" icon="M9 12l2 2 4-4M7.835 4.617a1.825 1.825 0 00-1.19 2.36L8 9.5m0 0l.5 2.5m0 0l2.5.5m-3 0l2.5 2.5m0-5l2.5-2.5"/>
            </div>

            <x-card class="mt-6" padding="p-0">
                <x-slot:title>Papan Antrean {{ $selectedPoly?->name }}</x-slot:title>
                <x-slot:subtitle>{{ now()->translatedFormat('l, d F Y') }}</x-slot:subtitle>
                <x-slot:actions>
                    @if ($selectedPoly && auth()->user()->hasPermission(\App\Enums\Permission::MANAGE_QUEUE))
                        <form method="POST" action="{{ route('queue.call', $selectedPoly) }}">
                            @csrf
                            <x-button type="submit" size="sm">Panggil Berikutnya</x-button>
                        </form>
                    @endif
                </x-slot:actions>

                @if ($registrations->isEmpty())
                    <x-empty-state title="Antrean kosong"
                                   message="Belum ada pasien yang terdaftar di poli ini hari ini.">
                        @if (auth()->user()->hasPermission(\App\Enums\Permission::MANAGE_REGISTRATION))
                            <x-button :href="route('registrations.create')" size="sm">+ Pendaftaran Baru</x-button>
                        @endif
                    </x-empty-state>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="w-24 px-5 py-3 font-medium">Antrean</th>
                                    <th class="px-5 py-3 font-medium">Pasien</th>
                                    <th class="px-5 py-3 font-medium">Keluhan</th>
                                    <th class="px-5 py-3 font-medium">Status</th>
                                    <th class="px-5 py-3 text-right font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($registrations as $registration)
                                    <tr @class([
                                        'hover:bg-slate-50',
                                        'bg-emerald-50/60' => $registration->status === \App\Enums\RegistrationStatus::IN_EXAMINATION,
                                    ])>
                                        <td class="whitespace-nowrap px-5 py-3">
                                            <span class="font-mono text-base font-bold text-slate-900">{{ $registration->queue_code }}</span>
                                            @if ($registration->is_emergency)
                                                <x-badge variant="red" class="mt-0.5">UGD</x-badge>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3">
                                            <a href="{{ route('patients.show', $registration->patient) }}" class="font-medium text-slate-800 hover:underline">{{ $registration->patient->name }}</a>
                                            <p class="font-mono text-xs text-slate-500">{{ $registration->patient->mr_number }} &middot; {{ $registration->patient->age }} th</p>
                                        </td>
                                        <td class="max-w-xs px-5 py-3 text-slate-600">{{ \Illuminate\Support\Str::limit($registration->complaint ?? '-', 45) }}</td>
                                        <td class="whitespace-nowrap px-5 py-3"><x-badge :class="$registration->status->badgeClass()">{{ $registration->status->label() }}</x-badge></td>
                                        <td class="whitespace-nowrap px-5 py-3 text-right">
                                            @if (auth()->user()->hasPermission(\App\Enums\Permission::MANAGE_QUEUE))
                                                <div class="flex justify-end gap-1.5">
                                                    @if ($registration->status === \App\Enums\RegistrationStatus::REGISTERED)
                                                        <form method="POST" action="{{ route('queue.advance', ['registration' => $registration, 'status' => \App\Enums\RegistrationStatus::QUEUING->value]) }}">
                                                            @csrf @method('PATCH')
                                                            <x-button type="submit" size="sm" variant="secondary">Masuk Antrean</x-button>
                                                        </form>
                                                    @endif
                                                    @foreach ($registration->status->allowedTransitions() as $target)
                                                        @continue($target === \App\Enums\RegistrationStatus::QUEUING)
                                                        <form method="POST" action="{{ route('queue.advance', ['registration' => $registration, 'status' => $target->value]) }}">
                                                            @csrf @method('PATCH')
                                                            <x-button type="submit" size="sm"
                                                      variant="match ($target) { \App\Enums\RegistrationStatus::DONE => 'success', \App\Enums\RegistrationStatus::CALLED => 'primary', \App\Enums\RegistrationStatus::IN_EXAMINATION => 'primary', default => 'secondary' }">
                                                                {{ \Illuminate\Support\Str::headline($target->value) }}
                                                            </x-button>
                                                        </form>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-xs text-slate-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl bg-slate-900 p-5 text-white shadow-lg">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Sedang Dilayani</p>
                @if ($nowServing)
                    <p class="mt-2 font-mono text-4xl font-bold">{{ $nowServing->queue_code }}</p>
                    <p class="mt-1 text-sm text-slate-300">{{ $nowServing->patient->name }}</p>
                    <p class="text-xs text-slate-500">{{ $nowServing->poly->name }}</p>
                @else
                    <p class="mt-2 text-3xl font-bold text-slate-600">--</p>
                    <p class="mt-1 text-sm text-slate-500">Tidak ada pasien dalam pemeriksaan</p>
                @endif
            </div>

            <div class="rounded-xl bg-teal-600 p-5 text-white shadow-lg">
                <p class="text-xs font-medium uppercase tracking-wide text-teal-100">Berikutnya</p>
                @if ($nextUp)
                    <p class="mt-2 font-mono text-4xl font-bold">{{ $nextUp->queue_code }}</p>
                    <p class="mt-1 text-sm text-teal-50">{{ $nextUp->patient->name }}</p>
                    <p class="text-xs text-teal-200">{{ $nextUp->doctor?->name ?? 'Dokter belum ditentukan' }}</p>
                @else
                    <p class="mt-2 text-3xl font-bold text-teal-300">--</p>
                    <p class="mt-1 text-sm text-teal-100">Tidak ada pasien menunggu</p>
                @endif
            </div>

            <x-card title="Distribusi Status">
                <ul class="space-y-2 text-sm">
                    @foreach ([
                        \App\Enums\RegistrationStatus::REGISTERED, \App\Enums\RegistrationStatus::QUEUING,
                        \App\Enums\RegistrationStatus::CALLED, \App\Enums\RegistrationStatus::IN_EXAMINATION,
                        \App\Enums\RegistrationStatus::DONE,
                    ] as $status)
                        <li class="flex items-center justify-between gap-2">
                            <x-badge :class="$status->badgeClass()">{{ $status->label() }}</x-badge>
                            <span class="font-semibold text-slate-800">{{ $registrations->where('status', $status)->count() }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        </div>
    </div>
@endsection
