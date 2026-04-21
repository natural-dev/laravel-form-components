@props([
    'name' => null,
    'options' => [],
    'selected' => null,
    'id' => null,
    'placeholder' => null,
])

@php
    $nameAttr = $name;
    $oldKey = $nameAttr ? preg_replace('/\]/', '', str_replace('[', '.', $nameAttr)) : null;

    $modelSelected = null;
    if (is_null($selected) && $nameAttr && isset($GLOBALS['_form_model']) && !is_null($GLOBALS['_form_model'])) {
        $model = $GLOBALS['_form_model'];
        $cleanName = preg_replace('/\[.*$/', '', $nameAttr);
        if (method_exists($model, 'getAttribute') || property_exists($model, $cleanName)) {
            try {
                $modelSelected = $model->getAttribute($cleanName);
            } catch (\Exception $e) {
                $modelSelected = null;
            }
        }
    }

    $resolvedSelected = $oldKey ? old($oldKey, $selected ?? $modelSelected) : ($selected ?? $modelSelected);

    $resolvedId = $id ?: ($nameAttr ? preg_replace('/[^a-zA-Z0-9\-_:.]/', '_', $nameAttr) : null);
    $hasError = $nameAttr && isset($errors) && $errors->has($oldKey ?? $nameAttr);

    $isMultiple = (string)($attributes->get('multiple') ?? '') !== '';
    $selectedValues = $isMultiple ? (array)($resolvedSelected ?? []) : [$resolvedSelected];
    $selectedValues = array_map('strval', $selectedValues);
@endphp

<select
    @if($nameAttr) name="{{ $nameAttr }}" @endif
    @if(!is_null($resolvedId)) id="{{ $resolvedId }}" @endif
    {{ $attributes->merge(['class' => trim('form-control '.($hasError ? 'is-invalid' : ''))]) }}
>
    @if(!is_null($placeholder) && !$isMultiple)
        <option value="">{{ $placeholder }}</option>
    @endif

    @foreach(($options ?? []) as $optValue => $optLabel)
        @php $optValueStr = (string) $optValue; @endphp
        <option value="{{ $optValueStr }}" @if(in_array($optValueStr, $selectedValues, true)) selected @endif>
            {{ $optLabel }}
        </option>
    @endforeach

    {{ $slot }}
</select>
