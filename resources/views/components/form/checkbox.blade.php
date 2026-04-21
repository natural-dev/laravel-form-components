@props([
    'name' => null,
    'value' => 1,
    'checked' => false,
    'id' => null,
    'uncheckedValue' => null,
])

@php
    $nameAttr = $name;
    $oldKey = $nameAttr ? preg_replace('/\]/', '', str_replace('[', '.', $nameAttr)) : null;

    $modelChecked = null;
    if (is_null($checked) && $nameAttr && isset($GLOBALS['_form_model']) && !is_null($GLOBALS['_form_model'])) {
        $model = $GLOBALS['_form_model'];
        $cleanName = preg_replace('/\[.*$/', '', $nameAttr);
        if (method_exists($model, 'getAttribute') || property_exists($model, $cleanName)) {
            try {
                $modelChecked = (bool) $model->getAttribute($cleanName);
            } catch (\Exception $e) {
                $modelChecked = null;
            }
        }
    }

    $oldVal = $oldKey ? old($oldKey) : null;
    $isChecked = !is_null($oldVal)
        ? ((string) $oldVal === (string) $value)
        : ($modelChecked ?? (bool) $checked);

    $resolvedId = $id ?: ($nameAttr ? preg_replace('/[^a-zA-Z0-9\-_:.]/', '_', $nameAttr) : null);
    $hasError = $nameAttr && isset($errors) && $errors->has($oldKey ?? $nameAttr);
@endphp

@if(!is_null($uncheckedValue) && $nameAttr)
    <input type="hidden" name="{{ $nameAttr }}" value="{{ $uncheckedValue }}">
@endif

<input
    type="checkbox"
    @if($nameAttr) name="{{ $nameAttr }}" @endif
    value="{{ $value }}"
    @if(!is_null($resolvedId)) id="{{ $resolvedId }}" @endif
    @if($isChecked) checked @endif
    {{ $attributes->class(['is-invalid' => $hasError]) }}
/>
