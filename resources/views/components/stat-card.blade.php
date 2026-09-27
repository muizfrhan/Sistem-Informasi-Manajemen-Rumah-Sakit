@props(['label', 'value', 'hint' => null, 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'tone' => 'teal'])

@php
    $tones = [
        'teal' => 'bg-teal-50 text-teal-600',
        'blue' => 'bg-sky-50 text-sky-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'green' => 'bg-emerald-50 text-emerald-600',
        'purple' => 'bg-violet-50 text-violet-600',
        'rose' => 'bg-rose-50 text-rose-600',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="truncate text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</p>
            <p class="mt-1.5 text-2xl font-bold text-slate-900">{{ $value }}</p>
            @if ($hint)<p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>@endif
        </div>
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $tones[$tone] ?? $tones['teal'] }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
            </svg>
        </span>
    </div>
</div>
