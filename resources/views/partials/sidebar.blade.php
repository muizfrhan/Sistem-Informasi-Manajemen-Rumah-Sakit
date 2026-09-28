@php
    $user = auth()->user();
    $navigation = \App\Support\Navigation::for($user);
@endphp

{{--
    Sidebar aplikasi.

    Desktop : docked di kiri, selalu terlihat.
    Mobile  : drawer geser dengan backdrop, focus trap, tombol tutup di dalam
              drawer, dan dapat ditutup dengan Escape atau klik backdrop.
--}}

<div
    x-data="{
        init() {
            // Kunci scroll body selagi drawer mobile terbuka.
            this.$watch('$store.sidebar.open', (isOpen) => {
                document.body.classList.toggle('overflow-hidden', isOpen);
                if (isOpen) { this.$nextTick(() => this.$refs.closeButton?.focus()) }
            });
        },
    }"
    x-on:keydown.escape.window="$store.sidebar.close()"
>
    {{-- Backdrop khusus mobile --}}
    <div
        x-show="$store.sidebar.open"
        x-cloak
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-[2px] lg:hidden"
        x-on:click="$store.sidebar.close()"
        aria-hidden="true"
    ></div>

    <aside
        class="fixed inset-y-0 left-0 z-50 flex w-[17rem] flex-col border-r border-line bg-surface
               transition-transform duration-200 ease-out
               -translate-x-full lg:translate-x-0"
        x-bind:class="$store.sidebar.open && '!translate-x-0'"
        aria-label="Navigasi utama"
    >
        {{-- Brand --}}
        <div class="flex h-16 shrink-0 items-center gap-3 border-b border-line px-5">
            <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand text-brand-fg shadow-e1">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                </span>
                <span class="min-w-0 leading-tight">
                    <span class="block truncate text-sm font-bold text-ink">{{ config('simrs.hospital_name') }}</span>
                    <span class="block truncate text-[10px] font-medium tracking-wide text-ink-3 uppercase">Hospital Information System</span>
                </span>
            </a>

            <button type="button" x-ref="closeButton" x-on:click="$store.sidebar.close()"
                    class="-mr-1.5 ml-auto rounded-lg p-1.5 text-ink-3 transition hover:bg-surface-3 hover:text-ink lg:hidden"
                    aria-label="Tutup menu navigasi">
                <x-icon name="x-circle" class="h-5 w-5" />
            </button>
        </div>

        {{-- Navigasi --}}
        <nav class="scroll-slim flex-1 space-y-5 overflow-y-auto px-3 py-4" aria-label="Menu utama">
            @foreach ($navigation as $group)
                <div>
                    <p class="px-3 pb-1.5 text-[10px] font-bold tracking-wider text-ink-3 uppercase">
                        {{ $group['label'] }}
                    </p>

                    <ul class="space-y-0.5">
                        @foreach ($group['items'] as $item)
                            @php $active = request()->routeIs($item['pattern']); @endphp
                            <li>
                                <a href="{{ route($item['route']) }}"
                                   @class(['nav-link', 'nav-link-active' => $active])
                                   @if ($active) aria-current="page" @endif
                                   x-on:click="$store.sidebar.close()">
                                    <x-icon :name="$item['icon']" class="h-[18px] w-[18px] shrink-0" />
                                    <span class="truncate">{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

        {{-- Identitas pengguna --}}
        <div class="shrink-0 border-t border-line p-3">
            <a href="{{ route('profile') }}"
               class="flex items-center gap-3 rounded-lg px-2 py-2 transition hover:bg-surface-2">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-soft text-xs font-bold text-brand-ink">
                    {{ $user?->initials }}
                </span>
                <span class="min-w-0 flex-1 leading-tight">
                    <span class="block truncate text-xs font-semibold text-ink">{{ $user?->name }}</span>
                    <span class="block truncate text-[11px] text-ink-3">{{ $user?->role?->name ?? 'Tanpa peran' }}</span>
                </span>
                <x-icon name="chevron-right" class="h-4 w-4 shrink-0 text-ink-3" />
            </a>
        </div>
    </aside>
</div>
