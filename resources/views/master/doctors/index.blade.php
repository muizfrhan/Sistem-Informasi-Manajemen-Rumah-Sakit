@extends('layouts.app')

@section('title', 'Data Dokter')
@section('heading', 'Data Dokter')
@section('subheading', $doctors->total().' dokter terdaftar')

@section('content')
    <x-card padding="p-0">
        <x-slot:title>Daftar Dokter</x-slot:title>
        <x-slot:subtitle>Dokter yang dapat ditugaskan menerima pasien</x-slot:subtitle>
        <x-slot:actions>
            @if ($canManage)<x-button :href="route('master.doctors.create')" size="sm">+ Tambah Dokter</x-button>@endif
        </x-slot:actions>

        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4">
            <div class="min-w-56 flex-1"><x-input name="q" :value="request('q')" placeholder="Cari nama dokter..." /></div>
            <div class="w-56"><x-select name="poly_id" :value="request('poly_id')" :options="$polies->pluck('name', 'id')->all()" placeholder="Semua poli" /></div>
            <x-button type="submit" variant="secondary">Filter</x-button>
            @if (request()->hasAny(['q', 'poly_id']))
                <a href="{{ route('master.doctors.index') }}" class="pb-2 text-xs text-slate-500 hover:text-slate-700">Reset</a>
            @endif
        </form>

        @if ($doctors->isEmpty())
            <x-empty-state title="Dokter tidak ditemukan" message="Coba ubah filter atau tambahkan dokter baru." />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">Kode</th>
                            <th class="px-5 py-3 font-medium">Nama Dokter</th>
                            <th class="px-5 py-3 font-medium">Spesialisasi</th>
                            <th class="px-5 py-3 font-medium">Poli</th>
                            <th class="px-5 py-3 font-medium">NIP</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            @if ($canManage)<th class="px-5 py-3 text-right font-medium">Aksi</th>@endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($doctors as $doctor)
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-3 font-mono text-xs font-semibold text-slate-700">{{ $doctor->code }}</td>
                                <td class="px-5 py-3 font-medium text-slate-800">{{ $doctor->name }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $doctor->specialty ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $doctor->poly->name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-slate-500">{{ $doctor->nip ?? '-' }}</td>
                                <td class="px-5 py-3"><x-badge :variant="$doctor->is_active ? 'green' : 'red'">{{ $doctor->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                                @if ($canManage)
                                    <td class="whitespace-nowrap px-5 py-3 text-right">
                                        <a href="{{ route('master.doctors.edit', $doctor) }}" class="text-xs font-medium text-teal-700 hover:underline">Edit</a>
                                        <span class="mx-1 text-slate-300">|</span>
                                        <x-delete-button :action="route('master.doctors.destroy', $doctor)" method="DELETE"
                                                        :confirm="'Hapus data dokter {$doctor->name}?'" label="Hapus" />
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($doctors->hasPages())<div class="border-t border-slate-200 px-5 py-3">{{ $doctors->links() }}</div>@endif
        @endif
    </x-card>
@endsection
