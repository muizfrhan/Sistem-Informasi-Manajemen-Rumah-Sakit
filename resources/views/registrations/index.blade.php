@extends('layouts.app')

@section('title', 'Pendaftaran')
@section('heading', 'Pendaftaran')
@section('subheading', $registrations->total().' data pendaftaran')

@section('content')
    <x-card padding="p-0">
        <x-slot:title>Daftar Pendaftaran Pasien</x-slot:title>
        <x-slot:subtitle>Riwayat pendaftaran seluruh poli</x-slot:subtitle>
        <x-slot:actions>
            @if (auth()->user()->hasPermission(\App\Enums\Permission::MANAGE_REGISTRATION))
                <x-button :href="route('registrations.create')" size="sm">+ Pendaftaran Baru</x-button>
            @endif
        </x-slot:actions>

        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4">
            <div class="w-52">
                <x-input name="date" type="date" :value="request('date')" :max="now()->format('Y-m-d')" />
            </div>
            <div class="w-56">
                <x-select name="poly_id" :value="request('poly_id')"
                          :options="$polies->pluck('name', 'id')->all()" placeholder="Semua poli" />
            </div>
            <div class="w-52">
                <x-select name="status" :value="request('status')"
                          :options="collect($statuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all()"
                          placeholder="Semua status" />
            </div>
            <x-button type="submit" variant="secondary">Filter</x-button>
            @if (request()->hasAny(['date', 'poly_id', 'status']))
                <a href="{{ route('registrations.index') }}" class="pb-2 text-xs text-slate-500 hover:text-slate-700">Reset</a>
            @endif
        </form>

        @if ($registrations->isEmpty())
            <x-empty-state title="Belum ada pendaftaran"
                           message="Belum ada data yang sesuai filter. Coba ubah filter atau buat pendaftaran baru." />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">No. Registrasi</th>
                            <th class="px-5 py-3 font-medium">Tanggal</th>
                            <th class="px-5 py-3 font-medium">Antrean</th>
                            <th class="px-5 py-3 font-medium">Pasien</th>
                            <th class="px-5 py-3 font-medium">Poli / Dokter</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($registrations as $registration)
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-3 font-mono text-xs font-semibold text-teal-700">
                                    <a href="{{ route('registrations.show', $registration) }}" class="hover:underline">{{ $registration->registration_number }}</a>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-slate-600">{{ $registration->visit_date->translatedFormat('d M Y') }}</td>
                                <td class="whitespace-nowrap px-5 py-3">
                                    <span class="font-mono font-semibold text-slate-800">{{ $registration->queue_code }}</span>
                                    @if ($registration->is_emergency)
                                        <x-badge variant="red" class="ml-1">UGD</x-badge>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <a href="{{ route('patients.show', $registration->patient) }}" class="font-medium text-slate-800 hover:underline">{{ $registration->patient->name }}</a>
                                    <p class="font-mono text-xs text-slate-500">{{ $registration->patient->mr_number }}</p>
                                </td>
                                <td class="px-5 py-3">
                                    <p class="text-slate-800">{{ $registration->poly->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $registration->doctor?->name ?? '-' }}</p>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3"><x-badge :class="$registration->status->badgeClass()">{{ $registration->status->label() }}</x-badge></td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <a href="{{ route('registrations.show', $registration) }}" class="text-xs font-medium text-teal-700 hover:underline">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($registrations->hasPages())
                <div class="border-t border-slate-200 px-5 py-3">{{ $registrations->links() }}</div>
            @endif
        @endif
    </x-card>
@endsection
