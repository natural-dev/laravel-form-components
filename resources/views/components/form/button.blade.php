@props([
    'type' => 'submit',
])

<button type="{{ $type }}" {{ $attributes }}>
    {{ $slot }}
</button>
