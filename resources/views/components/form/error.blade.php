@props([
    'name' => null,
])

@php
    $nameAttr = $name;
    $oldKey = $nameAttr ? preg_replace('/\]/', '', str_replace('[', '.', $nameAttr)) : null;
    $key = $oldKey ?? $nameAttr;
    $errorId = $key ? 'validation-error-'.preg_replace('/[^a-zA-Z0-9_-]/', '_', $key) : null;
@endphp

@if($key && isset($errors) && $errors->has($key))
    <div @if($errorId) id="{{ $errorId }}" @endif class="invalid-feedback d-block" role="alert">
        {{ $errors->first($key) }}
    </div>
@endif
