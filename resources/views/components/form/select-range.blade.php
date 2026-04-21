@props([
    'name' => null,
    'start' => 0,
    'end' => 100,
    'selected' => null,
    'step' => 1,
    'id' => null,
    'placeholder' => null,
])

@php
    $opts = [];
    $s = (int) $start;
    $e = (int) $end;
    $st = max(1, (int) $step);

    if ($s <= $e) {
        for ($i = $s; $i <= $e; $i += $st) $opts[$i] = $i;
    } else {
        for ($i = $s; $i >= $e; $i -= $st) $opts[$i] = $i;
    }
@endphp

<x-form.select :name="$name" :options="$opts" :selected="$selected" :id="$id" :placeholder="$placeholder" {{ $attributes }} />
