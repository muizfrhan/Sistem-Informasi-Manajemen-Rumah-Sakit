@extends('layouts.app')

@section('title', 'Tagihan ' . $invoice->invoice_number)
@section('heading', 'Tagihan ' . $invoice->invoice_number)
@section('subheading', $invoice->patient->name . ' - ' . ($invoice->issued_at?->translatedFormat('d F Y') ?? '-'))

@php
    // Subtitle dirangkai di PHP: atribut komponen Blade tidak boleh memuat
    // karakter kutip ganda di dalam nilai atributnya.
    $itemCountSubtitle = $invoice->items->count().' item';
@endphp

@section('content')
    <div class="space-y-5">

        {{-- ===================== Header ===================== --}}
        <x-card>
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-badge :class="$invoice->status->badgeClass()" dot>{{ $invoice->status->label() }}</x-badge>
                        @if ($invoice->insurance_type && $invoice->insurance_type !== 'NONE')
                            <x-badge variant="info">
                                {{ \App\Enums\InsuranceType::tryFrom($invoice->insurance_type)?->label() ?? $invoice->insurance_type }}
                            </x-badge>
                        @endif
                    </div>

                    <h2 class="mt-3 text-lg font-bold text-ink">{{ $invoice->patient->name }}</h2>
                    <p class="mt-0.5 text-sm text-ink-3">
                        <span class="font-mono">{{ $invoice->patient->mr_number }}</span>
                        &middot; {{ $invoice->patient->gender->label() }}
                        &middot; {{ $invoice->patient->age }} th
                    </p>
                </div>

                <div class="shrink-0 text-right">
                    <p class="text-xs text-ink-3">Total Tagihan</p>
                    <p class="text-3xl font-bold tracking-tight text-ink tabular-nums">
                        Rp {{ number_format($invoice->total, 0, ',', '.') }}
                    </p>
                    @if ($invoice->outstanding > 0)
                        <p class="mt-1 text-xs font-semibold text-danger-ink">
                            Sisa Rp {{ number_format($invoice->outstanding, 0, ',', '.') }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Progress pembayaran --}}
            @if ($invoice->total > 0)
                <div class="mt-5 border-t border-line pt-4">
                    <div class="mb-1.5 flex items-center justify-between text-xs">
                        <span class="text-ink-3">Progres Pembayaran</span>
                        <span class="font-semibold text-ink tabular-nums">
                            Rp {{ number_format($invoice->paid_total, 0, ',', '.') }} dari
                            Rp {{ number_format($invoice->total, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-surface-3">
                        <div @class([
                                'h-full rounded-full transition-all',
                                'bg-emerald-500' => $invoice->progress >= 100,
                                'bg-brand' => $invoice->progress < 100,
                            ])
                             style="width: {{ $invoice->progress }}%"></div>
                    </div>
                </div>
            @endif
        </x-card>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <div class="space-y-5 lg:col-span-2">
                {{-- ===================== Rincian item ===================== --}}
                <x-card title="Rincian Tagihan" :subtitle="$itemCountSubtitle" padding="p-0">
                    <x-data-table :cols="$invoice->items->count()" min-width="42rem">
                        <x-slot:head>
                            <tr>
                                <th scope="col">Item</th>
                                <th scope="col" class="hidden sm:table-cell">Jenis</th>
                                <th scope="col" class="text-right">Qty</th>
                                <th scope="col" class="text-right">Subtotal</th>
                            </tr>
                        </x-slot:head>

                        <x-slot:body>
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td data-label="Item">
                                        <span class="font-medium text-ink">{{ $item->description }}</span>
                                    </td>
                                    <td data-label="Jenis" class="hidden sm:table-cell">
                                        <x-badge variant="slate">{{ $item->item_type->label() }}</x-badge>
                                    </td>
                                    <td data-label="Qty" class="text-right text-xs tabular-nums">
                                        {{ $item->quantity }}
                                    </td>
                                    <td data-label="Subtotal" class="text-right font-semibold tabular-nums">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </x-slot:body>
                    </x-data-table>

                    <div class="space-y-2 border-t border-line bg-surface-2 px-5 py-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-ink-3">Subtotal</span>
                            <span class="font-semibold text-ink tabular-nums">
                                Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}
                            </span>
                        </div>

                        @if ($invoice->discount > 0)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-ink-3">Diskon</span>
                                <span class="font-semibold text-success-ink tabular-nums">
                                    &minus; Rp {{ number_format($invoice->discount, 0, ',', '.') }}
                                </span>
                            </div>
                        @endif

                        <div class="flex items-center justify-between border-t border-line pt-2">
                            <span class="text-sm font-semibold text-ink">Total</span>
                            <span class="text-lg font-bold text-ink tabular-nums">
                                Rp {{ number_format($invoice->total, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </x-card>

                {{-- ===================== Riwayat pembayaran ===================== --}}
                <x-card title="Riwayat Pembayaran" :subtitle="$invoice->payments->count() . ' transaksi'" padding="p-0">
                    @if ($invoice->payments->isEmpty())
                        <x-empty-state compact icon="wallet" title="Belum ada pembayaran"
                                       message="Belum ada pembayaran yang dicatat untuk tagihan ini." />
                    @else
                        <div class="table-wrap">
                            <table class="table table-stack" style="min-width: 40rem">
                                <thead>
                                    <tr>
                                        <th scope="col">Waktu</th>
                                        <th scope="col">Metode</th>
                                        <th scope="col" class="hidden sm:table-cell">Referensi</th>
                                        <th scope="col" class="hidden md:table-cell">Kasir</th>
                                        <th scope="col" class="text-right">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invoice->payments->sortByDesc('paid_at') as $payment)
                                        <tr>
                                            <td data-label="Waktu">
                                                <span class="text-xs font-medium text-ink">
                                                    {{ $payment->paid_at->translatedFormat('d M Y H:i') }}
                                                </span>
                                            </td>
                                            <td data-label="Metode">
                                                <x-badge :class="$payment->method->badgeClass()">
                                                    {{ $payment->method->label() }}
                                                </x-badge>
                                            </td>
                                            <td data-label="Referensi" class="hidden sm:table-cell font-mono text-xs text-ink-2">
                                                {{ $payment->reference ?? '-' }}
                                            </td>
                                            <td data-label="Kasir" class="hidden md:table-cell text-xs text-ink-2">
                                                {{ $payment->cashier?->name ?? '-' }}
                                            </td>
                                            <td data-label="Nominal" class="text-right font-semibold tabular-nums">
                                                Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </x-card>
            </div>

            {{-- ===================== Panel samping ===================== --}}
            <div class="space-y-4">
                {{-- ---------- Form pembayaran ---------- --}}
                @if ($canManage && $invoice->status->isOpen() && $invoice->total > 0)
                    <x-card title="Catat Pembayaran" subtitle="Pembayaran sebagian diperbolehkan">
                        <form method="POST" action="{{ route('billing.payments.store', $invoice) }}" class="space-y-4">
                            @csrf

                            <x-input name="amount" label="Nominal Pembayaran" type="number" required
                                     min="1" :max="$invoice->outstanding" :value="old('amount', $invoice->outstanding)"
                                     prefix="Rp"
                                     hint="Maksimal Rp {{ number_format($invoice->outstanding, 0, ',', '.') }}." />

                            <x-select name="method" label="Metode Pembayaran" required
                                      :value="old('method')"
                                      :options="collect($paymentMethods)->mapWithKeys(fn ($m) => [$m->value => $m->label()])->all()"
                                      placeholder="Pilih metode" />

                            <x-input name="reference" label="Nomor Referensi" :maxlength="100"
                                     :value="old('reference')"
                                     placeholder="No. kuitansi, VA, atau slip transfer" />

                            <x-button type="submit" class="w-full" icon="wallet">Simpan Pembayaran</x-button>
                        </form>
                    </x-card>
                @elseif ($invoice->status === \App\Enums\InvoiceStatus::PAID)
                    <div class="card border-success-ink/25 bg-success-soft p-5 text-center">
                        <x-icon name="check-circle" class="mx-auto h-8 w-8 text-success-ink" />
                        <p class="mt-2 text-sm font-bold text-success-ink">Tagihan Lunas</p>
                        <p class="mt-1 text-xs text-success-ink/90">
                            Dibayar penuh pada {{ $invoice->paid_at?->translatedFormat('d F Y H:i') ?? '-' }}
                        </p>
                    </div>
                @endif

                <x-card title="Informasi Tagihan">
                    <dl class="space-y-3 text-sm">
                        @foreach ([
                            ['No. Tagihan', $invoice->invoice_number, true],
                            ['Diterbitkan', $invoice->issued_at?->translatedFormat('d F Y'), false],
                            ['Jatuh Tempo', $invoice->due_at?->translatedFormat('d F Y'), false],
                            ['Dibuat Oleh', $invoice->author?->name ?? 'Sistem', false],
                        ] as [$label, $value, $mono])
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="shrink-0 text-xs text-ink-3">{{ $label }}</dt>
                                <dd @class(['text-right text-xs font-medium text-ink', 'font-mono' => $mono])>{{ $value ?? '-' }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    @if ($canManage && $invoice->status->isOpen())
                        <form method="POST" action="{{ route('billing.destroy', $invoice) }}" class="mt-4 border-t border-line pt-4">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="ghost" size="sm" class="w-full text-danger-ink"
                                      x-on:click.prevent="$dispatch('confirm', {
                                          action: $el.form.action,
                                          method: 'DELETE',
                                          title: 'Batalkan tagihan {{ $invoice->invoice_number }}?',
                                          message: 'Tagihan akan dibatalkan dan tidak dapat digunakan lagi. Tindakan ini hanya mungkin bila belum ada pembayaran masuk.',
                                          confirmLabel: 'Batalkan Tagihan',
                                      })">
                                Batalkan Tagihan
                            </x-button>
                        </form>
                    @endif
                </x-card>

                <x-card title="Navigasi">
                    <div class="space-y-2">
                        <a href="{{ route('billing.index') }}" class="nav-link w-full">
                            <x-icon name="arrow-left" class="h-4 w-4" />
                            Semua Tagihan
                        </a>
                        <a href="{{ route('patients.show', $invoice->patient) }}" class="nav-link w-full">
                            <x-icon name="users" class="h-4 w-4" />
                            Profil Pasien
                        </a>
                        @if ($invoice->registration)
                            <a href="{{ route('registrations.show', $invoice->registration) }}" class="nav-link w-full">
                                <x-icon name="clipboard" class="h-4 w-4" />
                                Detail Kunjungan
                            </a>
                        @endif
                    </div>
                </x-card>
            </div>
        </div>
    </div>
@endsection
