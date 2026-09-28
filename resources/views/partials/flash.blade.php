@php
    /**
     * Toast notification. Semua pesan sistem (sukses / gagal / peringatan)
     * ditampilkan lewat komponen ini, bukan window.alert() bawaan browser.
     */
    $icons = [
        'check-circle' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'x-circle' => 'm9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'exclamation' => 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z',
        'info' => 'm11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z',
    ];

    $tones = [
        'success' => ['classes' => 'border-success-ink/25 bg-success-soft text-success-ink', 'icon' => 'check-circle'],
        'error' => ['classes' => 'border-danger-ink/25 bg-danger-soft text-danger-ink', 'icon' => 'x-circle'],
        'warning' => ['classes' => 'border-warning-ink/25 bg-warning-soft text-warning-ink', 'icon' => 'exclamation'],
        'info' => ['classes' => 'border-info-ink/25 bg-info-soft text-info-ink', 'icon' => 'info'],
    ];

    $toasts = collect(['success', 'error', 'warning', 'info'])
        ->mapWithKeys(fn ($type) => [$type => session($type)])
        ->filter()
        ->map(fn ($message, $type) => [
            'type' => $type,
            'message' => $message,
            'classes' => $tones[$type]['classes'],
            'svg' => $icons[$tones[$type]['icon']],
        ])
        ->values()
        ->all();
@endphp

@if ($toasts || $errors->any())
    <div class="pointer-events-none fixed inset-x-0 top-0 z-[70] flex flex-col items-center gap-2 p-4 sm:items-end sm:p-6"
         role="region" aria-label="Notifikasi sistem">
        <template x-for="item in @js($toasts)" :key="item.type">
            <div x-data="{ show: true }"
                 x-show="show"
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-init="setTimeout(() => show = false, {{ (int) config('simrs.toast_duration', 6000) }})"
                 class="toast max-w-md border"
                 :class="item.classes"
                 role="status">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" x-bind:d="item.svg"></path>
                </svg>

                <p class="flex-1 text-sm leading-relaxed font-medium" x-text="item.message"></p>

                <button type="button" x-on:click="show = false" aria-label="Tutup notifikasi"
                        class="-mt-0.5 -mr-1 shrink-0 rounded p-1 opacity-70 transition hover:opacity-100">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </template>

        @if ($errors->any())
            <div class="toast max-w-md border border-danger-ink/25 bg-danger-soft text-danger-ink" role="alert">
                <x-icon name="exclamation" class="h-5 w-5 shrink-0" />
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold">
                        Data gagal disimpan ({{ $errors->count() }} isian perlu diperbaiki)
                    </p>
                    <ul class="mt-1 space-y-0.5 text-xs leading-relaxed">
                        @foreach ($errors->all() as $message)
                            <li class="list-inside list-disc">{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
@endif
