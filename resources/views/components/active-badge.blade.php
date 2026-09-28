@props(['active' => true, 'label' => null, 'offLabel' => 'Nonaktif', 'onLabel' => 'Aktif'])

{{-- Badge status aktif/nonaktif yang konsisten di seluruh aplikasi. --}}

<span @class([
    'badge ring-1 ring-inset',
    'bg-success-soft text-success-ink ring-success-ink/20' => $active,
    'bg-surface-3 text-ink-3 ring-line-2' => ! $active,
])>
    <span @class([
        'h-1.5 w-1.5 shrink-0 rounded-full',
        'bg-emerald-500' => $active,
        'bg-ink-3' => ! $active,
    ]) aria-hidden="true"></span>
    {{ $label ?? ($active ? 'Aktif' : $offLabel) }}
</span>
