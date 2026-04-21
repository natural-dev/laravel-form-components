@props([
    'name' => null,
    'id' => null,
])

<x-form.input type="file" :name="$name" :id="$id" {{ $attributes }} />
