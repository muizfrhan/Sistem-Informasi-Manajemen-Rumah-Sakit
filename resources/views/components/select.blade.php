@props([
    'name',
    'label' => null,
    'value' => null,
    'options' => [],
    'placeholder' => 'Pilih...',
    'required' => false,
    'hint' => null,
    'class' => '',
    'disabled' => false,
])

@php
    $hasError = $errors->has($name);
    $current = old($name, $value);
    $current = $current instanceof \BackedEnum ? $current->value : $current;
    $id = $attributes->get('id', $name);
    $describedBy = collect([
        $hint ? $id.'-hint' : null,
        $hasError ? $id.'-error' : null,
    ])->filter()->implode(' ');
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="field-label">
            {{ $label }}
            @if ($required)<span class="text-danger-ink" aria-hidden="true">*</span><span class="sr-only">(wajib diisi)</span>@endif
        </label>
    @endif

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        @if ($required) required @endif
        @if ($disabled) disabled @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if ($hasError) aria-invalid="true" @endif
        {{ $attributes->except('id')->merge(['class' => 'field-control '.($hasError ? 'field-control-error' : '').' '.$class]) }}
    >
        @if ($placeholder !== false && $placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $option)
            @php
                $optionValue = is_array($option) ? ($option['value'] ?? '') : (is_object($option) && $option instanceof \BackedEnum ? $option->value : $option);
                $optionLabel = is_array($option) ? ($option['label'] ?? $option['value'] ?? '') : (is_object($option) ? (method_exists($option, 'label') ? $option->label() : $option->name) : $option);
            @endphp
            <option value="{{ $optionValue }}" @selected((string) $current === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>

    @if ($hasError)
        <p id="{{ $id }}-error" class="field-error">
            <x-icon name="exclamation" class="h-3.5 w-3.5 shrink-0" />
            <span>{{ $errors->first($name) }}</span>
        </p>
    @elseif ($hint)
        <p id="{{ $id }}-hint" class="field-hint">{{ $hint }}</p>
    @endif
</div>
