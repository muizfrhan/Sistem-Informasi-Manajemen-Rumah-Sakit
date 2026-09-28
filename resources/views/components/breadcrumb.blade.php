@props(['label' => 'Halaman', 'separator' => 'chevron-right'])

<div class="flex flex-wrap items-center gap-1.5 text-xs text-ink-3">
    @foreach ($label as $index => $crumb)
        @if ($index > 0)
            <x-icon :name="$separator" class="h-3 w-3 shrink-0 text-ink-3/70" />
        @endif

        @if (! $loop->last && isset($crumb['url']))
            <a href="{{ $crumb['url'] }}" class="rounded transition hover:text-ink hover:underline">{{ $crumb['label'] }}</a>
        @else
            <span @class(['font-medium text-ink-2' => $loop->last]) {{ $loop->last ? 'aria-current="page"' : '' }}>{{ $crumb['label'] }}</span>
        @endif
    @endforeach
</div>
