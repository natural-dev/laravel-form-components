@props([
    'bag' => null,
])

@php
    $bagObj = isset($errors) ? ($bag ? $errors->getBag($bag) : $errors) : null;
@endphp

@if($bagObj && $bagObj->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
            @foreach ($bagObj->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
