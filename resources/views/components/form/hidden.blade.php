@props([
    'name' => null,
    'value' => null,
    'id' => null,
])

<x-form.input type="hidden" :name="$name" :value="$value" :id="$id" {{ $attributes }} />
