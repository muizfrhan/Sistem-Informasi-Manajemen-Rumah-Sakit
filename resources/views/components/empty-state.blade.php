@props([
    'title' => 'Belum ada data',
    'message' => null,
    'icon' => 'inbox',
    'compact' => false,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 text-center '.($compact ? 'py-8' : 'py-14')]) }}>
    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-surface-3 text-ink-3">
        <x-icon :name="$icon" class="h-6 w-6" />
    </span>
    <p class="mt-3.5 text-sm font-semibold text-ink">{{ $title }}</p>
    @if ($message)
        <p class="mt-1 max-w-sm text-xs leading-relaxed text-ink-3">{{ $message }}</p>
    @endif
    @if (! $slot->isEmpty())
        <div class="mt-4 flex flex-wrap justify-center gap-2">{{ $slot }}</div>
    @endif
</div>
