@props(['action', 'method' => 'POST', 'confirm' => null, 'label' => 'Hapus', 'variant' => 'danger', 'size' => 'sm'])

<form method="POST" action="{{ $action }}" class="inline"
      @if ($confirm) onsubmit="return confirm(@js($confirm))" @endif>
    @csrf
    @if (strtoupper($method) !== 'POST')<input type="hidden" name="_method" value="{{ $method }}">@endif
    <x-button type="submit" variant="{{ $variant }}" size="{{ $size }}">{{ $label }}</x-button>
</form>
