@props([
    'label',
    'value',
    'hint' => null,
    'icon' => 'chart',
    'tone' => 'brand',
    'href' => null,
    'unit' => null,
])

@php
    $tones = [
        'brand' => 'bg-brand-soft text-brand-ink',
        'info' => 'bg-info-soft text-info-ink',
        'success' => 'bg-success-soft text-success-ink',
        'warning' => 'bg-warning-soft text-warning-ink',
        'danger' => 'bg-danger-soft text-danger-ink',
        'slate' => 'bg-surface-3 text-ink-2',
    ];

    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'card group relative overflow-hidden p-4 transition'.($href ? ' hover:border-line-2 hover:shadow-e2' : '')]) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <p class="truncate text-[11px] font-semibold tracking-wide text-ink-3 uppercase">{{ $label }}</p>
            <p class="mt-1.5 flex items-baseline gap-1">
                <span class="text-2xl leading-none font-bold tracking-tight text-ink tabular-nums">{{ $value }}</span>
                @if ($unit)<span class="text-xs font-medium text-ink-3">{{ $unit }}</span>@endif
            </p>
            @if ($hint)<p class="mt-1.5 line-clamp-2 text-xs text-ink-3">{{ $hint }}</p>@endif
        </div>
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $tones[$tone] ?? $tones['brand'] }}">
            <x-icon :name="$icon" class="h-[18px] w-[18px]" />
        </span>
    </div>
</{{ $tag }}>
