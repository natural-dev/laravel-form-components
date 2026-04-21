@props([
    'name' => null,
    'type' => 'text',
    'value' => null,
    'id' => null,
])

@php
    $nameAttr = $name;
    $oldKey = $nameAttr ? preg_replace('/\]/', '', str_replace('[', '.', $nameAttr)) : null;

    $modelValue = null;
    if (is_null($value) && $nameAttr && isset($GLOBALS['_form_model']) && !is_null($GLOBALS['_form_model'])) {
        $model = $GLOBALS['_form_model'];
        $cleanName = preg_replace('/\[.*$/', '', $nameAttr);
        if (method_exists($model, 'getAttribute') || property_exists($model, $cleanName)) {
            try {
                $modelValue = $model->getAttribute($cleanName);
            } catch (\Exception $e) {
                $modelValue = null;
            }
        }
    }

    $resolvedValue = $oldKey ? old($oldKey, $value ?? $modelValue) : ($value ?? $modelValue);

    $resolvedId = $id ?: ($nameAttr ? preg_replace('/[^a-zA-Z0-9\-_:.]/', '_', $nameAttr) : null);

    $hasError = $nameAttr && isset($errors) && $errors->has($oldKey ?? $nameAttr);
    $baseClass = in_array($type, ['checkbox', 'radio'], true) ? null : 'form-control';
@endphp

<input
    @if($nameAttr) name="{{ $nameAttr }}" @endif
    type="{{ $type }}"
    @if(!is_null($resolvedId)) id="{{ $resolvedId }}" @endif
    @if(!is_null($resolvedValue) && !in_array($type, ['password', 'file'], true)) value="{{ $resolvedValue }}" @endif
    {{ $attributes->merge(['class' => trim(($baseClass ? $baseClass.' ' : '').($hasError ? 'is-invalid' : ''))]) }}
/>
