@props(['variant' => 'slate', 'class' => ''])

@php
    $variants = [
        'slate' => 'bg-slate-100 text-slate-700',
        'teal' => 'bg-teal-100 text-teal-800',
        'green' => 'bg-emerald-100 text-emerald-800',
        'amber' => 'bg-amber-100 text-amber-800',
        'red' => 'bg-rose-100 text-rose-800',
        'blue' => 'bg-sky-100 text-sky-800',
        'indigo' => 'bg-indigo-100 text-indigo-800',
        'purple' => 'bg-violet-100 text-violet-800',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium '.($variants[$variant] ?? $variants['slate']).' '.$class]) }}>
    {{ $slot }}
</span>
