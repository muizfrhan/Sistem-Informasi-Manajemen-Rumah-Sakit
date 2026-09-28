@php
    $user = auth()->user();
@endphp

<header class="sticky top-0 z-30 border-b border-line bg-surface/85 backdrop-blur-md">
    <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
        {{-- Pembuka drawer (mobile) --}}
        <button type="button"
                x-on:click="$store.sidebar.open = true"
                class="-ml-1.5 rounded-lg p-2 text-ink-2 transition hover:bg-surface-2 hover:text-ink lg:hidden"
                aria-label="Buka menu navigasi"
                x-bind:aria-expanded="$store.sidebar.open">
            <x-icon name="menu" class="h-5 w-5" />
        </button>

        <div class="min-w-0 flex-1">
            <div class="truncate text-sm font-semibold text-ink">@yield('heading', config('app.name'))</div>
            @hasSection('subheading')
                <div class="truncate text-xs text-ink-3">@yield('subheading')</div>
            @endif
        </div>

        <div class="flex shrink-0 items-center gap-1.5">
            {{-- Tanggal operasional --}}
            <span class="mr-1 hidden items-center gap-1.5 rounded-lg bg-surface-2 px-2.5 py-1.5 text-xs font-medium text-ink-2 xl:inline-flex">
                <x-icon name="calendar" class="h-3.5 w-3.5" />
                <time datetime="{{ now()->toDateString() }}">{{ now()->translatedFormat('D, d M Y') }}</time>
            </span>

            {{-- Pencarian cepat pasien --}}
            @if (auth()->user()->can(\App\Enums\Permission::VIEW_PATIENT))
                <div x-data="{ open: false }" class="relative">
                    <button type="button" x-on:click="open = !open" x-on:click.outside="open = false"
                            x-bind:aria-expanded="open"
                            class="rounded-lg p-2 text-ink-2 transition hover:bg-surface-2 hover:text-ink"
                            aria-label="Cari pasien">
                        <x-icon name="search" class="h-5 w-5" />
                    </button>

                    <div x-show="open" x-cloak x-on:keydown.escape="open = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute right-0 z-40 mt-2 w-[min(22rem,calc(100vw-2rem))]">
                        <form method="GET" action="{{ route('patients.index') }}" class="card p-2 shadow-e3">
                            <label for="global-search" class="sr-only">Cari pasien</label>
                            <div class="flex gap-2">
                                <input id="global-search" name="q" type="search" autocomplete="off"
                                       placeholder="No. RM, NIK, atau nama…"
                                       class="field-control text-sm">
                                <x-button type="submit" size="sm" class="shrink-0">Cari</x-button>
                            </div>
                            <p class="mt-2 px-1 text-[11px] text-ink-3">Pencarian memakai No. Rekam Medis, NIK, atau nama pasien.</p>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Mode gelap --}}
            <button type="button" x-on:click="$store.theme.toggle()"
                    class="rounded-lg p-2 text-ink-2 transition hover:bg-surface-2 hover:text-ink"
                    aria-label="Ganti mode terang dan gelap">
                <span x-show="! $store.theme.isDark()"><x-icon name="moon" class="h-5 w-5" /></span>
                <span x-show="$store.theme.isDark()" x-cloak><x-icon name="sun" class="h-5 w-5" /></span>
            </button>

            {{-- Menu pengguna --}}
            <div x-data="{ open: false }" class="relative">
                <button type="button" x-on:click="open = !open" x-on:click.outside="open = false"
                        x-on:keydown.escape="open = false"
                        x-bind:aria-expanded="open"
                        class="flex items-center gap-2 rounded-lg py-1.5 pr-1.5 pl-2 transition hover:bg-surface-2">
                    <span class="hidden text-left leading-tight sm:block">
                        <span class="block max-w-[9rem] truncate text-xs font-semibold text-ink">{{ $user?->name }}</span>
                        <span class="block text-[11px] text-ink-3">{{ $user?->role?->name ?? 'Tanpa peran' }}</span>
                    </span>
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand text-[11px] font-bold text-brand-fg">
                        {{ $user?->initials }}
                    </span>
                    <x-icon name="chevron-down" class="h-3.5 w-3.5 shrink-0 text-ink-3" />
                </button>

                <div x-show="open" x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="absolute right-0 z-40 mt-2 w-60 overflow-hidden rounded-xl bg-surface shadow-e3 ring-1 ring-line">
                    <div class="border-b border-line px-4 py-3">
                        <p class="truncate text-sm font-semibold text-ink">{{ $user?->name }}</p>
                        <p class="truncate text-xs text-ink-3">{{ $user?->username }}</p>
                        @if ($user?->last_login_at)
                            <p class="mt-1.5 text-[11px] text-ink-3">
                                Login terakhir {{ $user->last_login_at->translatedFormat('d M Y, H:i') }}
                            </p>
                        @endif
                    </div>

                    <div class="p-1.5">
                        <a href="{{ route('profile') }}" class="nav-link py-2 text-sm">
                            <x-icon name="user" class="h-4 w-4" />
                            Profil Saya
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-link w-full py-2 text-sm text-danger-ink hover:bg-danger-soft">
                                <x-icon name="logout" class="h-4 w-4" />
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
