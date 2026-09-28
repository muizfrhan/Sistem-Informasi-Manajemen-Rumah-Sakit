@props(['title', 'description' => null, 'class' => ''])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between '.$class]) }}>
    <div class="min-w-0">
        @hasSection('breadcrumbs')
            @yield('breadcrumbs')
            <div class="mb-1.5"></div>
        @endif

        <h1 class="text-xl font-bold tracking-tight text-ink sm:text-2xl">{{ $title }}</h1>

        @if ($description)
            <p class="mt-1 text-sm text-ink-3">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
