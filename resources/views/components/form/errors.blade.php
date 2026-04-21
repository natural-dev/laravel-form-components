@props([
    'bag' => null,
])

@php
    $bagObj = $bag ? $errors->getBag($bag) : $errors;
@endphp

@if($bagObj->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($bagObj->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
