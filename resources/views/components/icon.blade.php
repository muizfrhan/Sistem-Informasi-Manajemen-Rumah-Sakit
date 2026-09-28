@props(['name', 'class' => 'h-5 w-5'])

{{--
    Komponen ikon. Gunakan `{!! \App\Support\Icons::render($name, $class) !!}`
    bila perlu menyisipkan ikon di dalam komponen lain (slot Blade tidak
    mendukung komponen bersarang).
--}}

{!! \App\Support\Icons::render($name, $class) !!}
