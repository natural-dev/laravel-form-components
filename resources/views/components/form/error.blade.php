@props([
    'name' => null,
])

@php
    $nameAttr = $name;
    $oldKey = $nameAttr ? preg_replace('/\]/', '', str_replace('[', '.', $nameAttr)) : null;
    $key = $oldKey ?? $nameAttr;
@endphp

@if($key && $errors->has($key))
    <span class="help-block text-danger">
        <strong>{{ $errors->first($key) }}</strong>
    </span>
@endif
