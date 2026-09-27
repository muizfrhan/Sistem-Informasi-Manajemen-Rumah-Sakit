@extends('layouts.app')

@section('title', 'Permintaan Demo')
@section('heading', 'Permintaan Demo')
@section('subheading', $leads->total().' calon klien dari halaman landing')

@section('content')
    <div class="mb-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Baru', $counts->get('NEW', 0), 'teal', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Sudah dihubungi', $counts->get('CONTACTED', 0), 'blue', 'M3 5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5zM7 9h10M7 13h6'],
            ['Demo dijadwalkan', $counts->get('DEMO_SCHEDULED', 0), 'amber', 'M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z'],
            ['Menjadi klien', $counts->get('WON', 0), 'green', 'M5 13l4 4L19 7'],
        ] as $stat)
            <x-stat-card :label="$stat[0]" :value="$stat[1]" :icon="$stat[3]" :tone="$stat[2]" />
        @endforeach
    </div>

    <x-card padding="p-0">
        <x-slot:title>Daftar Permintaan</x-slot:title>
        <x-slot:subtitle>Data diisi melalui formulir "Jadwalkan Demo" pada halaman landing</x-slot:subtitle>

        <form method="GET" class="grid grid-cols-1 gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4 sm:grid-cols-2 lg:grid-cols-4">
            <div><x-input name="q" :value="$search" placeholder="Nama / instansi / email..." /></div>
            <div>
                <x-select name="status" :value="$status" :options="collect(['NEW', 'CONTACTED', 'DEMO_SCHEDULED', 'WON', 'LOST'])->mapWithKeys(fn ($s) => [$s => ucfirst(strtolower(str_replace('_', ' ', $s)))])->all()" placeholder="Semua status" />
            </div>
            <div>
                <x-select name="facility_type" :value="$facilityType" :options="['HOSPITAL' => 'Rumah Sakit', 'CLINIC' => 'Klinik', 'PRIVATE_PRACTICE' => 'Praktik Mandiri', 'OTHER' => 'Lainnya']" placeholder="Semua jenis" />
            </div>
            <div class="flex items-center gap-2">
                <x-button type="submit" variant="secondary">Filter</x-button>
                @if (request()->hasAny(['q', 'status', 'facility_type']))
                    <a href="{{ route('admin.demo-requests.index') }}" class="text-xs text-slate-500 hover:text-slate-700">Reset</a>
                @endif
            </div>
        </form>

        @if ($leads->isEmpty())
            <x-empty-state title="Belum ada permintaan demo"
                           message="Permintaan akan muncul di sini setelah calon klien mengisi formulir pada halaman landing."
                           icon="M3 8l7.4 6.4a2 2 0 002.5 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">Tanggal</th>
                            <th class="px-5 py-3 font-medium">Calon Klien</th>
                            <th class="px-5 py-3 font-medium">Fasilitas</th>
                            <th class="px-5 py-3 font-medium">Kontak</th>
                            <th class="px-5 py-3 font-medium">Kebutuhan</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($leads as $lead)
                            <tr class="align-top hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-3 text-xs text-slate-500">{{ $lead->created_at?->translatedFormat('d M Y H:i') }}</td>
                                <td class="px-5 py-3">
                                    <p class="font-medium text-slate-800">{{ $lead->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $lead->position ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-3">
                                    <p class="text-slate-800">{{ $lead->institution }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ $lead->facilityTypeLabel() }}@if ($lead->bed_count) &middot; {{ $lead->bed_count }} TT @endif
                                    </p>
                                </td>
                                <td class="px-5 py-3 text-xs">
                                    <a href="mailto:{{ $lead->email }}" class="block text-blue-700 hover:underline">{{ $lead->email }}</a>
                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $lead->phone) }}" target="_blank" rel="noopener" class="text-slate-600 hover:underline">{{ $lead->phone }}</a>
                                </td>
                                <td class="max-w-xs px-5 py-3 text-xs text-slate-600">{{ \Illuminate\Support\Str::limit($lead->message, 90) ?: '-' }}</td>
                                <td class="px-5 py-3">
                                    <x-badge :variant="match ($lead->status) { 'NEW' => 'teal', 'CONTACTED' => 'blue', 'DEMO_SCHEDULED' => 'amber', 'WON' => 'green', default => 'slate' }">
                                        {{ $lead->statusLabel() }}
                                    </x-badge>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <div x-data="{ open: false }" class="relative inline-block text-left">
                                        <button type="button" @click="open = !open"
                                                class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                            Kelola
                                        </button>

                                        <div x-show="open" x-transition @click.outside="open = false"
                                             class="absolute right-0 z-20 mt-2 w-72 rounded-xl bg-white p-4 text-left shadow-lg ring-1 ring-slate-200">
                                            <form method="POST" action="{{ route('admin.demo-requests.update', $lead) }}" class="space-y-3">
                                                @csrf
                                                @method('PATCH')

                                                <div>
                                                    <label class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                                                    <select name="status"
                                                            class="block w-full rounded-lg border-0 px-2.5 py-1.5 text-xs ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-teal-600">
                                                        @foreach (['NEW', 'CONTACTED', 'DEMO_SCHEDULED', 'WON', 'LOST'] as $status)
                                                            <option value="{{ $status }}" @selected($lead->status === $status)>
                                                                {{ ucfirst(strtolower(str_replace('_', ' ', $status))) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="mb-1 block text-xs font-medium text-slate-600">Catatan internal</label>
                                                    <textarea name="internal_note" rows="3" class="block w-full rounded-lg border-0 px-2.5 py-1.5 text-xs ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-teal-600">{{ $lead->internal_note }}</textarea>
                                                </div>

                                                <div class="flex items-center justify-between gap-2">
                                                    <x-button type="submit" size="sm">Simpan</x-button>
                                                    <x-button type="submit" size="sm" variant="danger"
                                                             form="delete-lead-{{ $lead->id }}"
                                                             onclick="return confirm('Hapus permintaan ini?')">Hapus</x-button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <form id="delete-lead-{{ $lead->id }}" method="POST" action="{{ route('admin.demo-requests.destroy', $lead) }}" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($leads->hasPages())<div class="border-t border-slate-200 px-5 py-3">{{ $leads->links() }}</div>@endif
        @endif
    </x-card>
@endsection
