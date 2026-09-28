@extends('layouts.app')

@section('title', 'Laporan')
@section('heading', 'Laporan & Analitik')
@section('subheading', $range['from']->translatedFormat('d M Y') . ' - ' . $range['to']->translatedFormat('d M Y'))

@section('content')
    <div class="space-y-5">

        {{-- ===================== Filter periode ===================== --}}
        <x-card padding="p-4">
            <form method="GET" action="{{ route('reports.index') }}"
                  class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="min-w-0 flex-1">
                    <label for="range" class="field-label">Periode</label>
                    <select id="range" name="range" class="field-control" onchange="this.form.submit()">
                        @foreach ([
                            'week' => 'Minggu Ini',
                            'month' => 'Bulan Ini',
                            'quarter' => '3 Bulan Terakhir',
                            'year' => 'Tahun Ini',
                            'custom' => 'Rentang Kustom',
                        ] as $value => $label)
                            <option value="{{ $value }}" @selected(request('range', 'month') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="from" class="field-label">Dari</label>
                    <input id="from" name="from" type="date" value="{{ request('from', $range['from']->toDateString()) }}"
                           class="field-control">
                </div>

                <div>
                    <label for="to" class="field-label">Sampai</label>
                    <input id="to" name="to" type="date" value="{{ request('to', $range['to']->toDateString()) }}"
                           class="field-control">
                </div>

                <input type="hidden" name="range" value="custom">
                <x-button type="submit" variant="secondary" icon="funnel">Terapkan</x-button>
            </form>
        </x-card>

        {{-- ===================== Kunjungan ===================== --}}
        <x-card title="Kunjungan" :subtitle="'Periode ' . $range['days'] . ' hari'">
            <x-slot:actions>
                <x-badge variant="outline">{{ $visits['total'] }} total</x-badge>
            </x-slot:actions>

            <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
                @foreach ([
                    ['Total Kunjungan', $visits['total'], 'clipboard', 'brand'],
                    ['Rawat Jalan', $visits['outpatient'], 'users', 'info'],
                    ['Rawat Inap', $visits['inpatient'], 'bed', 'slate'],
                    ['Gawat Darurat', $visits['emergency'], 'heart', 'danger'],
                    ['Selesai', $visits['completed'], 'check-circle', 'success'],
                ] as $tile)
                    <x-stat-card :label="$tile[0]" :value="$tile[1]" unit="kali" :icon="$tile[2]" :tone="$tile[3]" />
                @endforeach
            </div>

            <div class="mt-5 border-t border-line pt-5">
                <x-bar-chart :data="$trend->toArray()" :max="max(1, (int) $trend->max('count'))"
                             :highlight-last="false" value-label="kunjungan" />
            </div>
        </x-card>

        {{-- ===================== Pendapatan ===================== --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-card title="Pendapatan" subtitle="Berdasarkan tagihan yang terbit pada periode ini">
                <dl class="space-y-3">
                    @foreach ([
                        ['Total Diterbitkan', $revenue['invoiced'], 'receipt', 'ink'],
                        ['Total Diterima', $revenue['collected'], 'wallet', 'success-ink'],
                        ['Belum Dibayar', $revenue['outstanding'], 'clock', 'danger-ink'],
                    ] as $row)
                        <div class="flex items-center justify-between gap-3 border-b border-line pb-3 last:border-0 last:pb-0">
                            <dt class="flex items-center gap-2 text-sm text-ink-2">
                                <x-icon :name="$row[2]" class="h-4 w-4 text-ink-3" />
                                {{ $row[0] }}
                            </dt>
                            <dd @class(['text-sm font-bold tabular-nums', 'text-'.$row[3]])>
                                Rp {{ number_format($row[1], 0, ',', '.') }}
                            </dd>
                        </div>
                    @endforeach
                </dl>

                <div class="mt-4 rounded-lg bg-surface-2 px-4 py-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-ink-3">Jumlah tagihan</span>
                        <span class="font-semibold text-ink tabular-nums">{{ $revenue['invoice_count'] }}</span>
                    </div>
                    @if ($revenue['invoiced'] > 0)
                        <div class="mt-2 flex items-center justify-between text-xs">
                            <span class="text-ink-3">Tingkat pelunasan</span>
                            <span class="font-semibold text-success-ink">
                                {{ round($revenue['collected'] / $revenue['invoiced'] * 100) }}%
                            </span>
                        </div>
                    @endif
                </div>
            </x-card>

            <x-card title="Rekam Medis & Farmasi">
                <dl class="space-y-3">
                    @foreach ([
                        ['Rekam Medis Dibuat', $medical['records'], 'stethoscope'],
                        ['Sudah Difinalisasi', $medical['finalized'], 'check-circle'],
                        ['Draft Tersisa', $medical['draft'], 'pencil'],
                        ['Resep Diterbitkan', $medical['prescriptions'], 'pill'],
                        ['Resep Diserahkan', $medical['dispensed'], 'inbox'],
                    ] as $row)
                        <div class="flex items-center justify-between gap-3 border-b border-line pb-3 last:border-0 last:pb-0">
                            <dt class="flex items-center gap-2 text-sm text-ink-2">
                                <x-icon :name="$row[2]" class="h-4 w-4 text-ink-3" />
                                {{ $row[0] }}
                            </dt>
                            <dd class="text-sm font-bold text-ink tabular-nums">{{ $row[1] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-card>
        </div>

        {{-- ===================== Kunjungan per poli ===================== --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-card title="Kunjungan per Poli" subtitle="10 poli dengan kunjungan terbanyak">
                @php $polyMax = max(1, (int) $byPoly->max('total')); @endphp

                @if ($byPoly->isEmpty())
                    <x-empty-state compact icon="building" title="Belum ada poli" />
                @else
                    <ul class="space-y-3">
                        @foreach ($byPoly as $poly)
                            <li>
                                <div class="mb-1 flex items-center justify-between gap-3 text-xs">
                                    <span class="truncate text-ink-2">{{ $poly['name'] }}</span>
                                    <span class="font-bold text-ink tabular-nums">{{ $poly['total'] }}</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-surface-3">
                                    <div class="h-full rounded-full bg-brand"
                                         style="width: {{ round($poly['total'] / $polyMax * 100) }}%"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>

            <x-card title="Distribusi Status Kunjungan">
                @if ($byStatus->isEmpty())
                    <x-empty-state compact icon="ticket" title="Belum ada kunjungan pada periode ini" />
                @else
                    <ul class="space-y-2.5">
                        @foreach ($byStatus as $row)
                            <li class="flex items-center justify-between gap-3">
                                <x-badge :class="$row['status']->badgeClass()">{{ $row['label'] }}</x-badge>
                                <span class="text-sm font-bold text-ink tabular-nums">{{ $row['count'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>
        </div>

        {{-- ===================== Diagnosis & obat ===================== --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-card title="Diagnosis Teratas (ICD-10)" subtitle="Berdasarkan kode diagnosis pada periode ini">
                @if ($topDiagnoses->isEmpty())
                    <x-empty-state compact icon="stethoscope" title="Belum ada diagnosis"
                                   message="Belum ada kode ICD-10 yang tercatat pada periode ini." />
                @else
                    <div class="table-wrap">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-line">
                                    <th scope="col" class="pb-2 pr-4 text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Kode</th>
                                    <th scope="col" class="pb-2 pr-4 text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Diagnosis</th>
                                    <th scope="col" class="pb-2 text-right text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Kasus</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topDiagnoses as $row)
                                    <tr class="border-b border-line last:border-0">
                                        <td class="py-2 pr-4 font-mono text-xs font-bold text-brand">{{ $row['code'] }}</td>
                                        <td class="py-2 pr-4 text-xs text-ink-2">{{ $row['description'] }}</td>
                                        <td class="py-2 text-right text-sm font-bold text-ink tabular-nums">{{ $row['count'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>

            <x-card title="Obat Paling Sering Diresepkan" subtitle="Berdasarkan jumlah pada periode ini">
                @if ($topDrugs->isEmpty())
                    <x-empty-state compact icon="pill" title="Belum ada resep"
                                   message="Belum ada resep pada periode ini." />
                @else
                    <div class="table-wrap">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-line">
                                    <th scope="col" class="pb-2 pr-4 text-[11px] font-semibold tracking-wide text-ink-3 uppercase">#</th>
                                    <th scope="col" class="pb-2 pr-4 text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Obat</th>
                                    <th scope="col" class="pb-2 text-right text-[11px] font-semibold tracking-wide text-ink-3 uppercase">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topDrugs as $index => $row)
                                    <tr class="border-b border-line last:border-0">
                                        <td class="py-2 pr-4 text-xs text-ink-3">{{ $index + 1 }}</td>
                                        <td class="py-2 pr-4 text-xs font-medium text-ink">{{ $row['name'] }}</td>
                                        <td class="py-2 text-right text-sm font-bold text-ink tabular-nums">
                                            {{ number_format($row['quantity'], 0, ',', '.') }} {{ $row['unit'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        </div>

        {{-- ===================== Sarana & pasien ===================== --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <x-card title="Rawat Inap">
                <dl class="space-y-2.5 text-sm">
                    @foreach ([
                        ['Total kamar', $inpatient['rooms_total']],
                        ['Kapasitas tempat tidur', $inpatient['capacity']],
                        ['Sedang terisi', $inpatient['occupied']],
                        ['Tersedia', $inpatient['available']],
                        ['Dalam pemeliharaan', $inpatient['rooms_maintenance']],
                    ] as $row)
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-ink-2">{{ $row[0] }}</dt>
                            <dd class="font-bold text-ink tabular-nums">{{ $row[1] }}</dd>
                        </div>
                    @endforeach
                </dl>

                <div class="mt-3">
                    <div class="mb-1 flex items-center justify-between text-xs">
                        <span class="text-ink-3">Tingkat hunian</span>
                        <span class="font-bold text-ink">{{ $inpatient['occupancy_rate'] }}%</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-surface-3">
                        <div class="h-full rounded-full bg-brand" style="width: {{ $inpatient['occupancy_rate'] }}%"></div>
                    </div>
                </div>
            </x-card>

            <x-card title="Persediaan Obat">
                <dl class="space-y-2.5 text-sm">
                    @foreach ([
                        ['Jenis obat aktif', $inventory['items']],
                        ['Nilai persediaan', 'Rp '.number_format($inventory['value'], 0, ',', '.')],
                        ['Stok menipis', $inventory['low']],
                        ['Mendekati kedaluwarsa', $inventory['expiring']],
                    ] as $row)
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-ink-2">{{ $row[0] }}</dt>
                            <dd class="font-bold text-ink tabular-nums">{{ $row[1] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-card>

            <x-card title="Data Pasien">
                <dl class="space-y-2.5 text-sm">
                    @foreach ([
                        ['Pasien baru pada periode ini', $patients['new']],
                        ['Total pasien aktif', $patients['active']],
                        ['Total kunjungan', $visits['total']],
                    ] as $row)
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-ink-2">{{ $row[0] }}</dt>
                            <dd class="font-bold text-ink tabular-nums">{{ $row[1] }}</dd>
                        </div>
                    @endforeach
                </dl>

                <div class="mt-4 rounded-lg bg-surface-2 px-4 py-3 text-xs text-ink-2">
                    <p class="font-semibold text-ink">Catatan</p>
                    <p class="mt-1 leading-relaxed">
                        Seluruh angka pada halaman ini dihitung langsung dari data operasional.
                        Nilai kosong berarti belum ada transaksi pada periode terpilih.
                    </p>
                </div>
            </x-card>
        </div>
    </div>
@endsection
