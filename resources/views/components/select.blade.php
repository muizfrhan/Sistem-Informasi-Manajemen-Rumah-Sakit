@props(['name', 'label' => null, 'value' => null, 'options' => [], 'placeholder' => 'Pilih...', 'required' => false, 'hint' => null, 'class' => '', 'disabled' => false])

@php
    $hasError = $errors->has($name);
    $current = old($name, $value);
@endphp

<div>
    @if ($label)
        <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-slate-700">
            {{ $label }}
            @if ($required)<span class="text-rose-500">*</span>@endif
        </label>
    @endif

    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if ($required) required @endif
        @if ($disabled) disabled @endif
        @if ($hasError) aria-invalid="true" @endif
        {{ $attributes->merge(['class' => 'block w-full rounded-lg border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6 disabled:bg-slate-100 '.($hasError ? 'ring-rose-400 focus:ring-rose-500' : 'ring-slate-300 focus:ring-teal-600').' '.$class]) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $option)
            @php
                $optionValue = is_array($option) ? $option['value'] : $option;
                $optionLabel = is_array($option) ? $option['label'] : $option;
            @endphp
            <option value="{{ $optionValue }}" @selected((string) $current === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>

    @if ($hasError)
        <p class="mt-1.5 text-xs text-rose-600">{{ $errors->first($name) }}</p>
    @elseif ($hint)
        <p class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
