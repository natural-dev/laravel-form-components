# Laravel Form Components

A **Laravel** package that registers **anonymous Blade components** for building forms with named routes, HTTP verbs (`PUT`, `PATCH`, `DELETE`), CSRF, `multipart/form-data`, and values from **`old()`** or a shared **Eloquent model** for the lifetime of the form.

Modeled after the ergonomics of [`laravelcollective/html`](https://github.com/LaravelCollective/html), without facades or PHP helpers: Blade views only, plus a minimal `ServiceProvider`. **This package is intended to replace the `Form` side of Laravel Collective** for new Laravel apps; see [Replacing laravelcollective/html](#replacing-laravelcollectivehtml) at the end of this README, and [Future improvements](#future-improvements) for gaps versus the old builder.

**Repository:** [github.com/natural-dev/laravel-form-components](https://github.com/natural-dev/laravel-form-components)

## Requirements

- PHP **^8.1**
- Laravel / Illuminate **^10.0 | ^11.0 | ^12.0** (`illuminate/support`, `illuminate/view`)

## Installation

### Packagist (recommended once the package is published)

```bash
composer require natural-dev/laravel-form-components
```

### VCS repository (before a stable Packagist release)

In your application `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/natural-dev/laravel-form-components"
        }
    ],
    "require": {
        "natural-dev/laravel-form-components": "dev-main"
    }
}
```

Then:

```bash
composer update natural-dev/laravel-form-components
```

### Local path (package development)

```json
"repositories": [
    { "type": "path", "url": "../laravel-form-components" }
]
```

The service provider is registered via Laravel **package discovery**; you do not need to add it manually to `config/app.php`.

## What the package does

1. **`FormComponentsServiceProvider`** registers an anonymous component path with `Blade::anonymousComponentPath()` so `<x-form.* />` tags resolve to the package views under `resources/views/components/form/`.
2. It can **publish** those views into your app (`resources/views/components/…`) for customization.
3. Child fields read a model stored in **`$GLOBALS['_form_model']`** while the form is open (`<x-form>` or between `<x-form.open>` and `<x-form.close>`).

### Form URL: `action` or `route`

Set the URL with **`action`** (string) or **`route`**:

- `route="route.name"` and `routeParams` (array) for named parameters.
- `:route="['route.name', $id]"` as a shortcut (first element = name, the rest = ordered arguments).

The **`method`** is uppercased internally: for verbs other than `GET`/`POST` the form is submitted as `POST` and `@method(...)` is rendered (standard Laravel behavior). **`files`** adds `enctype="multipart/form-data"`. **`model`** sets `$GLOBALS['_form_model']` until the form closes.

## Quick start

### Slot wrapper (`<x-form>`)

Opens and closes the `<form>` and clears the model at the end.

```blade
<x-form :route="['devices.update', $device]" method="PUT" :model="$device" files>
    <x-form.label for="name" value="Name" />
    <x-form.input name="name" />
    <x-form.error name="name" />

    <x-form.button class="btn btn-primary">Save</x-form.button>
</x-form>
```

### Open / close style

```blade
<x-form.open :route="['devices.store']" method="POST" files />
    <x-form.input name="name" :value="old('name')" />
    <x-form.button class="btn btn-primary">Create</x-form.button>
<x-form.close />
```

## Values: `old()`, props, and model

For **input**, **textarea**, and **select** (and **hidden** / **file** via input), resolution order is:

1. **`old($key)`** when present in the validation session (the key is derived from `name`, turning `foo[bar]` into dot notation like `foo.bar`).
2. If there is no `old()`, the explicit prop (**`value`**, **`selected`**, etc.).
3. If the prop does not set a value and **`:model`** is active, `getAttribute()` is used on the field’s base name (the segment before `[` in nested names).

**Exceptions:** `value` is not filled for **`password`** or **`file`** inputs.

### Checkbox and radio (actual behavior)

- After a validation error, the checked state is inferred by comparing **`old()`** to the field’s **`value`**.
- Reading the **model** for the initial checked state only happens when the **`checked` prop is `null`**. The component default is `false`, so in typical usage **`<x-form.checkbox name="remember" />` does not read the model’s boolean** unless you pass `:checked="null"` to delegate to the model (or rely on `old()` after a failed POST).

**Radio** buttons follow the same rule: the model is used only if `checked` is `null`; otherwise the boolean `checked` prop or `old()` comparison applies.

## Components and props

| Component | Summary |
|-----------|---------|
| `<x-form>` | Slot: full form + `@csrf`, `@method`, closing tag, and model `unset`. |
| `<x-form.open>` | Opens `<form>`; same props as above (does not close). |
| `<x-form.close>` | Closes `</form>` and removes `$GLOBALS['_form_model']`. |
| `<x-form.input>` | `name`, `type` (default `text`), `value`, `id`. Base class `form-control` except for `type` `checkbox`/`radio`. Adds `is-invalid` when the field has errors. |
| `<x-form.hidden>` | Forwards to `<x-form.input type="hidden" />`. |
| `<x-form.file>` | Forwards to `<x-form.input type="file" />`. |
| `<x-form.textarea>` | Body: resolved value or **slot**. |
| `<x-form.select>` | `options` (array value => label), `selected`, `placeholder` (only when not `multiple`), `multiple` via HTML attribute; extra options via **slot** inside `<select>`. |
| `<x-form.select-range>` | Integers from `start` to `end` with `step` (minimum 1); forwards to `select`. |
| `<x-form.checkbox>` | `value` (default `1`), `checked`, `uncheckedValue` (renders a leading `hidden` with that value when unchecked). No automatic `form-control` / `is-invalid`. |
| `<x-form.radio>` | `value`, `checked`; default `id` includes the value to avoid collisions. |
| `<x-form.label>` | `for`, label text in `value` or slot. |
| `<x-form.button>` | `type` defaults to `submit`. |
| `<x-form.error>` | First message for the field (`$errors->first`), with Bootstrap 3–style classes (`help-block text-danger`). |
| `<x-form.errors>` | Renders all messages; optional **`bag`** prop for a specific `MessageBag`. |

## Styling (Bootstrap)

By default, typical **Bootstrap** classes are applied (`form-control`, `is-invalid` on error, `alert alert-danger` for the error list). You can merge extra classes with the `class` attribute on each component (`$attributes->merge()` where implemented).

## Publishing views

```bash
php artisan vendor:publish --tag=form-components
```

Package templates are copied to your app’s `resources/views/components/` (including the `form/` directory). Published copies override the package views.

## Limitations

- **One active model per form:** `$GLOBALS['_form_model']` is global. Do not nest forms or rely on this mechanism for two model-bound forms at once.
- You must provide **`action` or `route`**; if both are missing, the form’s `action` attribute will be empty.
- **Checkbox** / **radio** behavior does not mirror the full Collective API; see the values section above.

## Future improvements

Ideas that would bring the package closer to parity or polish, without committing to a roadmap:

- **Checkbox / radio + model:** align default behavior with intuitive model binding (e.g. treat “prop omitted” differently from `checked="false"`, or document a single supported pattern) so typical `<x-form.checkbox name="agree" />` usage reads booleans from the model without needing `:checked="null"`.
- **Form context without `$GLOBALS`:** pass model state through a stack or view composer so nested or parallel forms are safer and the implementation is easier to reason about.
- **Theming / CSS framework presets:** optional variants (Bootstrap 5, Tailwind-only classes, or unstyled) instead of hard-coded Bootstrap 3–leaning classes on error components.
- **Extension points:** documented patterns or small hooks for app-level “macros” (custom Blade components or published partials that wrap `<x-form.input>`).
- **Collective-style helpers (out of scope today):** anything equivalent to the **`Html`** facade (links, lists, etc.) would be a separate concern or package; forms stay the focus here.
- **Tests in the package:** automated coverage for edge cases (`name` with brackets, `multiple` select, `GET` forms without CSRF, etc.).

Contributions or issues for any of the above are welcome in the repository.

## Replacing laravelcollective/html

This package is meant as a **modern replacement for the form side of [`laravelcollective/html`](https://github.com/LaravelCollective/html)**. That project historically offered **`Form`** (and often **`Html`**) facades and a PHP form builder; **Laravel Form Components** offers the same *workflow*—routes, spoofed methods, CSRF, `old()` values, model-bound fields—in **anonymous Blade components**, with **no facades** and minimal PHP surface (a single service provider).

It is **not a drop-in substitute**: you migrate views from `Form::open()` / `Form::model()` style calls to `<x-form>` / `<x-form.open>` tags. It **does not** replicate the **`Html`** facade, **`Form::macro()`**, or every edge case of the old builder. For typical CRUD forms it is a **lightweight, maintainable alternative** that stays close to Laravel’s native Blade stack.

If you relied on Collective only for forms, this package is the intended successor; if you relied heavily on **`Html`** or macros, plan to keep or reimplement those pieces in your app or in complementary packages.

**In short:** *Laravel Form Components* is a **replacement for `laravelcollective/html` when you only need forms**—same problems (CSRF, method spoofing, `old()`, model values), implemented as Blade components for Laravel 10+ instead of the legacy `Form` facade.

## License

Apache License 2.0 — see [`LICENSE`](LICENSE).
