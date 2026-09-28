@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'submit',
    'icon' => null,
    'permission' => null,
    'confirm' => null,
])

{{--
    Tombol dasar aplikasi.

    `permission` membuat tombol hanya dirender bila pengguna berwenang.
    Ini menggantikan pola `@can(...) <x-button>…</x-button> @endcan` di dalam
    named slot, yang tidak dapat dikompilasi Blade dengan benar. Route
    terkait tetap dilindungi secara terpisah di backend.

    `confirm` menerima payload (action, method, title, message, confirmLabel)
    untuk dialog konfirmasi global. Daftar atribut tombol tidak boleh
    dibungkus kondisi Blade karena Blade tidak dapat mengompilasi direktif
    di dalam daftar atribut sebuah tag komponen.

    Komponen ini tidak membungkus komponen lain sehingga aman dipakai
    di dalam slot mana pun.
--}}

@php
    $allowed = $permission === null || auth()->user()?->can($permission);
    $classes = 'btn btn-'.$size.' btn-'.$variant;
    $iconMarkup = $icon !== null ? \App\Support\Icons::render($icon, 'h-4 w-4 shrink-0') : '';

    $confirmAttributes = [];
    if (is_array($confirm)) {
        $confirmJson = json_encode(
            $confirm,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP,
        );

        $confirmAttributes = ['x-on:click.prevent' => "\$dispatch('confirm', ".$confirmJson.')'];
    }
@endphp

@if ($allowed)
    @if ($href)
        <a href="{{ $href }}" {{ $attributes->merge($confirmAttributes)->merge(['class' => $classes]) }}>
            {!! $iconMarkup !!}{{ $slot }}
        </a>
    @else
        <button type="{{ $type }}" {{ $attributes->merge($confirmAttributes)->merge(['class' => $classes]) }}>
            {!! $iconMarkup !!}{{ $slot }}
        </button>
    @endif
@endif
