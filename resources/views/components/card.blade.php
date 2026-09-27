@props(['title' => null, 'subtitle' => null, 'footer' => null, 'padding' => 'p-5'])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200']) }}>
    @if ($title || $subtitle)
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
            <div>
                @if ($title)<h3 class="text-sm font-semibold text-slate-900">{{ $title }}</h3>@endif
                @if ($subtitle)<p class="mt-0.5 text-xs text-slate-500">{{ $subtitle }}</p>@endif
            </div>
            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>

    @if ($footer)
        <div class="border-t border-slate-200 bg-slate-50 px-5 py-3 text-xs text-slate-500">{{ $footer }}</div>
    @endif
</div>
