@props(['title' => null, 'subtitle' => null, 'footer' => null, 'padding' => 'card-pad', 'class' => ''])

<div {{ $attributes->merge(['class' => 'card '.$class]) }}>
    @if ($title || $subtitle || isset($actions))
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-3.5">
            <div class="min-w-0">
                @if ($title)<h2 class="text-sm font-semibold text-ink">{{ $title }}</h2>@endif
                @if ($subtitle)<p class="mt-0.5 text-xs text-ink-3">{{ $subtitle }}</p>@endif
            </div>
            @isset($actions)
                <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>

    @if ($footer)
        <div class="border-t border-line bg-surface-2 px-5 py-3 text-xs text-ink-3">{{ $footer }}</div>
    @endif
</div>
