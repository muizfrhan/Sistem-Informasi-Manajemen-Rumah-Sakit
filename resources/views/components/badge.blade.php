@props(['variant' => 'slate', 'class' => '', 'dot' => false])

@php
    $tones = [
        'slate' => 'bg-surface-3 text-ink-2 ring-line-2',
        'brand' => 'bg-brand-soft text-brand-ink ring-brand-ink/20',
        'success' => 'bg-success-soft text-success-ink ring-success-ink/20',
        'warning' => 'bg-warning-soft text-warning-ink ring-warning-ink/20',
        'danger' => 'bg-danger-soft text-danger-ink ring-danger-ink/20',
        'info' => 'bg-info-soft text-info-ink ring-info-ink/20',
        'outline' => 'bg-transparent text-ink-2 ring-line-2',
    ];

    $dotTones = [
        'slate' => 'bg-ink-3',
        'brand' => 'bg-brand',
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-rose-500',
        'info' => 'bg-sky-500',
        'outline' => 'bg-ink-3',
    ];

    $tone = $tones[$variant] ?? $tones['slate'];
@endphp

<span {{ $attributes->merge(['class' => 'badge ring-1 ring-inset '.$tone.' '.$class]) }}>
    @if ($dot)
        <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $dotTones[$variant] ?? $dotTones['slate'] }}" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>
