@props([
    'name' => null,
    'options' => [],
    'selected' => null,
    'id' => null,
    'placeholder' => null,
    'multiple' => false,
])

@php
    $nameAttr = $name;
    $isMultiple = (bool) $multiple;

    $nameForSubmit = $nameAttr;
    if ($isMultiple && $nameForSubmit && ! str_ends_with($nameForSubmit, '[]')) {
        $nameForSubmit .= '[]';
    }

    $nameForOldBase = $nameForSubmit ?? $nameAttr;
    if ($nameForOldBase && str_ends_with($nameForOldBase, '[]')) {
        $nameForOldBase = substr($nameForOldBase, 0, -2);
    }
    $oldKey = $nameForOldBase ? preg_replace('/\]/', '', str_replace('[', '.', $nameForOldBase)) : null;

    $modelSelected = null;
    if (is_null($selected) && $nameAttr && isset($GLOBALS['_form_model']) && ! is_null($GLOBALS['_form_model'])) {
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

    if ($isMultiple) {
        if ($resolvedSelected instanceof \Illuminate\Support\Collection) {
            $selectedList = $resolvedSelected->all();
        } elseif (is_array($resolvedSelected)) {
            $selectedList = $resolvedSelected;
        } elseif ($resolvedSelected === null || $resolvedSelected === '') {
            $selectedList = [];
        } else {
            $selectedList = [$resolvedSelected];
        }
        $selectedValues = array_map('strval', $selectedList);
    } else {
        $selectedValues = [strval($resolvedSelected ?? '')];
    }

    $resolvedId = $id ?: ($nameAttr ? preg_replace('/[^a-zA-Z0-9\-_:.]/', '_', $nameAttr) : null);
    $hasError = $nameAttr && isset($errors) && $errors->has($oldKey ?? $nameAttr);
@endphp

<select
    @if($nameForSubmit) name="{{ $nameForSubmit }}" @endif
    @if(! is_null($resolvedId)) id="{{ $resolvedId }}" @endif
    @if($isMultiple) multiple @endif
    {{ $attributes->merge(['class' => trim('form-control '.($hasError ? 'is-invalid' : ''))]) }}
>
    @if(! is_null($placeholder) && ! $isMultiple)
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
