@props(['name', 'label' => null, 'value' => null, 'required' => false, 'rows' => 3, 'placeholder' => null, 'hint' => null, 'maxlength' => null])

@php $hasError = $errors->has($name); @endphp

<div>
    @if ($label)
        <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-slate-700">
            {{ $label }}
            @if ($required)<span class="text-rose-500">*</span>@endif
        </label>
    @endif

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($required) required @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($maxlength) maxlength="{{ $maxlength }}" @endif
        @if ($hasError) aria-invalid="true" @endif
        {{ $attributes->merge(['class' => 'block w-full rounded-lg border-0 px-3 py-2 text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6 '.($hasError ? 'ring-rose-400 focus:ring-rose-500' : 'ring-slate-300 focus:ring-teal-600')]) }}
    >{{ old($name, $value) }}</textarea>

    @if ($hasError)
        <p class="mt-1.5 text-xs text-rose-600">{{ $errors->first($name) }}</p>
    @elseif ($hint)
        <p class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
