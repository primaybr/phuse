# PHUSE Template System

> **v1.2.1** — Syntax overhauled to double-brace `{{variable}}` — see [Migration from v1.2.0](#migration-from-v120) if upgrading.

The PHUSE template engine is a fast, zero-dependency engine with a syntax that is immediately familiar to **Twig** and **Laravel Blade** users.  Single curly braces `{ }` are **never** parsed, so inline CSS rules and JavaScript code pass through the template completely unchanged.

---

## Table of Contents

1. [Quick Reference](#quick-reference)
2. [Why Double Braces?](#why-double-braces)
3. [Variable Output](#variable-output)
4. [Nested / Dot-Notation Access](#nested--dot-notation-access)
5. [Filters](#filters)
6. [Raw HTML Output](#raw-html-output)
7. [Template Comments](#template-comments)
8. [Escaped Output Tag](#escaped-output-tag)
9. [Conditional Statements](#conditional-statements)
10. [Foreach Loops](#foreach-loops)
11. [Numeric For Loops](#numeric-for-loops)
12. [Inline CSS & JavaScript Safety](#inline-css--javascript-safety)
13. [Caching](#caching)
14. [Controller Integration](#controller-integration)
15. [Configuration](#configuration)
16. [Migration from v1.2.0](#migration-from-v120)
17. [Troubleshooting](#troubleshooting)

---

## Quick Reference

| Syntax | Description |
| --- | --- |
| `{{variable}}` | Output a variable |
| `{{user.profile.age}}` | Dot-notation nested access |
| `{{name\|upper}}` | Apply a filter |
| `{{name\|substr:0:1\|upper}}` | Chained filters with params |
| `{!! htmlContent !!}` | Raw / unescaped HTML output |
| `{# comment #}` | Template comment (stripped) |
| `@{{variable}}` | Escaped — outputs literal `{{variable}}` |
| `{% if condition %}…{% endif %}` | Conditional block |
| `{% if … %}…{% else %}…{% endif %}` | If / else |
| `{% foreach items as item %}…{% endforeach %}` | Loop over array |
| `{% for i in 1..10 %}…{% endfor %}` | Numeric range loop |

---

## Why Double Braces?

### The Problem with Single Braces

The old `{variable}` syntax (single curly braces) conflicted with CSS and JavaScript because both languages use `{ }` extensively:

```css
/* ❌ OLD — single-brace parser could corrupt this */
.button {
  background-color: #007bff;
  color: white;
}
```

```javascript
// ❌ OLD — also affected inline JS objects
var config = { debug: true, version: "1.0" };
if (x) { doSomething(); }
```

### The PHUSE v1.2.1 Solution

By switching to `{{variable}}` (double braces), **only `{{ }}` triggers parsing**. Single `{ }` are completely ignored:

```css
/* ✅ NEW — CSS is 100% safe inside templates */
.button {
  background-color: #007bff;
  color: {{primaryColor}};   /* dynamic value still works */
}
```

```javascript
// ✅ NEW — JS objects and control flow are completely safe
var config = { debug: true };
if (x) { doSomething(); }

// Dynamic values from PHP still work:
var apiUrl = "{{apiUrl}}";
var userId = {{userId}};
```

This matches the syntax used by **Twig** (`{{ var }}`) and **Laravel Blade** (`{{ $var }}`), so developers coming from either framework need minimal re-learning.

---

## Variable Output

Use double curly braces to output any scalar variable passed from PHP:

```html
<!-- Template (App/Views/user/profile.php) -->
<h1>Hello, {{name}}!</h1>
<p>Age: {{age}}</p>
<p>City: {{city}}</p>
```

```php
// Controller
$this->render('user/profile', [
    'name' => 'Jane Doe',
    'age'  => 28,
    'city' => 'New York',
]);
```

**Output:**

```html
<h1>Hello, Jane Doe!</h1>
<p>Age: 28</p>
<p>City: New York</p>
```

---

## Nested / Dot-Notation Access

Access nested arrays and objects with dot notation — works to any depth:

```html
<p>{{user.name}}</p>
<p>{{user.profile.city}}</p>
<p>{{user.address.geo.lat}}</p>
```

```php
$data = [
    'user' => [
        'name'    => 'Alice',
        'profile' => ['city' => 'Jakarta'],
        'address' => ['geo' => ['lat' => -6.2]],
    ],
];
```

Works with **arrays**, **objects**, and any combination of the two via `get*()` accessor methods.

---

## Filters

Apply transformations with the pipe operator — identical to Twig syntax:

```html
{{name|upper}}               <!-- ALICE -->
{{name|lower}}               <!-- alice -->
{{name|capitalize}}          <!-- Alice Johnson -->
{{name|trim}}                <!-- removes surrounding whitespace -->
{{name|substr:0:3}}          <!-- Ali  (start=0, length=3) -->
{{name|substr:0:1|upper}}    <!-- A    (chained) -->
{{items|length}}             <!-- 4    (count array items) -->
{{items|count}}              <!-- 4    (alias for length) -->
{{rating|round}}             <!-- 4    -->
{{rating|stars}}             <!-- ★★★★☆ -->
{{date|date:'Y-m-d'}}        <!-- 2024-01-15 -->
{{date|date:'F j, Y'}}       <!-- January 15, 2024 -->
{{date|date:'M d, Y'|upper}} <!-- JAN 15, 2024 (chained) -->
```

### Available Filters

| Filter | Description | Example |
| --- | --- | --- |
| `upper` / `uppercase` | Convert to uppercase | `{{name\|upper}}` |
| `lower` / `lowercase` | Convert to lowercase | `{{name\|lower}}` |
| `capitalize` / `title` | Title-case each word | `{{name\|capitalize}}` |
| `trim` | Remove leading/trailing whitespace | `{{value\|trim}}` |
| `substr:start` | Substring from start | `{{text\|substr:5}}` |
| `substr:start:length` | Substring with length | `{{text\|substr:0:50}}` |
| `length` / `count` | Count array items | `{{items\|length}}` |
| `round` | Round to nearest integer | `{{rating\|round}}` |
| `stars` | Rating to star symbols ★☆ | `{{score\|stars}}` |
| `date:'format'` | Format date string or Unix timestamp | `{{date\|date:'Y-m-d'}}` |

### Filter Chaining

Filters are applied left to right:

```html
<!-- First letter, uppercased -->
{{name|substr:0:1|upper}}

<!-- Trim whitespace, then title-case -->
{{title|trim|capitalize}}

<!-- Format date and uppercase the result -->
{{event_date|date:'M d, Y'|upper}}
```

---

## Raw HTML Output

Use `{!! variable !!}` to output **unescaped HTML**. This is identical to Laravel Blade's `{!! !!}` syntax.

```html
<!-- ⚠️ Only use for trusted content stored in your database -->
<div class="article-body">
    {!! article.body !!}
</div>
```

```php
$data = [
    'article' => [
        'body' => '<p>This is <strong>rich text</strong> stored in the DB.</p>',
    ],
];
```

> **Security note**: Never pass untrusted user input through `{!! !!}`. Use `{{variable}}` for user-supplied content.

---

## Template Comments

Comments are stripped entirely at parse time — they do **not** appear in the rendered HTML, not even as `<!-- HTML comments -->`:

```html
{# This section needs updating — ticket #123 #}
<h1>{{title}}</h1>

{# TODO: add breadcrumb navigation here #}
<p>{{description}}</p>
```

Multi-line comments are supported:

```html
{#
  This entire block is invisible in the final HTML.
  Great for developer notes and TODO items.
#}
```

---

## Escaped Output Tag

Use `@{{variable}}` to output the **literal text** `{{variable}}` — useful when writing template documentation inside a template itself:

```html
<!-- Blade-style escaping -->
<p>To output a name, write: @{{name}}</p>
<p>Your name is: {{name}}</p>
```

```php
$data = ['name' => 'Alice'];
```

**Output:**

```html
<p>To output a name, write: {{name}}</p>
<p>Your name is: Alice</p>
```

---

## Conditional Statements

Use `{% if %}…{% endif %}` — identical to Twig's control flow tags:

```html
{% if logged_in %}
    <p>Welcome back, {{username}}!</p>
{% else %}
    <p>Please <a href="/login">login</a>.</p>
{% endif %}
```

### Comparison Operators

```html
{% if user.role == 'admin' %}   Admin panel
{% endif %}

{% if age >= 18 %}              Adult content
{% endif %}

{% if not premium %}            Upgrade prompt
{% endif %}

{% if items|count > 0 %}        Items list
{% endif %}
```

### Nested Conditionals

Conditionals can be nested to any depth:

```html
{% if logged_in %}
    Welcome, {{username}}!
    {% if user.role == 'admin' %}
        <a href="/admin">Admin Panel</a>
    {% endif %}
{% else %}
    <a href="/login">Login</a>
{% endif %}
```

### Array Truthiness

An array variable evaluates as `true` when non-empty and `false` when empty:

```html
{% if notifications %}
    <span class="badge">{{notifications|count}}</span>
{% else %}
    No notifications.
{% endif %}
```

---

## Foreach Loops

Iterate over arrays with `{% foreach … as … %}`:

```html
<ul>
{% foreach posts as post %}
    <li>
        <strong>{{post.title|capitalize}}</strong>
        — {{post.created_at|date:'M d, Y'}}
        {% if post.published %}
            <span class="badge">Published</span>
        {% else %}
            <span class="badge">Draft</span>
        {% endif %}
    </li>
{% endforeach %}
</ul>
```

```php
$data = [
    'posts' => [
        ['title' => 'first post', 'created_at' => '2024-01-01', 'published' => true],
        ['title' => 'draft post', 'created_at' => '2024-01-10', 'published' => false],
    ],
];
```

### Nested Foreach

```html
{% foreach users as user %}
    <div class="user">
        <h3>{{user.name}}</h3>
        <ul>
        {% foreach user.skills as skill %}
            <li>{{skill}}</li>
        {% endforeach %}
        </ul>
    </div>
{% endforeach %}
```

---

## Numeric For Loops

Use `{% for var in start..end %}` for integer ranges:

```html
<select name="year">
{% for year in 2020..2030 %}
    <option value="{{year}}">{{year}}</option>
{% endfor %}
</select>
```

---

## Inline CSS & JavaScript Safety

This is the core improvement of v1.2.1. With double-brace syntax, **all CSS and JavaScript is completely safe**:

### Inline `<style>` Block

```html
<style>
    /* All CSS rules are safe — { } are never parsed */
    .hero {
        background: {{bgColor}};   /* dynamic value ✅ */
        color: #fff;
    }
    .card {
        border-radius: 0.5rem;
        padding: 1rem;
    }
</style>
```

### Inline `<script>` Block

Variables can be injected into `<script>` blocks using `{{variable}}`.  Plain JavaScript objects and control flow are completely safe:

```html
<script>
    // Plain JS — completely safe ✅
    var config = { debug: false, timeout: 5000 };

    // PHP values injected via {{variable}} ✅
    var apiUrl  = "{{apiUrl}}";
    var userId  = {{userId}};
    var appName = "{{appName}}";

    // JavaScript control flow — safe ✅
    if (config.debug) {
        console.log("Debug mode on");
    }

    function handleResponse(data) {
        if (data.ok) { processResult(data); }
    }
</script>
```

### Inline `style` Attribute

Dynamic values work naturally in `style` attributes:

```html
<div style="background-color: {{themeColor}}; font-size: {{fontSize}}px;">
    {{content}}
</div>
```

### HTML Event Handlers

```html
<button onclick="handleClick({{item.id}}, '{{item.name}}')">
    {{item.label}}
</button>
```

---

## Caching

Enable caching for production performance:

```php
// Enable caching (default: enabled via Config/Template.php)
$this->template->enableCache(true);

// Clear cache manually
$this->template->clearCache();

// Force-clear even if auto-clear is disabled
$this->template->clearCache(true);
```

Configure in `Config/Template.php`:

```php
class Template
{
    public bool $enableCache            = true;
    public int  $cacheLifetime          = 3600;   // 1 hour
    public string $cacheDir             = 'templates';
    public bool $autoClearInDevelopment = false;
}
```

---

## Controller Integration

### Basic Usage

```php
class ArticleController extends Controller
{
    public function show(int $id): void
    {
        $article = $this->model('Article')->find($id);

        $this->render('articles/show', [
            'article' => $article,
            'title'   => $article->title,
        ]);
    }
}
```

### Method Chaining

```php
$html = $this->template
    ->setTemplate('user/profile')
    ->setData([
        'user'  => $user,
        'posts' => $userPosts,
    ])
    ->enableCache(false)
    ->render('', [], true);   // true = return string
```

### Layout Composition

```php
// Render inner content first
$content = $this->template->render('pages/home', $pageData, true);

// Inject into layout
$this->template->render('layouts/main', [
    'title'   => 'Home',
    'content' => $content,
]);
```

```html
<!-- layouts/main.php -->
<!DOCTYPE html>
<html>
<head>
    <title>{{title}}</title>
    <link rel="stylesheet" href="{{assetsUrl}}css/styles.css">
</head>
<body>
    {!! content !!}
</body>
</html>
```

---

## Configuration

### `Config/Template.php`

```php
class Template
{
    /** Enable compiled template caching */
    public bool $enableCache = true;

    /** Cache lifetime in seconds (default: 1 hour) */
    public int $cacheLifetime = 3600;

    /** Cache subdirectory inside Cache/ */
    public string $cacheDir = 'templates';

    /** Auto-clear cache in development mode */
    public bool $autoClearInDevelopment = false;
}
```

---

## Migration from v1.2.0

The only **breaking change** in v1.2.1 is the variable delimiter.

| Old syntax (v1.2.0) | New syntax (v1.2.1) |
| --- | --- |
| `{variable}` | `{{variable}}` |
| `{user.name}` | `{{user.name}}` |
| `{name\|upper}` | `{{name\|upper}}` |
| `{items}…{/items}` (block loop) | `{{items}}…{{/items}}` |
| `{% if %}` | unchanged ✅ |
| `{% foreach %}` | unchanged ✅ |
| `{% for %}` | unchanged ✅ |

### Migration Steps

1. **Find and replace** in all template files (`App/Views/**/*.php`):
   - Replace `{(` with `{{(` — careful not to double-replace
   - A safe approach: `sed -i 's/{\([a-zA-Z_][^}]*\)}/{{​\1}}/g' *.php`

2. **Verify** control flow tags (`{% if %}`, `{% foreach %}`, `{% for %}`) — these are unchanged.

3. **Clear the template cache** after deploying:

   ```php
   $this->template->clearCache(true);
   ```

### Backward Compatibility

The `{% %}` control flow tags are **unchanged** and fully backward-compatible.

---

## Troubleshooting

### Variable not replaced

- Check that the variable name in `{{variable}}` exactly matches the key passed in the data array.
- Nested access requires the full dot path: `{{user.profile.name}}`, not `{{profile.name}}`.
- Use `{# debug: data = … #}` comments during development to annotate expected values.

### Inline style / script looks wrong

- Variables inside `<style>` blocks can only be used in **property values**, not in selectors.
- Variables inside `<script>` blocks are resolved when the block is restored after parsing. Use `{{variable}}` (double braces) — not `{variable}`.

### Cache stale after template change

```php
$this->template->clearCache(true);  // force-clear
```

Or disable caching during development:

```php
$this->template->enableCache(false);
```

### Literal `{{` in output

Use the escaped tag syntax:

```html
@{{variable}}  →  renders as  {{variable}}
```
