@php $user = auth()->user(); @endphp

<header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
    <div class="flex h-16 items-center gap-4 px-4 sm:px-6 lg:px-8">
        <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden"
                @click="document.getElementById('sidebar').classList.toggle('-translate-x-full')"
                aria-label="Buka menu">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <div class="hidden min-w-0 flex-1 sm:block">
            <h1 class="truncate text-base font-semibold text-slate-900">@yield('heading', 'Dashboard')</h1>
            @hasSection('subheading')
                <p class="truncate text-xs text-slate-500">@yield('subheading')</p>
            @endif
        </div>
        <div class="flex-1 sm:hidden"></div>

        <div class="flex items-center gap-2 sm:gap-3">
            <span class="hidden items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs text-slate-600 md:inline-flex">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ now()->translatedFormat('d M Y, H:i') }}
            </span>

            <div x-data="{ open: false }" class="relative">
                <button type="button" @click="open = !open" @click.outside="open = false"
                        class="flex items-center gap-2 rounded-lg px-1.5 py-1.5 hover:bg-slate-100">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-600 text-xs font-bold text-white">
                        {{ $user?->initials }}
                    </span>
                    <span class="hidden text-left sm:block">
                        <span class="block text-xs font-semibold leading-tight text-slate-800">{{ $user?->name }}</span>
                        <span class="block text-[11px] leading-tight text-slate-500">{{ $user?->role?->name }}</span>
                    </span>
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" x-transition
                     class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-slate-200">
                    <div class="border-b border-slate-100 px-4 py-3">
                        <p class="truncate text-sm font-semibold text-slate-800">{{ $user?->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $user?->username }}</p>
                    </div>
                    <a href="{{ route('profile') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profil Saya
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-rose-600 hover:bg-rose-50">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
