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

## Styling: Bootstrap defaults and Tailwind CSS

The package is **CSS-framework agnostic** at the API level: you can pass any HTML attributes (including `class`) on every component; Laravel merges them with the component’s internal defaults where `$attributes->merge(['class' => …])` is used.

### Default classes (Bootstrap-oriented)

| Area | Default classes (from the Blade views) |
|------|------------------------------------------|
| Text-like inputs, textarea, select | `form-control`; if the field has a validation error, **`is-invalid`** is appended. |
| Field-level error (`<x-form.error>`) | `help-block text-danger` on a `<span>`, `<strong>` inside. |
| Global error list (`<x-form.errors>`) | `alert alert-danger` on a `<div>`, `<ul>` / `<li>` for each message. |
| Checkbox, radio | No default `form-control` / `is-invalid` (only `{{ $attributes }}`). |
| `<form>`, `<label>`, `<button>` | No opinionated classes unless you add them. |

### Using Tailwind CSS

**Yes, you can use Tailwind** in two complementary ways:

1. **Layer utilities on top of defaults** — Pass Tailwind classes via `class`; they are **merged** with the package defaults (e.g. `class="rounded-lg shadow-sm"` adds to `form-control`). This works when you are fine sharing the DOM with Bootstrap-style class names, or you hide Bootstrap via your own base styles.

2. **Tailwind-only (recommended for greenfield Tailwind apps)** — Run **`php artisan vendor:publish --tag=form-components`** and edit the published copies under `resources/views/components/form/`. Replace `form-control`, `is-invalid`, `alert alert-danger`, and `help-block text-danger` with your Tailwind utility set (for example `block w-full rounded-md border-gray-300 …`, `border-red-500`, `text-sm text-red-600`, etc.). After publishing, the package views are no longer used for those paths.

