@props([
    'empty' => null,
    'emptyMessage' => null,
    'emptyIcon' => 'inbox',
    'stack' => true,
    'minWidth' => '38rem',
    'cols' => null,
])

{{--
    Tabel responsif.

    - Desktop: tabel biasa dengan scroll horizontal bila kolom terlalu banyak.
    - Mobile : setiap <tr> menjadi kartu, label kolom diambil dari
               atribut data-label pada masing-masing <td>.

    Slot: head (isi <thead>), body (isi <tbody>), mobile (opsional,
    konten khusus mobile), empty (konten saat data kosong).
--}}

@php
    $isEmpty = $cols !== null ? $cols === 0 : (bool) ($empty ?? false);
@endphp

<div {{ $attributes->merge(['class' => 'relative']) }}>
    @if ($isEmpty && ! $slot->isEmpty())
        {{ $slot }}
    @elseif ($isEmpty)
        <x-empty-state :title="$empty ?? 'Belum ada data'" :message="$emptyMessage" :icon="$emptyIcon">
            @isset($emptyAction)
                {{ $emptyAction }}
            @endisset
        </x-empty-state>
    @else
        <div class="table-wrap">
            <table class="table {{ $stack ? 'table-stack' : '' }}"
                   @if ($minWidth) style="min-width: {{ $minWidth }}" @endif>
                @isset($head)
                    <thead>{{ $head }}</thead>
                @endisset
                <tbody>{{ $body }}</tbody>
            </table>
        </div>

        @isset($mobile)
            <div class="space-y-2.5 sm:hidden">{{ $mobile }}</div>
        @endisset
    @endif
</div>
