@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'required' => false,
    'placeholder' => null,
    'hint' => null,
    'autocomplete' => null,
    'readonly' => false,
    'disabled' => false,
    'class' => '',
    'prefix' => null,
    'suffix' => null,
])

@php
    $hasError = $errors->has($name);
    $id = $attributes->get('id', $name);
    $describedBy = collect([
        $hint ? $id.'-hint' : null,
        $hasError ? $id.'-error' : null,
    ])->filter()->implode(' ');
@endphp

<div @if ($prefix || $suffix) class="flex items-stretch gap-2" @endif>
    @if ($prefix)
        <span class="flex shrink-0 items-center rounded-lg bg-surface-2 px-3 text-sm text-ink-2 ring-1 ring-line-2 ring-inset">{{ $prefix }}</span>
    @endif

    <div @class(['min-w-0 flex-1' => $prefix || $suffix])>
        @if ($label)
            <label for="{{ $id }}" class="field-label">
                {{ $label }}
                @if ($required)<span class="text-danger-ink" aria-hidden="true">*</span>@endif
                @if ($required)<span class="sr-only">(wajib diisi)</span>@endif
            </label>
        @endif

        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ old($name, $value) }}"
            @if ($required) required @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if ($readonly) readonly @endif
            @if ($disabled) disabled @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($hasError) aria-invalid="true" @endif
            {{ $attributes->except('id')->merge(['class' => 'field-control '.($hasError ? 'field-control-error' : '').' '.$class]) }}
        >

        @if ($hasError)
            <p id="{{ $id }}-error" class="field-error">
                <x-icon name="exclamation" class="h-3.5 w-3.5 shrink-0" />
                <span>{{ $errors->first($name) }}</span>
            </p>
        @elseif ($hint)
            <p id="{{ $id }}-hint" class="field-hint">{{ $hint }}</p>
        @endif
    </div>

    @if ($suffix)
        <span class="flex shrink-0 items-center rounded-lg bg-surface-2 px-3 text-sm text-ink-2 ring-1 ring-line-2 ring-inset">{{ $suffix }}</span>
    @endif
</div>
