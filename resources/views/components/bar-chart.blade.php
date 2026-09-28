@props([
    'data' => [],
    'max' => 1,
    'height' => 'h-44',
    'emptyText' => 'Belum ada data untuk rentang ini.',
    'valueLabel' => 'kunjungan',
    'highlightLast' => true,
])

{{--
    Grafik batang ringan berbasis CSS (tanpa library eksternal).
    Setiap batang dapat dibaca sebagai teks oleh screen reader dan
    memiliki nilai yang dapat dilihat lewat tooltip.
--}}

@php
    $hasData = collect($data)->sum('count') > 0;
    $peak = max(1, (int) $max);
    $lastIndex = count($data) - 1;
@endphp

@if (! $hasData)
    <x-empty-state compact icon="chart" title="{{ $emptyText }}" />
@else
    <div class="{{ $height }} flex items-end gap-1" role="img"
         aria-label="Grafik {{ $valueLabel }} untuk {{ count($data) }} periode terakhir.">
        @foreach ($data as $index => $point)
            @php
                $percent = $point['count'] > 0 ? max(3, round($point['count'] / $peak * 100)) : 1;
                $isLast = $highlightLast && $index === $lastIndex;
            @endphp

            <div class="group flex h-full min-w-0 flex-1 flex-col justify-end gap-1.5"
                 title="{{ $point['title'] ?? $point['label'] }}: {{ $point['count'] }} {{ $valueLabel }}">
                <div class="relative flex flex-1 items-end">
                    <div class="w-full rounded-t-md transition-all
                                {{ $isLast ? 'bg-brand' : 'bg-brand/35 group-hover:bg-brand/70' }}"
                         style="height: {{ $percent }}%"></div>

                    <span class="pointer-events-none absolute -top-1 left-1/2 z-10 -translate-x-1/2 -translate-y-full
                                 rounded-md bg-ink px-2 py-1 text-[11px] font-semibold whitespace-nowrap text-canvas opacity-0 shadow-e2
                                 transition-opacity group-hover:opacity-100">
                        {{ $point['count'] }} {{ $valueLabel }}
                    </span>
                </div>

                <span @class([
                    'truncate text-center text-[10px]',
                    'font-bold text-brand' => $isLast,
                    'text-ink-3' => ! $isLast,
                ])>{{ $point['label'] }}</span>
            </div>
        @endforeach
    </div>

    <ul class="sr-only">
        @foreach ($data as $point)
            <li>{{ $point['title'] ?? $point['label'] }}: {{ $point['count'] }} {{ $valueLabel }}</li>
        @endforeach
    </ul>
@endif
