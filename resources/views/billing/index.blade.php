@extends('layouts.app')

@section('title', 'Tagihan')
@section('heading', 'Tagihan & Pembayaran')
@section('subheading', $invoices->total() . ' tagihan tercatat')

@section('content')
    <div class="space-y-5">
        <x-page-header title="Tagihan & Pembayaran"
                       :description="'Total ' . number_format($invoices->total(), 0, ',', '.') . ' tagihan'">
            <x-slot:actions>
                <x-button permission="billing.manage" :href="route('billing.create')" icon="plus">Buat Tagihan</x-button>
            </x-slot:actions>
        </x-page-header>

        {{-- ===================== Statistik ===================== --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Belum Lunas" :value="$summary['unpaid_count']" unit="tagihan"
                         :hint="'Sisa Rp ' . number_format($summary['unpaid_total'], 0, ',', '.')"
                         tone="danger" icon="clock" />
            <x-stat-card label="Diterima Hari Ini" :value="'Rp ' . number_format($summary['paid_today'], 0, ',', '.')"
                         hint="Total pelunasan hari ini" tone="success" icon="wallet" />
            <x-stat-card label="Diterima Bulan Ini" :value="'Rp ' . number_format($summary['paid_month'], 0, ',', '.')"
                             :hint="now()->translatedFormat('F Y')" tone="brand" icon="chart" />
            <x-stat-card label="Tagihan Hari Ini" :value="$summary['count_today']" unit="tagihan"
                         hint="Diterbitkan hari ini" tone="slate" icon="receipt" />
        </div>

        <x-card padding="p-0">
            <x-filter-bar :action="route('billing.index')" :reset="route('billing.index')">
                <x-slot:fields>
                    <div>
                        <label for="q" class="field-label">Pencarian</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-ink-3">
                                <x-icon name="search" class="h-3.5 w-3.5" />
                            </span>
                            <input id="q" name="q" type="search" value="{{ request('q') }}"
                                   placeholder="No. tagihan, nama, atau No. RM" class="field-control pl-9">
                        </div>
                    </div>

                    <div>
                        <label for="status" class="field-label">Status</label>
                        <select id="status" name="status" class="field-control">
                            <option value="">Semua status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="from" class="field-label">Diterbitkan Dari</label>
                        <input id="from" name="from" type="date" value="{{ request('from') }}" class="field-control">
                    </div>

                    <div>
                        <label for="to" class="field-label">Sampai</label>
                        <input id="to" name="to" type="date" value="{{ request('to') }}" class="field-control">
                    </div>
                </x-slot:fields>
            </x-filter-bar>

            <x-data-table :cols="$invoices->count()" min-width="64rem"
                          empty="Belum ada tagihan"
                          empty-message="Belum ada tagihan yang cocok dengan filter ini. Tagihan dibuat dari rekam medis yang telah difinalisasi."
                          empty-icon="receipt">
                <x-slot:emptyAction>
                    <x-button :href="route('billing.index')" size="sm" variant="secondary" icon="refresh">Reset Filter</x-button>
                </x-slot:emptyAction>

                <x-slot:head>
                    <tr>
                        <th scope="col">No. Tagihan</th>
                        <th scope="col">Pasien</th>
                        <th scope="col" class="hidden sm:table-cell">Tanggal</th>
                        <th scope="col">Total</th>
                        <th scope="col" class="hidden lg:table-cell">Sisa</th>
                        <th scope="col">Status</th>
                    </tr>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($invoices as $invoice)
                        <tr>
                            <td data-label="No. Tagihan">
                                <a href="{{ route('billing.show', $invoice) }}"
                                   class="font-mono text-xs font-bold text-brand hover:underline">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>

                            <td data-label="Pasien">
                                <a href="{{ route('patients.show', $invoice->patient) }}"
                                   class="font-semibold text-ink hover:underline">
                                    {{ $invoice->patient->name }}
                                </a>
                                <span class="block font-mono text-xs text-ink-3">{{ $invoice->patient->mr_number }}</span>
                            </td>

                            <td data-label="Tanggal" class="hidden sm:table-cell text-xs text-ink-2">
                                {{ $invoice->issued_at?->translatedFormat('d M Y') ?? '-' }}
                            </td>

                            <td data-label="Total" class="font-semibold tabular-nums">
                                Rp {{ number_format($invoice->total, 0, ',', '.') }}
                            </td>

                            <td data-label="Sisa" class="hidden lg:table-cell tabular-nums">
                                @if ($invoice->outstanding > 0)
                                    <span class="font-semibold text-danger-ink">
                                        Rp {{ number_format($invoice->outstanding, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-success-ink">Lunas</span>
                                @endif
                            </td>

                            <td data-label="Status">
                                <x-badge :class="$invoice->status->badgeClass()">{{ $invoice->status->label() }}</x-badge>
                            </td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-data-table>

            @if ($invoices->hasPages())
                {{ $invoices->links() }}
            @endif
        </x-card>
    </div>
@endsection
