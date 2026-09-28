@props([
    'title' => null,
    'subtitle' => null,
    'maxWidth' => '2xl',
    'icon' => null,
    'closeOnBackdrop' => true,
])

@php
    $widths = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '3xl' => 'sm:max-w-3xl',
        '4xl' => 'sm:max-w-4xl',
    ];
@endphp

{{--
    Dialog modal berbasis Alpine state lokal (`open`).
    Pemakaian:
        <x-modal title="Judul" x-ref="m"> ... </x-modal>
        <x-button x-on:click="$refs.m.open = true">Buka</x-button>
--}}

<div
    x-data="{ open: false }"
    x-on:keydown.escape="open = false"
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-live="polite"
>
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition-opacity ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-950/50 backdrop-blur-[2px]"
        @if ($closeOnBackdrop) x-on:click="open = false" @endif
        aria-hidden="true"
    ></div>

    <div class="flex min-h-full items-end justify-center sm:items-center sm:p-4">
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95 sm:translate-y-0"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95 sm:translate-y-0"
            role="dialog"
            aria-modal="true"
            @if ($title) aria-label="{{ $title }}" @endif
            tabindex="-1"
            {{ $attributes->merge(['class' => 'relative w-full ' . ($widths[$maxWidth] ?? $widths['2xl']) . ' rounded-t-2xl bg-surface shadow-e3 ring-1 ring-line outline-none sm:rounded-2xl']) }}
        >
            @if ($title || $subtitle)
                <div class="flex items-start gap-3 border-b border-line px-5 py-4">
                    @if ($icon)
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-surface-3 text-ink-2">
                            <x-icon :name="$icon" class="h-[18px] w-[18px]" />
                        </span>
                    @endif
                    <div class="min-w-0 flex-1">
                        <h2 class="text-sm font-semibold text-ink">{{ $title }}</h2>
                        @if ($subtitle)<p class="mt-0.5 text-xs text-ink-3">{{ $subtitle }}</p>@endif
                    </div>
                    <button type="button" x-on:click="open = false" aria-label="Tutup dialog"
                            class="-mt-1 -mr-1 rounded-lg p-1.5 text-ink-3 transition hover:bg-surface-3 hover:text-ink">
                        <x-icon name="x-circle" class="h-4 w-4" />
                    </button>
                </div>
            @endif

            <div class="px-5 py-4">
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-line bg-surface-2 px-5 py-3.5">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
