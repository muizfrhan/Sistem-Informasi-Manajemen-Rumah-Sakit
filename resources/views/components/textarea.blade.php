@props([
    'name',
    'label' => null,
    'value' => null,
    'required' => false,
    'rows' => 3,
    'placeholder' => null,
    'hint' => null,
    'maxlength' => null,
    'class' => '',
])

@php
    $hasError = $errors->has($name);
    $id = $attributes->get('id', $name);
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="field-label">
            {{ $label }}
            @if ($required)<span class="text-danger-ink" aria-hidden="true">*</span><span class="sr-only">(wajib diisi)</span>@endif
        </label>
    @endif

    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($required) required @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($maxlength) maxlength="{{ $maxlength }}" @endif
        @if ($hasError) aria-invalid="true" @endif
        @if ($hint) aria-describedby="{{ $id }}-hint" @endif
        {{ $attributes->except('id')->merge(['class' => 'field-control resize-y '.($hasError ? 'field-control-error' : '').' '.$class]) }}
    >{{ old($name, $value) }}</textarea>

    @if ($hasError)
        <p class="field-error">
            <x-icon name="exclamation" class="h-3.5 w-3.5 shrink-0" />
            <span>{{ $errors->first($name) }}</span>
        </p>
    @elseif ($hint)
        <p id="{{ $id }}-hint" class="field-hint">{{ $hint }}</p>
    @endif
</div>
