@extends('layouts.app')

@section('title', 'Data Obat')
@section('heading', 'Data Obat')
@section('subheading', $drugs->total().' obat terdaftar')

@section('content')
    <x-card padding="p-0">
        <x-slot:title>Daftar Obat</x-slot:title>
        <x-slot:subtitle>
            Master data obat untuk resep dan pengurangan stok
            @if ($lowStockCount > 0)
                &middot; <span class="font-semibold text-amber-600">{{ $lowStockCount }} obat menipis</span>
            @endif
        </x-slot:subtitle>

        <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4">
            <div class="min-w-56 flex-1"><x-input name="q" :value="request('q')" placeholder="Cari nama atau kode obat..." /></div>
            <label class="flex items-center gap-2 pb-2 text-sm text-slate-700">
                <input type="checkbox" name="low_stock" value="1" @checked(request()->boolean('low_stock'))
                       class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                Stok menipis
            </label>
            <x-button type="submit" variant="secondary">Filter</x-button>
            @if (request()->hasAny(['q', 'low_stock']))
                <a href="{{ route('master.drugs.index') }}" class="pb-2 text-xs text-slate-500 hover:text-slate-700">Reset</a>
            @endif
        </form>

        @if ($canManage)
            <form method="POST" action="{{ route('master.drugs.store') }}" x-data="{ open: false }" class="border-b border-slate-200 px-5 py-4">
                @csrf
                <button type="button" @click="open = !open" class="text-sm font-semibold text-teal-700 hover:underline">
                    <span x-text="open ? 'Batal' : '+ Tambah Obat'"></span>
                </button>
                <div x-show="open" x-collapse class="mt-4 grid grid-cols-1 gap-4 rounded-lg bg-slate-50 p-4 sm:grid-cols-3">
                    <x-input name="code" label="Kode" required maxlength="30" placeholder="OB016" />
                    <div class="sm:col-span-2"><x-input name="name" label="Nama Obat" required maxlength="150" placeholder="Amoxicillin" /></div>
                    <x-input name="generic_name" label="Nama Generik" maxlength="150" />
                    <x-input name="form" label="Sediaan" maxlength="50" placeholder="Tablet" />
                    <x-input name="strength" label="Kekuatan" maxlength="30" placeholder="500 mg" />
                    <x-input name="unit" label="Satuan" :value="'Tablet'" required maxlength="30" />
                    <x-input name="price" label="Harga (Rp)" type="number" required min="0" step="1" placeholder="8500" />
                    <x-input name="stock" label="Stok" type="number" required min="0" step="1" value="0" />
                    <x-input name="min_stock" label="Stok Minimum" type="number" required min="0" step="1" value="10" />
                    <div class="flex items-end gap-3 sm:col-span-3">
                        <x-checkbox name="requires_prescription" label="Wajib Resep" :value="true" />
                        <x-checkbox name="is_active" label="Aktif" :value="true" />
                        <x-button type="submit" size="sm">Simpan Obat</x-button>
                    </div>
                </div>
            </form>
        @endif

        @if ($drugs->isEmpty())
            <x-empty-state title="Obat tidak ditemukan" message="Coba ubah filter pencarian." />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="w-28 px-5 py-3 font-medium">Kode</th>
                            <th class="px-5 py-3 font-medium">Nama Obat</th>
                            <th class="px-5 py-3 font-medium">Sediaan</th>
                            <th class="px-5 py-3 text-right font-medium">Harga</th>
                            <th class="px-5 py-3 text-center font-medium">Stok</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            @if ($canManage)<th class="w-28 px-5 py-3 text-right font-medium">Aksi</th>@endif
                        </tr>
                    </thead>

                    @foreach ($drugs as $drug)
                        <tbody x-data="{ editing: false }" class="border-t border-slate-100 hover:bg-slate-50">
                            <tr>
                                <td class="whitespace-nowrap px-5 py-3 font-mono text-xs font-semibold text-slate-700">{{ $drug->code }}</td>
                                <td class="px-5 py-3">
                                    <p class="font-medium text-slate-800">{{ $drug->name }}</p>
                                    @if ($drug->generic_name)<p class="text-xs text-slate-500">{{ $drug->generic_name }}</p>@endif
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ collect([$drug->strength, $drug->form])->filter()->join(' ') ?: '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right font-medium text-slate-800">{{ number_format($drug->price, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-center">
                                    <x-badge :variant="$drug->isLowStock() ? 'red' : 'green'">{{ number_format($drug->stock) }}</x-badge>
                                </td>
                                <td class="px-5 py-3">
                                    <x-badge :variant="$drug->is_active ? 'green' : 'red'">{{ $drug->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                                    @if (! $drug->requires_prescription)
                                        <x-badge variant="slate" class="ml-1">Bebas</x-badge>
                                    @endif
                                </td>
                                @if ($canManage)
                                    <td class="whitespace-nowrap px-5 py-3 text-right">
                                        <button type="button" @click="editing = !editing" class="text-xs font-medium text-teal-700 hover:underline">Edit</button>
                                        <span class="mx-1 text-slate-300">|</span>
                                        <x-delete-button :action="route('master.drugs.destroy', $drug)" method="DELETE"
                                                        :confirm="'Hapus obat {$drug->name}?'" label="Hapus" />
                                    </td>
                                @endif
                            </tr>
                            @if ($canManage)
                                <tr x-show="editing" x-collapse class="border-t border-dashed border-slate-200 bg-slate-50">
                                    <td colspan="{{ $canManage ? 7 : 6 }}" class="px-5 py-3">
                                        <form method="POST" action="{{ route('master.drugs.update', $drug) }}" class="flex flex-wrap items-end gap-3">
                                            @csrf @method('PUT')
                                            <div class="w-24"><x-input name="code" :value="$drug->code" required maxlength="30" /></div>
                                            <div class="min-w-48 flex-1"><x-input name="name" :value="$drug->name" required maxlength="150" /></div>
                                            <div class="min-w-40"><x-input name="generic_name" :value="$drug->generic_name" maxlength="150" /></div>
                                            <div class="w-24"><x-input name="form" :value="$drug->form" maxlength="50" /></div>
                                            <div class="w-24"><x-input name="strength" :value="$drug->strength" maxlength="30" /></div>
                                            <div class="w-24"><x-input name="unit" :value="$drug->unit" required maxlength="30" /></div>
                                            <div class="w-28"><x-input name="price" type="number" :value="$drug->price" required min="0" step="1" /></div>
                                            <div class="w-24"><x-input name="stock" type="number" :value="$drug->stock" required min="0" step="1" /></div>
                                            <div class="w-28"><x-input name="min_stock" type="number" :value="$drug->min_stock" required min="0" step="1" /></div>
                                            <x-checkbox name="requires_prescription" label="Wajib Resep" :value="$drug->requires_prescription" />
                                            <x-checkbox name="is_active" label="Aktif" :value="$drug->is_active" />
                                            <x-button type="submit" size="sm">Simpan</x-button>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    @endforeach
                </table>
            </div>

            @if ($drugs->hasPages())<div class="border-t border-slate-200 px-5 py-3">{{ $drugs->links() }}</div>@endif
        @endif
    </x-card>
@endsection
