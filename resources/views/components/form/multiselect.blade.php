@props([
    'name' => null,
    'options' => [],
    'selected' => null,
    'id' => null,
])

<x-form.select
    :name="$name"
    :options="$options"
    :selected="$selected"
    :id="$id"
    :placeholder="null"
    {{ $attributes->except(['multiple', 'placeholder']) }}
    multiple
/>