Optional: use the official [**@tailwindcss/forms**](https://github.com/tailwindlabs/tailwindcss-forms) plugin in your app so native `<input>` / `<select>` / `<textarea>` look consistent when you strip or replace `form-control`.

There is **no runtime Tailwind dependency** in this package; Tailwind lives in your app’s build (Vite, etc.).

## Form URL, method, CSRF, and model

These apply to **`<x-form>`** and **`<x-form.open>`** (same props).

| Prop / attribute | Type / default | Description |
|------------------|----------------|-------------|
| **`action`** | `string\|null`, default `null` | Absolute or relative form `action` URL. If set, **`route` is ignored**. |
| **`route`** | `string\|array\|null`, default `null` | Named Laravel route. **String:** `route('name', routeParams)`. **Array:** first element = route name, remaining elements = ordered URL parameters, e.g. `['devices.update', $device]`. |
| **`routeParams`** | `array`, default `[]` | Used only when **`route`** is a **string**; passed as the second argument to Laravel’s `route()` helper. |
| **`method`** | `string`, default `'POST'` | Logical HTTP method (e.g. `PUT`, `PATCH`, `DELETE`, `GET`). For anything other than `GET` / `POST`, the `<form>` still uses `method="post"` and Laravel’s **`@method(...)`** directive is emitted. Stored uppercased internally for decisions. |
| **`files`** | `bool`, default `false` | When `true`, sets **`enctype="multipart/form-data"`** on the `<form>`. |
| **`model`** | `mixed`, default `null` | When not `null`, assigns **`$GLOBALS['_form_model']`** so child fields can resolve values until the form closes. |
| **`$attributes` / `class`, `id`, `data-*`, etc.** | — | Forwarded onto the `<form>` element (e.g. `class="space-y-4"`, `id`, `novalidate`). |

**`<x-form>`** wraps a **slot** (your fields), outputs the closing `</form>`, then **`unset`s** `$_form_model`. **`<x-form.open>`** only opens the form; **`<x-form.close>`** only closes `</form>` and clears the model.

**`<x-form.close>`** has no props.

You must set **`action` or `route`**; otherwise `action` on the `<form>` can be empty.

## Value resolution: `old()`, props, and model

For **input**, **textarea**, **select** (including **hidden** / **file** via input), order is:

1. **`old($key)`** when the validation session has a value. **`$key`** is derived from **`name`** (bracket names like `foo[bar]` map to dot-style keys like `foo.bar`). Multi-select names ending in **`[]`** use the base name for `old()` (e.g. `roles[]` → `roles`).
2. If no `old()`, the explicit prop (**`value`**, **`selected`**, …).
3. If still unset and **`:model`** is active on the form, **`getAttribute($cleanName)`** on the model (`$cleanName` = segment before `[` in `name`).

**Not filled:** **`value`** is omitted for **`password`** and **`file`** types (so the browser does not prefill secrets or file paths).

### Checkbox and radio

- After validation failure, **checked** state is inferred by comparing **`old()`** to the field’s **`value`**.
- **Model** for the initial checked state is only read when **`checked` is `null`**. The Blade **`@props`** default for **`checked`** is **`false`**, so **omitting `checked` does not read the model**. Use **`:checked="null"`** when you want the model to drive the initial state.

## Component reference

Unless noted, extra HTML attributes go through **`{{ $attributes }}`** (or **`$attributes->merge([...])`** where stated) on the underlying element.

---

### `<x-form>`

| Prop | Default | Description |
|------|---------|-------------|
| `action` | `null` | Form `action` URL. |
| `route` | `null` | Route name string, array `[name, ...params]`, or `null`. |
| `routeParams` | `[]` | Named parameters when `route` is a string. |
| `method` | `'POST'` | Logical method; see table above. |
| `files` | `false` | Multipart upload. |
| `model` | `null` | Bound model for children. |

**Slot:** body of the form (fields, buttons). **Default layout classes:** none on `<form>`.

---

### `<x-form.open>`

Same props as **`<x-form>`**. Does **not** render a closing tag; pair with **`<x-form.close>`**.

---

### `<x-form.close>`

No props. Renders `</form>` and clears **`$_form_model`**.

---

### `<x-form.input>`

| Prop | Default | Description |
|------|---------|-------------|
| `name` | `null` | `name` attribute; drives `old()` / error keys and optional auto **`id`**. |
| `type` | `'text'` | Any HTML input type. For `checkbox` / `radio`, default **`form-control`** / **`is-invalid`** are **not** applied (same as native). |
| `value` | `null` | Explicit value; skipped when `null` so **model** / **`old()`** can apply. Ignored for **`password`** / **`file`** output. |
| `id` | `null` | If omitted and `name` is set, **`id`** is a sanitized copy of **`name`**. |

**`$attributes->merge(['class' => …])`:** default **`form-control`** (+ **`is-invalid`** if errors), merged with your **`class`**.

---

### `<x-form.hidden>`

| Prop | Default | Description |
|------|---------|-------------|
| `name` | `null` | Passed through to **`<x-form.input type="hidden">`**. |
| `value` | `null` | Hidden value. |
| `id` | `null` | Optional `id`. |

Forwards **`{{ $attributes }}`** to **input**.

---

### `<x-form.file>`

| Prop | Default | Description |
|------|---------|-------------|
| `name` | `null` | File input name. |
| `id` | `null` | Optional `id`. |

Forwards to **`type="file"`** input; **no** `value` attribute.

---

### `<x-form.textarea>`

| Prop | Default | Description |
|------|---------|-------------|
| `name` | `null` | `name` attribute. |
| `value` | `null` | Body source when not using slot-only content. |
| `id` | `null` | Auto from `name` if omitted. |

**Content:** inner text is **`$resolvedValue`** if set, otherwise the **slot**. **`$attributes->merge`:** **`form-control`** + **`is-invalid`** when applicable.

---

### `<x-form.select>`

| Prop | Default | Description |
|------|---------|-------------|
| `name` | `null` | Select `name`. When **`multiple`** is true, **`[]`** is appended if the name does not already end with **`[]`**. |
| `options` | `[]` | Associative array **`value => label`** for `<option>` rows. |
| `selected` | `null` | Selected value(s). Single: scalar. **Multiple:** array or **`Collection`** of scalars. |
| `id` | `null` | Auto from `name` if omitted. |
| `placeholder` | `null` | First **`<option value="">`** label; **only when not `multiple`**. |
| `multiple` | `false` | Boolean multi-select. You may also use the boolean HTML attribute **`multiple`** on the tag. |

**Slot:** extra `<option>` elements (or groups) appended after the generated options.

**`$attributes->merge`:** **`form-control`** + **`is-invalid`**. Renders the **`multiple`** attribute when enabled.

---

### `<x-form.multiselect>`

| Prop | Default | Description |
|------|---------|-------------|
| `name` | `null` | Passed to **select** ( **`[]`** appended as needed). |
| `options` | `[]` | Same as select. |
| `selected` | `null` | Array or **`Collection`**. |
| `id` | `null` | Same as select. |

Forwards **`class`** and other attributes except **`multiple`** / **`placeholder`**; **`multiple`** is always on. **`placeholder`** is forced off.

---

### `<x-form.select-range>`

| Prop | Default | Description |
|------|---------|-------------|
| `name` | `null` | Passed to inner **`<x-form.select>`**. |
| `start` | `0` | Range start (integer). |
| `end` | `100` | Range end (integer). |
| `selected` | `null` | Initial selected integer, if any. |
| `step` | `1` | Step size; internally **minimum `1`**. |
| `id` | `null` | Passed through. |
| `placeholder` | `null` | Passed to **select**. |

Builds **`$options`** as **`value => label`** integers from **`start`** to **`end`** (inclusive), stepping up or down. Forwards **`{{ $attributes }}`** to **select** (so you can pass **`multiple`**, **`class`**, etc.).

---

### `<x-form.checkbox>`

| Prop | Default | Description |
|------|---------|-------------|
| `name` | `null` | Checkbox `name`. |
| `value` | `1` | Value submitted when checked. |
| `checked` | `false` | Initial checked state when not using **`old()`** / model. Use **`:checked="null"`** to read **model** (see [Checkbox and radio](#checkbox-and-radio)). |
| `id` | `null` | Auto from `name` if omitted. |
| `uncheckedValue` | `null` | If not `null` and **`name`** is set, a **leading `<input type="hidden">`** with this value is rendered so the key is always posted (unchecked submits hidden value). |

**No** default **`form-control`** / **`is-invalid`**. Raw **`{{ $attributes }}`** on the checkbox input.

---

### `<x-form.radio>`

| Prop | Default | Description |
|------|---------|-------------|
| `name` | `null` | Radio group `name`. |
| `value` | `null` | Option value (optional for HTML, but needed for meaningful groups). |
| `checked` | `false` | Same semantics as checkbox for **`old()`** / model (**`:checked="null"`** for model). |
| `id` | `null` | Default **`id`** includes **`name`** and **`value`** to reduce collisions between radios. |

**No** default Bootstrap field classes; **`{{ $attributes }}`** only.

---

### `<x-form.label>`

| Prop | Default | Description |
|------|---------|-------------|
| `for` | `null` | Target **`id`** of the control. |
| `value` | `null` | Label text if the slot is empty. |

**Slot:** preferred way to pass label text. **`{{ $attributes }}`** on **`<label>`** (e.g. `class`).

---

### `<x-form.button>`

| Prop | Default | Description |
|------|---------|-------------|
| `type` | `'submit'` | Button `type` (`submit`, `button`, `reset`, …). |

**Slot:** button label. **`{{ $attributes }}`** on **`<button>`** (e.g. `class`).

---

### `<x-form.error>`

| Prop | Default | Description |
|------|---------|-------------|
| `name` | `null` | Field name used to resolve the error key (same bracket → dot rules as inputs). |

Renders nothing if **`$errors`** is missing or there is no error for that key. Otherwise a **`<span>`** with **`help-block text-danger`** and **`$errors->first($key)`** inside **`<strong>`**.

---

### `<x-form.errors>`

| Prop | Default | Description |
|------|---------|-------------|
| `bag` | `null` | If set, uses **`$errors->getBag($bag)`** instead of the default bag. |

Renders nothing if **`$errors`** is missing or the chosen bag is empty. Otherwise **`alert alert-danger`** with a **`<ul>`** of all messages.

## Quick examples

### Slot wrapper

```blade
<x-form :route="['devices.update', $device]" method="PUT" :model="$device" files class="space-y-4">
    <x-form.label for="name" value="Name" />
    <x-form.input name="name" class="mt-1 block w-full" />
    <x-form.error name="name" />

    <x-form.button class="rounded bg-indigo-600 px-3 py-2 text-white">Save</x-form.button>
</x-form>
```

### Open / close

```blade
<x-form.open :route="['devices.store']" method="POST" files />
    <x-form.input name="name" :value="old('name')" />
    <x-form.button>Create</x-form.button>
<x-form.close />
```

### Multi-select

```blade
<x-form.multiselect name="roles" :options="$roleOptions" :selected="$user->roles->pluck('id')->all()" />

<x-form.select name="roles" :options="$roleOptions" :selected="$ids" multiple />
```

## Publishing views

```bash
php artisan vendor:publish --tag=form-components
```

Copies all package views to **`resources/views/components/`** in your app (including **`form/`**). Published files override the package.

## Limitations

- **One active model per form:** `$GLOBALS['_form_model']` is global. Do not nest model-bound forms.
- **`action` or `route`** is required for a valid `action` URL.
- **Checkbox / radio** model binding requires **`:checked="null"`**; see [Checkbox and radio](#checkbox-and-radio).

## Future improvements

Ideas that would bring the package closer to parity or polish, without committing to a roadmap:

- **Checkbox / radio + model:** default **`checked`** semantics so typical usage reads the model without **`:checked="null"`**.
- **Form context without `$GLOBALS`:** stack or view-based context for safer nesting.
- **Optional shipped themes:** presets (Bootstrap 5, Tailwind-only snippets) alongside current defaults.
- **Extension points:** documented patterns for app-level “macros” via published partials or wrappers.
- **Collective-style `Html` helpers:** out of scope here; separate package or app code.
- **Automated tests** in this repository (Testbench, view snapshots).

Contributions or issues are welcome in the repository.

## Replacing laravelcollective/html

This package is meant as a **modern replacement for the form side of [`laravelcollective/html`](https://github.com/LaravelCollective/html)**. That project historically offered **`Form`** (and often **`Html`**) facades and a PHP form builder; **Laravel Form Components** offers the same *workflow*—routes, spoofed methods, CSRF, `old()` values, model-bound fields—in **anonymous Blade components**, with **no facades** and minimal PHP surface (a single service provider).

It is **not a drop-in substitute**: you migrate views from `Form::open()` / `Form::model()` style calls to `<x-form>` / `<x-form.open>` tags. It **does not** replicate the **`Html`** facade, **`Form::macro()`**, or every edge case of the old builder. For typical CRUD forms it is a **lightweight, maintainable alternative** that stays close to Laravel’s native Blade stack.

If you relied on Collective only for forms, this package is the intended successor; if you relied heavily on **`Html`** or macros, plan to keep or reimplement those pieces in your app or in complementary packages.

**In short:** *Laravel Form Components* is a **replacement for `laravelcollective/html` when you only need forms**—same problems (CSRF, method spoofing, `old()`, model values), implemented as Blade components for Laravel 10+ instead of the legacy `Form` facade.

## License

Apache License 2.0 — see [`LICENSE`](LICENSE).
