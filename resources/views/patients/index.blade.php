@extends('layouts.app')

@section('title', 'Data Pasien')
@section('heading', 'Data Pasien')
@section('subheading', $patients->total().' pasien terdaftar')

@section('content')
    <x-card padding="p-0">
        <x-slot:title>Kelola Data Pasien</x-slot:title>
        <x-slot:subtitle>Cari berdasarkan No. RM, nama, NIK, atau nomor BPJS</x-slot:subtitle>
        <x-slot:actions>
            @if (auth()->user()->hasPermission(\App\Enums\Permission::MANAGE_PATIENT))
                <x-button :href="route('patients.create')" size="sm">+ Pasien Baru</x-button>
            @endif
        </x-slot:actions>

        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4">
            <div class="min-w-56 flex-1">
                <x-input name="q" :value="$search" placeholder="Cari pasien..." class="bg-white" />
            </div>
            <div class="w-44">
                <x-select name="insurance_type" :value="request('insurance_type')" :options="collect($insuranceTypes)->mapWithKeys(fn ($i) => [$i->value => $i->label()])->all()" placeholder="Semua jaminan" />
            </div>
            <div class="w-40">
                <x-select name="status" :value="request('status')" :options="['active' => 'Aktif', 'inactive' => 'Nonaktif']" placeholder="Semua status" />
            </div>
            <x-button type="submit" variant="secondary" size="md">Filter</x-button>
            @if (request()->hasAny(['q', 'insurance_type', 'status']))
                <a href="{{ route('patients.index') }}" class="pb-2 text-xs text-slate-500 hover:text-slate-700">Reset</a>
            @endif
        </form>

        @if ($patients->isEmpty())
            <x-empty-state title="Tidak ada pasien ditemukan"
                           message="Coba ubah kata kunci pencarian atau daftarkan pasien baru.">
                @if (auth()->user()->hasPermission(\App\Enums\Permission::MANAGE_PATIENT))
                    <x-button :href="route('patients.create')">+ Daftarkan Pasien</x-button>
                @endif
            </x-empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">No. RM</th>
                            <th class="px-5 py-3 font-medium">Nama Pasien</th>
                            <th class="px-5 py-3 font-medium">NIK</th>
                            <th class="px-5 py-3 font-medium">JK / Umur</th>
                            <th class="px-5 py-3 font-medium">Jaminan</th>
                            <th class="px-5 py-3 text-center font-medium">Kunjungan</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($patients as $patient)
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-3">
                                    <a href="{{ route('patients.show', $patient) }}" class="font-mono text-sm font-semibold text-teal-700 hover:underline">{{ $patient->mr_number }}</a>
                                </td>
                                <td class="px-5 py-3">
                                    <a href="{{ route('patients.show', $patient) }}" class="font-medium text-slate-800 hover:underline">{{ $patient->name }}</a>
                                    @if ($patient->phone)
                                        <p class="text-xs text-slate-500">{{ $patient->phone }}</p>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-slate-600">{{ $patient->masked_nik }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-slate-600">{{ $patient->gender->value }} / {{ $patient->age }} th</td>
                                <td class="px-5 py-3">
                                    <x-badge :variant="match ($patient->insurance_type) { \App\Enums\InsuranceType::BPJS => 'teal', \App\Enums\InsuranceType::NONE => 'slate', default => 'purple' }">
                                        {{ $patient->insurance_type->label() }}
                                    </x-badge>
                                </td>
                                <td class="px-5 py-3 text-center text-slate-600">{{ $patient->registrations_count }}</td>
                                <td class="px-5 py-3">
                                    <x-badge :variant="$patient->is_active ? 'green' : 'red'">{{ $patient->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <a href="{{ route('patients.show', $patient) }}" class="text-xs font-medium text-teal-700 hover:underline">Detail</a>
                                    @if (auth()->user()->hasPermission(\App\Enums\Permission::MANAGE_PATIENT))
                                        <span class="mx-1 text-slate-300">|</span>
                                        <a href="{{ route('patients.edit', $patient) }}" class="text-xs font-medium text-slate-600 hover:underline">Edit</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($patients->hasPages())
                <div class="border-t border-slate-200 px-5 py-3">{{ $patients->links() }}</div>
            @endif
        @endif
    </x-card>
@endsection
