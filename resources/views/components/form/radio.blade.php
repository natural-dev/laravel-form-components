@props([
    'name' => null,
    'value' => null,
    'checked' => false,
    'id' => null,
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
                $modelChecked = ((string) $model->getAttribute($cleanName) === (string) $value);
            } catch (\Exception $e) {
                $modelChecked = null;
            }
        }
    }

    $oldVal = $oldKey ? old($oldKey) : null;
    $isChecked = !is_null($oldVal)
        ? ((string)$oldVal === (string)$value)
        : ($modelChecked ?? (bool)$checked);

    $resolvedId = $id ?: ($nameAttr ? preg_replace('/[^a-zA-Z0-9\-_:.]/', '_', $nameAttr.'_'.$value) : null);
@endphp

<input
    type="radio"
    @if($nameAttr) name="{{ $nameAttr }}" @endif
    @if(!is_null($value)) value="{{ $value }}" @endif
    @if(!is_null($resolvedId)) id="{{ $resolvedId }}" @endif
    @if($isChecked) checked @endif
    {{ $attributes }}
/>
