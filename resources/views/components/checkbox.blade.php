@props(['name', 'label', 'value' => false, 'hint' => null, 'valueTrue' => 1, 'valueFalse' => 0, 'disabled' => false])

@php $checked = (bool) old($name, $value); @endphp

<div class="flex items-start gap-2.5">
    <input type="hidden" name="{{ $name }}" value="{{ $valueFalse }}">
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="checkbox"
        value="{{ $valueTrue }}"
        @checked($checked)
        @if ($disabled) disabled @endif
        {{ $attributes->merge(['class' => 'mt-0.5 h-4 w-4 shrink-0 cursor-pointer rounded border-line-2 text-brand accent-brand focus:ring-2 focus:ring-brand focus:ring-offset-0']) }}
    >
    <label for="{{ $name }}" class="cursor-pointer select-none">
        <span class="block text-sm font-medium text-ink">{{ $label }}</span>
        @if ($hint)<span class="mt-0.5 block text-xs text-ink-3">{{ $hint }}</span>@endif
    </label>
</div>
@error($name)<p class="field-error"><x-icon name="exclamation" class="h-3.5 w-3.5 shrink-0" /><span>{{ $message }}</span></p>@enderror
