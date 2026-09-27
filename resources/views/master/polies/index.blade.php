@extends('layouts.app')

@section('title', 'Data Poli')
@section('heading', 'Data Poli')
@section('subheading', $polies->total().' poli terdaftar')

@section('content')
    <x-card padding="p-0">
        <x-slot:title>Daftar Poli</x-slot:title>
        <x-slot:subtitle>Poli yang tersedia untuk pendaftaran pasien</x-slot:subtitle>
        <x-slot:actions>
            @if ($canManage)<x-button :href="route('master.polies.create')" size="sm">+ Tambah Poli</x-button>@endif
        </x-slot:actions>

        @if ($polies->isEmpty())
            <x-empty-state title="Belum ada poli" message="Tambahkan poli pertama untuk mulai menerima pendaftaran." />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">Kode</th>
                            <th class="px-5 py-3 font-medium">Nama Poli</th>
                            <th class="px-5 py-3 font-medium">Prefiks Antrean</th>
                            <th class="px-5 py-3 text-center font-medium">Dokter</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            @if ($canManage)<th class="px-5 py-3 text-right font-medium">Aksi</th>@endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($polies as $poly)
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-3 font-mono text-xs font-semibold text-slate-700">{{ $poly->code }}</td>
                                <td class="px-5 py-3">
                                    <p class="font-medium text-slate-800">{{ $poly->name }}</p>
                                    @if ($poly->description)<p class="text-xs text-slate-500">{{ $poly->description }}</p>@endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-3"><span class="font-mono font-bold text-teal-700">{{ $poly->queue_prefix }}</span></td>
                                <td class="px-5 py-3 text-center text-slate-600">{{ $poly->doctors_count }}</td>
                                <td class="px-5 py-3"><x-badge :variant="$poly->is_active ? 'green' : 'red'">{{ $poly->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                                @if ($canManage)
                                    <td class="whitespace-nowrap px-5 py-3 text-right">
                                        <a href="{{ route('master.polies.edit', $poly) }}" class="text-xs font-medium text-teal-700 hover:underline">Edit</a>
                                        <span class="mx-1 text-slate-300">|</span>
                                        <x-delete-button :action="route('master.polies.destroy', $poly)" method="DELETE"
                                                        :confirm="'Hapus poli {$poly->name}?'" label="Hapus" />
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($polies->hasPages())<div class="border-t border-slate-200 px-5 py-3">{{ $polies->links() }}</div>@endif
        @endif
    </x-card>
@endsection
