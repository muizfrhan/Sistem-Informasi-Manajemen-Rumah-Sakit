@if ($paginator->hasPages())
    @php
        $previous = 'Sebelumnya';
        $next = 'Berikutnya';
    @endphp

    <nav role="navigation" aria-label="Navigasi halaman"
         class="flex flex-col items-center justify-between gap-3 border-t border-line px-4 py-3 sm:flex-row">
        <p class="text-xs text-ink-3">
            Menampilkan
            <span class="font-semibold text-ink-2">{{ $paginator->firstItem() }}</span>
            sampai
            <span class="font-semibold text-ink-2">{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-semibold text-ink-2">{{ number_format($paginator->total()) }}</span>
            data
        </p>

        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="btn btn-sm btn-secondary cursor-not-allowed opacity-50" aria-disabled="true">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                    </svg>
                    <span class="hidden sm:inline">{{ $previous }}</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-sm btn-secondary" aria-label="Ke halaman {{ $previous }}">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                    </svg>
                    <span class="hidden sm:inline">{{ $previous }}</span>
                </a>
            @endif

            <div class="mx-1 flex items-center gap-1">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-1 text-sm text-ink-3">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                      class="flex h-8 min-w-8 items-center justify-center rounded-lg bg-brand px-2 text-xs font-bold text-brand-fg">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}"
                                   class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-xs font-semibold text-ink-2 transition hover:bg-surface-2 hover:text-ink">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-sm btn-secondary" aria-label="Ke halaman {{ $next }}">
                    <span class="hidden sm:inline">{{ $next }}</span>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                    </svg>
                </a>
            @else
                <span class="btn btn-sm btn-secondary cursor-not-allowed opacity-50" aria-disabled="true">
                    <span class="hidden sm:inline">{{ $next }}</span>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
