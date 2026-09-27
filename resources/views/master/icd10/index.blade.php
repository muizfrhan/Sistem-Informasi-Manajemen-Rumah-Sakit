@extends('layouts.app')

@section('title', 'ICD-10')
@section('heading', 'Kode ICD-10')
@section('subheading', $codes->total().' kode diagnosis')

@section('content')
    <x-card padding="p-0">
        <x-slot:title>Daftar Kode Diagnosis ICD-10</x-slot:title>
        <x-slot:subtitle>Referensi diagnosis internasional (WHO ICD-10)</x-slot:subtitle>

        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4">
            <div class="min-w-56 flex-1"><x-input name="q" :value="request('q')" placeholder="Cari kode atau deskripsi..." /></div>
            <div class="w-44"><x-select name="category" :value="request('category')" :options="$categories->all()" placeholder="Semua kategori" /></div>
            <x-button type="submit" variant="secondary">Cari</x-button>
            @if (request()->hasAny(['q', 'category']))
                <a href="{{ route('master.icd10.index') }}" class="pb-2 text-xs text-slate-500 hover:text-slate-700">Reset</a>
            @endif
        </form>

        @if ($canManage)
            <form method="POST" action="{{ route('master.icd10.store') }}" x-data="{ open: false }" class="border-b border-slate-200 px-5 py-4">
                @csrf
                <button type="button" @click="open = !open" class="text-sm font-semibold text-teal-700 hover:underline">
                    <span x-text="open ? 'Batal' : '+ Tambah Kode ICD-10'"></span>
                </button>
                <div x-show="open" x-collapse class="mt-4 grid grid-cols-1 gap-4 rounded-lg bg-slate-50 p-4 sm:grid-cols-4">
                    <x-input name="code" label="Kode" required maxlength="10" placeholder="J00" />
                    <div class="sm:col-span-2"><x-input name="description" label="Deskripsi" required maxlength="255" placeholder="Acute nasopharyngitis" /></div>
                    <x-input name="category" label="Kategori" maxlength="10" placeholder="X" />
                    <div class="flex items-end gap-3 sm:col-span-4">
                        <x-checkbox name="is_active" label="Aktif" :value="true" />
                        <x-button type="submit" size="sm">Simpan Kode</x-button>
                    </div>
                </div>
            </form>
        @endif

        @if ($codes->isEmpty())
            <x-empty-state title="Kode ICD-10 tidak ditemukan" message="Coba kata kunci lain." />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="w-32 px-5 py-3 font-medium">Kode</th>
                            <th class="px-5 py-3 font-medium">Deskripsi</th>
                            <th class="w-24 px-5 py-3 font-medium">Kategori</th>
                            <th class="w-24 px-5 py-3 font-medium">Status</th>
                            @if ($canManage)<th class="w-32 px-5 py-3 text-right font-medium">Aksi</th>@endif
                        </tr>
                    </thead>

                    @foreach ($codes as $icd)
                        <tbody x-data="{ editing: false }" class="border-t border-slate-100 hover:bg-slate-50">
                            <tr>
                                <td class="whitespace-nowrap px-5 py-3 font-mono text-xs font-semibold text-teal-700">{{ $icd->code }}</td>
                                <td class="px-5 py-3 text-slate-700">{{ $icd->description }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $icd->category ?? '-' }}</td>
                                <td class="px-5 py-3"><x-badge :variant="$icd->is_active ? 'green' : 'red'">{{ $icd->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                                @if ($canManage)
                                    <td class="whitespace-nowrap px-5 py-3 text-right">
                                        <button type="button" @click="editing = !editing" class="text-xs font-medium text-teal-700 hover:underline">Edit</button>
                                        <span class="mx-1 text-slate-300">|</span>
                                        <x-delete-button :action="route('master.icd10.destroy', $icd)" method="DELETE"
                                                        :confirm="'Hapus kode ICD-10 {$icd->code}?'" label="Hapus" />
                                    </td>
                                @endif
                            </tr>
                            @if ($canManage)
                                <tr x-show="editing" x-collapse class="border-t border-dashed border-slate-200 bg-slate-50">
                                    <td colspan="5" class="px-5 py-3">
                                        <form method="POST" action="{{ route('master.icd10.update', $icd) }}" class="flex flex-wrap items-end gap-3">
                                            @csrf @method('PUT')
                                            <div class="w-28"><x-input name="code" :value="$icd->code" required maxlength="10" /></div>
                                            <div class="min-w-64 flex-1"><x-input name="description" :value="$icd->description" required maxlength="255" /></div>
                                            <div class="w-24"><x-input name="category" :value="$icd->category" maxlength="10" /></div>
                                            <x-checkbox name="is_active" label="Aktif" :value="$icd->is_active" />
                                            <x-button type="submit" size="sm">Simpan</x-button>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    @endforeach
                </table>
            </div>

            @if ($codes->hasPages())<div class="border-t border-slate-200 px-5 py-3">{{ $codes->links() }}</div>@endif
        @endif
    </x-card>
@endsection
