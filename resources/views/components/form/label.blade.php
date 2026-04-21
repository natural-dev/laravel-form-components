@props([
    'for' => null,
    'value' => null,
])

<label @if($for) for="{{ $for }}" @endif {{ $attributes }}>
    {{ $value ?? $slot }}
</label>
