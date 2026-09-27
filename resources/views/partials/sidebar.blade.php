@php
    $user = auth()->user();
    $can = fn (string $permission) => $user?->hasPermission($permission);

    $navigation = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10', 'permission' => 'dashboard.view', 'active' => 'dashboard'],
        ['label' => 'Pasien', 'route' => 'patients.index', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'permission' => 'patient.view', 'active' => 'patients.*'],
        ['label' => 'Pendaftaran', 'route' => 'registrations.index', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'permission' => 'registration.view', 'active' => 'registrations.*'],
        ['label' => 'Antrean', 'route' => 'queue.index', 'icon' => 'M4 6h16M4 10h16M4 14h10M4 18h7', 'permission' => 'queue.manage', 'active' => 'queue.*'],

        ['label' => 'Master Data', 'permission' => 'master.view', 'open' => true, 'children' => [
            ['label' => 'Data Poli', 'route' => 'master.polies.index', 'active' => 'master.polies.*'],
            ['label' => 'Data Dokter', 'route' => 'master.doctors.index', 'active' => 'master.doctors.*'],
            ['label' => 'ICD-10', 'route' => 'master.icd10.index', 'active' => 'master.icd10.*'],
            ['label' => 'Data Obat', 'route' => 'master.drugs.index', 'active' => 'master.drugs.*'],
        ]],

        ['label' => 'Permintaan Demo', 'route' => 'admin.demo-requests.index', 'icon' => 'M3 8l7.4 6.4a2 2 0 002.5 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'permission' => 'reports.view', 'active' => 'admin.demo-requests.*'],

        ['label' => 'Audit Trail', 'route' => 'admin.audit-logs.index', 'icon' => 'M9 17v-2m4 2v-4m4 4v-6M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z', 'permission' => 'audit.view', 'active' => 'admin.audit-logs.*', 'hiddenWithout' => true],

        ['label' => 'Pengguna', 'route' => 'admin.users.index', 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6-8a4 4 0 11-4-4m7 20h-3a6 6 0 00-4 5', 'permission' => 'user.manage', 'active' => 'admin.users.*'],
    ];
@endphp

<aside id="sidebar"
       class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full border-r border-slate-800 bg-slate-900 transition-transform lg:translate-x-0">
    <div class="flex h-16 items-center gap-3 border-b border-slate-800 px-6">
        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-500 text-lg font-bold text-slate-900">S</span>
        <div class="leading-tight">
            <p class="text-sm font-semibold text-white">{{ config('app.name') }}</p>
            <p class="text-[11px] text-slate-400">Hospital Information System</p>
        </div>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" id="sidebar-nav">
        @foreach ($navigation as $item)
            @if (isset($item['children']))
                @php $visibleChildren = collect($item['children'])->filter(fn ($c) => $can($item['permission'])); @endphp
                @if ($visibleChildren->isNotEmpty() && ! ($item['hiddenWithout'] ?? false))
                    @php $isOpen = collect($visibleChildren)->contains(fn ($c) => request()->routeIs($c['active'])); @endphp
                    <div x-data="{ open: @js($isOpen) }" class="pt-1">
                        <button type="button" @click="open = !open"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            <span class="flex-1 text-left">{{ $item['label'] }}</span>
                            <svg class="h-4 w-4 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-collapse class="ml-4 space-y-1 border-l border-slate-800 pl-3 pt-1">
                            @foreach ($visibleChildren as $child)
                                <a href="{{ route($child['route']) }}"
                                   @class([
                                       'block rounded-md px-3 py-1.5 text-sm',
                                       'bg-teal-500/15 text-teal-300' => request()->routeIs($child['active']),
                                       'text-slate-400 hover:bg-slate-800 hover:text-white' => ! request()->routeIs($child['active']),
                                   ])>{{ $child['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @elseif ($can($item['permission']))
                <a href="{{ route($item['route']) }}"
                   @class([
                       'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                       'bg-teal-500/15 text-teal-300' => request()->routeIs($item['active']),
                       'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs($item['active']),
                   ])>
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                    </svg>
                    {{ $item['label'] }}
                </a>
            @endif
        @endforeach
    </nav>

    <div class="border-t border-slate-800 p-4 text-[11px] leading-relaxed text-slate-500">
        <p><span class="text-slate-400">Peran:</span> {{ $user?->role?->name ?? '-' }}</p>
        <p><span class="text-slate-400">Izin aktif:</span> {{ $user?->isSuperAdmin() ? 'Semua' : ($user?->permissions?->count() ?? 0) }}</p>
    </div>
</aside>
