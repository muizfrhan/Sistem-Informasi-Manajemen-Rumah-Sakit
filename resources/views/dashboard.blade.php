@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Selamat datang, ' . $user->name . ' - ' . now()->translatedFormat('l, d F Y'))

@section('content')
    <div class="space-y-6">

        {{-- ===================== Quick actions ===================== --}}
        @if ($quickActions !== [])
            <div class="flex flex-wrap items-center gap-2">
                <span class="mr-1 hidden text-xs font-semibold tracking-wide text-ink-3 uppercase sm:inline">Aksi cepat</span>
                @foreach ($quickActions as $action)
                    <x-button :href="route($action['route'])" size="sm" variant="secondary" :icon="$action['icon']">
                        {{ $action['label'] }}
                    </x-button>
                @endforeach
            </div>
        @endif

        {{-- ===================== Statistik utama ===================== --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4">
            <x-stat-card label="Pasien Terdaftar" :value="number_format($stats['patientsTotal'], 0, ',', '.')"
                         :hint="$stats['patientsToday'].' pasien baru hari ini'"
                         :href="auth()->user()->can(\App\Enums\Permission::VIEW_PATIENT) ? route('patients.index') : null"
                         icon="users" tone="brand" />

            <x-stat-card label="Kunjungan Hari Ini" :value="number_format($stats['registrationsToday'], 0, ',', '.')"
                         :hint="$stats['registrationsActive'].' masih dalam proses'"
                         :href="auth()->user()->can(\App\Enums\Permission::VIEW_REGISTRATION) ? route('registrations.index') : null"
                         icon="clipboard" tone="info" />

            <x-stat-card label="Dokter Aktif" :value="number_format($stats['doctorsActive'], 0, ',', '.')"
                         :hint="$stats['doctorsOnDuty'].' dokter berjadwal hari ini'"
                         :href="auth()->user()->can(\App\Enums\Permission::VIEW_SCHEDULE) ? route('schedules.index') : null"
                         icon="user" tone="success" />

            <x-stat-card label="Poli Aktif" :value="number_format($stats['poliesActive'], 0, ',', '.')"
                         :hint="$perPoly->sum('today_count').' kunjungan tersebar hari ini'"
                         icon="building" tone="slate" />
        </div>

        {{-- ===================== Grafik + Status antrean ===================== --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <x-card class="lg:col-span-2" :title="$chart['label']"
                    subtitle="Jumlah kunjungan per hari berdasarkan tanggal kunjungan">
                <x-slot:actions>
                    @foreach (['week' => '7 Hari', 'month' => '30 Hari', 'quarter' => '90 Hari'] as $value => $label)
                        <a href="{{ route('dashboard', ['range' => $value]) }}"
                           @class([
                               'rounded-lg px-2.5 py-1 text-xs font-semibold transition',
                               'bg-brand text-brand-fg' => $chart['range'] === $value,
                               'text-ink-2 hover:bg-surface-2' => $chart['range'] !== $value,
                           ])
                           @if ($chart['range'] === $value) aria-current="true" @endif>
                            {{ $label }}
                        </a>
                    @endforeach
                </x-slot:actions>

                <x-bar-chart :data="$chart['data']" :max="$chart['max']" :highlight-last="false" />

                <p class="mt-3 border-t border-line pt-3 text-xs text-ink-3">
                    Total <span class="font-semibold text-ink-2 tabular-nums">{{ number_format($chart['total'], 0, ',', '.') }}</span>
                    kunjungan pada rentang terpilih
                    @if ($chart['max'] > 0)
                        &middot; puncak <span class="font-semibold text-ink-2 tabular-nums">{{ number_format($chart['max'], 0, ',', '.') }}</span>/hari
                    @endif
                </p>
            </x-card>

            <x-card title="Status Antrean Hari Ini" subtitle="Distribusi status kunjungan">
                @php $statusTotal = max(1, $stats['registrationsToday']); @endphp

                @if ($stats['registrationsToday'] === 0)
                    <x-empty-state compact icon="ticket" title="Belum ada kunjungan"
                                   message="Antrean hari ini masih kosong." />
                @else
                    <ul class="space-y-3">
                        @foreach ($statuses as $status)
                            @php $count = (int) ($statusCounts[$status->value] ?? 0); @endphp
                            <li>
                                <div class="mb-1 flex items-center justify-between gap-2 text-xs">
                                    <x-badge :class="$status->badgeClass()">{{ $status->label() }}</x-badge>
                                    <span class="font-bold text-ink tabular-nums">{{ $count }}</span>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-surface-3">
                                    <div class="h-full rounded-full bg-brand transition-all"
                                         style="width: {{ round($count / $statusTotal * 100) }}%"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>
        </div>

        {{-- ===================== Modul operasional ===================== --}}
        @if ($moduleStats !== [])
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @isset($moduleStats['emr'])
                    <x-stat-card label="Rekam Medis" :value="number_format($moduleStats['emr']['draft'], 0, ',', '.')"
                                 hint="Draft belum difinalisasi"
                                 :href="auth()->user()->can(\App\Enums\Permission::VIEW_EMR) ? route('medical-records.index') : null"
                                 icon="stethoscope" tone="slate" />
                @endisset

                @isset($moduleStats['pharmacy'])
                    <x-stat-card label="Resep Menunggu"
                                 :value="number_format($moduleStats['pharmacy']['pending'], 0, ',', '.')"
                                 :hint="$moduleStats['pharmacy']['low_stock'].' obat stok menipis'"
                                 :href="auth()->user()->can(\App\Enums\Permission::VIEW_PHARMACY) ? route('pharmacy.prescriptions.index') : null"
                                 icon="pill" tone="warning" />
                @endisset

                @isset($moduleStats['billing'])
                    <x-stat-card label="Belum Lunas"
                                 :value="number_format($moduleStats['billing']['open'], 0, ',', '.')"
                                 :hint="'Sisa tagihan Rp ' . number_format($moduleStats['billing']['outstanding'], 0, ',', '.')"
                                 :href="auth()->user()->can(\App\Enums\Permission::VIEW_BILLING) ? route('billing.index') : null"
                                 icon="receipt" tone="danger" />
                @endisset

                @isset($moduleStats['inpatient'])
                    <x-stat-card label="Kamar Tersedia"
                                 :value="number_format($moduleStats['inpatient']['available'], 0, ',', '.')"
                                 :hint="$moduleStats['inpatient']['occupied'].' dari '.$moduleStats['inpatient']['capacity'].' tempat tidur terisi'"
                                 :href="auth()->user()->can(\App\Enums\Permission::VIEW_INPATIENT) ? route('inpatient.index') : null"
                                 icon="bed" tone="success" />
                @endisset
            </div>
        @endif

        {{-- ===================== Kunjungan terbaru + poli ===================== --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <x-card class="lg:col-span-2" padding="p-0"
                    title="Pendaftaran Terbaru"
                    :subtitle="$recent->count().' antrean terakhir di hari ini'">
                <x-slot:actions>
                    <a href="{{ route('registrations.index') }}" class="btn btn-sm btn-ghost">
                        Lihat semua
                        <x-icon name="chevron-right" class="h-3.5 w-3.5" />
                    </a>
                </x-slot:actions>

                <x-data-table :cols="$recent->count()" min-width="34rem"
                              empty="Belum ada pendaftaran hari ini"
                              empty-message="Daftarkan pasien untuk memulai antrean poli."
                              empty-icon="clipboard">
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
                            <th scope="col">Status</th>
                        </tr>
                    </x-slot:head>

                    <x-slot:body>
                        @foreach ($recent as $registration)
                            <tr>
                                <td data-label="Antrean">
                                    <a href="{{ route('registrations.show', $registration) }}"
                                       class="font-mono text-sm font-bold text-brand hover:underline">
                                        {{ $registration->queue_code }}
                                    </a>
                                    @if ($registration->is_emergency)
                                        <span class="ml-1 inline-flex align-middle text-danger-ink" title="Gawat darurat">
                                            <x-icon name="heart" class="h-3.5 w-3.5" />
                                        </span>
                                    @endif
                                </td>

                                <td data-label="Pasien">
                                    <a href="{{ route('patients.show', $registration->patient) }}"
                                       class="font-medium text-ink hover:underline">
                                        {{ $registration->patient->name }}
                                    </a>
                                    <span class="block font-mono text-xs text-ink-3">{{ $registration->patient->mr_number }}</span>
                                </td>

                                <td data-label="Poli" class="hidden sm:table-cell">
                                    {{ $registration->poly->name }}
                                </td>

                                <td data-label="Status">
                                    <x-badge :class="$registration->status->badgeClass()">
                                        {{ $registration->status->label() }}
                                    </x-badge>
                                </td>
                            </tr>
                        @endforeach
                    </x-slot:body>
                </x-data-table>
            </x-card>

            <div class="space-y-4">
                <x-card title="Kunjungan per Poli" subtitle="Hari ini">
                    @forelse ($perPoly as $poly)
                        <a href="{{ route('queue.index', ['poly_id' => $poly->id]) }}"
                           class="flex items-center justify-between gap-3 rounded-lg px-2 py-2 text-sm transition hover:bg-surface-2">
                            <span class="truncate text-ink-2">{{ $poly->name }}</span>
                            <x-badge :variant="$poly->today_count > 0 ? 'brand' : 'slate'">
                                {{ $poly->today_count }}
                            </x-badge>
                        </a>
                    @empty
                        <x-empty-state compact icon="building" title="Belum ada poli"
                                       message="Tambahkan poli untuk mulai melayani pasien." />
                    @endforelse
                </x-card>

                <x-stat-card label="Pendapatan Diterima"
                             :value="'Rp ' . number_format($moduleStats['billing']['paid_month'] ?? 0, 0, ',', '.')"
                             :hint="'Akumulasi bulan ' . now()->translatedFormat('F Y')"
                             :href="auth()->user()->can(\App\Enums\Permission::VIEW_BILLING) ? route('billing.index') : null"
                             icon="wallet" tone="success" />
            </div>
        </div>

        {{-- ===================== Notifikasi + aktivitas ===================== --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-card title="Perlu Perhatian" subtitle="Dihitung langsung dari data operasional">
                <x-slot:actions>
                    <x-badge :variant="$alerts === [] ? 'success' : 'warning'" dot>
                        {{ count($alerts) }} item
                    </x-badge>
                </x-slot:actions>

                @if ($alerts === [])
                    <x-empty-state compact icon="check-circle" title="Semua kondisi normal"
                                   message="Tidak ada antrean tertahan, stok kritis, atau tagihan bermasalah saat ini." />
                @else
                    <ul class="space-y-2">
                        @foreach ($alerts as $alert)
                            <li>
                                <a href="{{ $alert['route'] ? route($alert['route']) : '#' }}"
                                   class="flex items-start gap-3 rounded-lg p-2.5 transition hover:bg-surface-2">
                                    <x-badge :variant="$alert['tone']" class="mt-0.5 shrink-0">
                                        {{ $alert['count'] }}
                                    </x-badge>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-semibold text-ink">{{ $alert['title'] }}</span>
                                        <span class="mt-0.5 block text-xs leading-relaxed text-ink-3">{{ $alert['detail'] }}</span>
                                    </span>
                                    <x-icon name="chevron-right" class="mt-1 h-4 w-4 shrink-0 text-ink-3" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>

            <x-card title="Aktivitas Terbaru" subtitle="Transaksi dan perubahan data terkini">
                @if ($activity === [])
                    <x-empty-state compact icon="clock" title="Belum ada aktivitas"
                                   message="Riwayat aktivitas akan tampil di sini setelah ada transaksi." />
                @else
                    <ol class="relative space-y-4 border-l border-line pl-5">
                        @foreach ($activity as $item)
                            <li class="relative">
                                <span class="absolute top-1.5 -left-[1.4375rem] h-2 w-2 rounded-full ring-4 ring-surface bg-brand"
                                      aria-hidden="true"></span>
                                <a href="{{ $item['route'] }}" class="block rounded-lg p-1.5 transition hover:bg-surface-2">
                                    <span class="block text-sm font-semibold text-ink">{{ $item['title'] }}</span>
                                    <span class="mt-0.5 block truncate text-xs text-ink-3">{{ $item['detail'] }}</span>
                                    <span class="mt-1 block text-[11px] text-ink-3/80">
                                        {{ \Carbon\Carbon::parse($item['at'])->diffForHumans() }}
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-card>
        </div>
    </div>
@endsection
