@php
    $flash = [
        'success' => ['bg-emerald-50 text-emerald-800 ring-emerald-200', 'M5 13l4 4L19 7'],
        'error' => ['bg-rose-50 text-rose-800 ring-rose-200', 'M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z'],
        'warning' => ['bg-amber-50 text-amber-800 ring-amber-200', 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        'info' => ['bg-sky-50 text-sky-800 ring-sky-200', 'M13 16h-1v-4h-1m1-4a1 1 0 11-2 0 1 1 0 012 0zM21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];
@endphp

@foreach ($flash as $key => [$classes, $path])
    @if (session()->has($key))
        <div x-data="{ show: true }" x-show="show"
             x-init="setTimeout(() => show = false, 6000)"
             class="mb-4 flex items-start gap-3 rounded-lg px-4 py-3 text-sm ring-1 {{ $classes }}">
            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
            </svg>
            <p class="flex-1">{{ session($key) }}</p>
            <button type="button" @click="show = false" class="shrink-0 opacity-60 hover:opacity-100" aria-label="Tutup">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif
@endforeach

@if ($errors->any())
    <div class="mb-4 rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-800 ring-1 ring-rose-200">
        <p class="font-semibold">Periksa kembali isian berikut ({{ $errors->count() }}):</p>
        <ul class="mt-1.5 list-inside list-disc space-y-0.5">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
