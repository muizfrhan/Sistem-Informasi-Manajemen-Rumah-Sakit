@props([
    'action',
    'method' => 'DELETE',
    'title' => 'Hapus data?',
    'message' => 'Tindakan ini tidak dapat dibatalkan.',
    'confirmLabel' => 'Ya, Hapus',
    'permission' => null,
    'icon' => 'trash',
    'label' => 'Hapus',
    'tooltip' => null,
    'variant' => 'ghost',
    'size' => 'sm',
])

{{--
    Aksi destruktif pada satu baris tabel.

    Memakai dialog konfirmasi global yang sudah terpasang sekali di layout,
    jadi tabel dengan banyak baris tidak memuat banyak dialog. Form dikirim
    oleh dialog tersebut, sehingga di sini cukup tombol pemicu.

    `permission` membuat tombol hanya dirender bila pengguna berwenang —
    pengaman tampilan. Route terkait tetap dijaga terpisah di backend lewat
    denyUnless(), jadi menyembunyikan tombol bukan satu-satunya lapisan.
--}}

@php
    $allowed = $permission === null || auth()->user()?->can($permission);
    $tooltip ??= $label;
    $payload = [
        'action' => $action,
        'method' => $method,
        'title' => $title,
        'message' => $message,
        'confirmLabel' => $confirmLabel,
    ];
    $classes = 'inline-flex items-center justify-center rounded-lg p-2 transition'
        .' focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-400'
        .' disabled:pointer-events-none disabled:opacity-50';

    $classes .= match ($variant) {
        'danger' => ' bg-rose-600 text-white hover:bg-rose-700',
        'subtle' => ' text-ink-3 hover:bg-surface-2 hover:text-ink',
        default => ' text-rose-600 hover:bg-rose-50 hover:text-rose-700',
    };

    if ($size === 'sm') {
        $classes = str_replace('p-2', 'p-1.5', $classes);
    }
@endphp

@if ($allowed)
    <button
        type="button"
        class="{{ $classes }}"
        title="{{ $tooltip }}"
        aria-label="{{ $tooltip }}"
        x-on:click="$dispatch('confirm', {{ Illuminate\Support\Js::from($payload) }})"
    >
        <x-icon :name="$icon" class="h-4 w-4" />
        @unless ($size === 'sm')
            <span class="sr-only">{{ $label }}</span>
        @endunless
    </button>
@endif
