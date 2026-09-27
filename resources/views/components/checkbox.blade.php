@props(['name', 'label', 'value' => false, 'hint' => null, 'valueTrue' => 1, 'valueFalse' => 0])

<div>
    <label for="{{ $name }}" class="flex items-start gap-2.5">
        <input type="hidden" name="{{ $name }}" value="{{ $valueFalse }}">
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="checkbox"
            value="{{ $valueTrue }}"
            @checked(old($name, $value))
            class="mt-0.5 h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500"
        >
        <span>
            <span class="block text-sm font-medium text-slate-700">{{ $label }}</span>
            @if ($hint)<span class="block text-xs text-slate-500">{{ $hint }}</span>@endif
        </span>
    </label>
    @error($name)<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
</div>
