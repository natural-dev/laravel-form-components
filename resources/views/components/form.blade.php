@props([
    'action' => null,
    'route' => null,
    'routeParams' => [],
    'method' => 'POST',
    'files' => false,
    'model' => null,
])

@php
    $methodUpper = strtoupper($method ?? 'POST');
    $formMethod = in_array($methodUpper, ['GET', 'POST'], true) ? $methodUpper : 'POST';

    $resolvedAction = $action;
    if (is_null($resolvedAction) && !is_null($route)) {
        if (is_array($route)) {
            $routeName = $route[0];
            $routeArgs = array_slice($route, 1);
            $resolvedAction = route($routeName, $routeArgs);
        } else {
            $resolvedAction = route($route, $routeParams ?? []);
        }
    }

    $enctype = $files ? 'multipart/form-data' : null;

    if (!is_null($model)) {
        $GLOBALS['_form_model'] = $model;
    }
@endphp

<form method="{{ strtolower($formMethod) }}" action="{{ $resolvedAction }}" @if($enctype) enctype="{{ $enctype }}" @endif {{ $attributes }}>
    @if ($formMethod !== 'GET')
        @csrf
    @endif

    @if (!in_array($methodUpper, ['GET', 'POST'], true))
        @method($methodUpper)
    @endif

    {{ $slot }}
</form>

@php
    if (isset($GLOBALS['_form_model'])) {
        unset($GLOBALS['_form_model']);
    }
@endphp
