# Changelog

## v1.2.6 (2026-07-01)

### CSS Framework

#### Icon System Split (`icons.css`)

The `.pi` / `.pi-*` icon rules (base class, size modifiers, and every icon definition) moved out of `styles.css` into a dedicated `Public/assets/css/icons.css`. `styles.css` now pulls it in with `@import url("icons.css")` at the same location the section used to live, so no consuming project or view needs to change how it loads CSS - every existing `<link href="styles.css">` tag keeps working unchanged.

#### New Icons

25 new icons added, growing the library past 100 icons:

| Class | Description |
| --- | --- |
| `.pi-clipboard` | Clipboard - forms & submissions |
| `.pi-spinner` | Loading spinner |
| `.pi-circle` | Generic hollow circle / fallback icon |
| `.pi-map` | Full map view |
| `.pi-verified` | Verified badge |
| `.pi-shopping-cart` | Shopping cart |
| `.pi-print` | Printer |
| `.pi-play-circle` | Video play button |
| `.pi-minus-circle` | Remove / negative status |
| `.pi-key` | Security / 2FA |
| `.pi-puzzle` | Plugin / module |
| `.pi-package` | Package / bundle |
| `.pi-languages` | i18n / translate |
| `.pi-send` | Send action |
| `.pi-log-in` | Sign in |
| `.pi-log-out` | Sign out |
| `.pi-help-circle` | Help / support |
| `.pi-rss` | RSS feed |
| `.pi-share-2` | Social share |
| `.pi-thumbs-up` | Like / engagement |
| `.pi-flag` | Flag / report |
| `.pi-server` | Server |
| `.pi-cloud` | Cloud |
| `.pi-wrench` | Tools / settings |
| `.pi-building` | Organization |

#### Icon Showcase Sync

`examples/icons` was out of sync with the actual icon inventory - 15 icons added in v1.2.4/v1.2.5 (`archive`, `bars`, `briefcase`, `chart-bar`, `cog`, `history`, `images`, `inbox`, `message`, `mobile`, `monitor`, `palette`, `pencil`, `sparkle`, `video`) were never added to the showcase grid. All 15 are now shown, plus new grids for the v1.2.6 additions above.

## v1.2.5 (2026-06-26)

### Core — Controller Helpers

Four convenience methods added to `Core\Controller` so subclasses no longer need to reach into sub-objects for common actions:

- **`redirect(string $url)`** — delegates to `$this->uri->redirect()` (includes open-redirect protection); declared `never`
- **`json(array $data, int $status = 200)`** — calls `Response::json()` and terminates; declared `never`
- **`isAjax(): bool`** — reads `HTTP_X_REQUESTED_WITH` header via `$this->input->isAjax()`
- **`flash(string $type, string $message)`** — stores `['type', 'message']` in the session flash key for one-request notifications

### Core — HTTP

#### `Response::json()` - Static JSON Terminator

New `Response::json(array $data, int $status = 200): never` sets `Content-Type: application/json`, `http_response_code`, echoes `json_encode($data)`, and calls `exit`. Avoids boilerplate in every API action.

#### `Input::isAjax()`

New method checks `strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'`.

#### `Client::getIpAddress()` — IP Spoofing Fix

`getIpAddress()` previously iterated all proxy forwarding headers (`X-Forwarded-For`, `CF-Connecting-IP`, etc.) and returned the first valid public IP — allowing any client to spoof its IP by setting a forged header. Fixed:

- New `static $trustedProxies = []` — empty by default; call `Client::setTrustedProxies(array $ips)` during bootstrap to register upstream proxy addresses
- Forwarding headers are only inspected when `REMOTE_ADDR` is in `$trustedProxies`
- Falls back unconditionally to `REMOTE_ADDR` otherwise

### Core — Database

#### `Connection::execute()` — Type-Safe PDO Binding

`execute()` previously merged `$this->boundParams` with any caller-passed params and passed them all to `PDOStatement::execute($array)` — PDO treats every value in the array as `PARAM_STR`, silently coercing `null`, booleans, and integers to strings.

Fixed: when params were already bound type-correctly via `arrayBind() → bindValue()`, `execute()` now calls `$statement->execute()` with no arguments, preserving the correct PDO types. Explicit params passed by the caller (e.g. from inline queries) still use `execute($params)` as before.

### Core — Router

Three fixes:

- **FQCN module support** — if the controller string already begins with `App\`, it is used as-is (no namespace prepend). Module controllers registered as `App\Modules\Blog\Controllers\Admin\PostsController` now resolve correctly
- **Route capture type** — matches are cast to `string` via `array_map('strval', $matches)` before dispatch, so UUID captures (which are strings, not ints) arrive correctly typed
- **`is_dir()` guard** — cache directory existence is now checked with `is_dir()` instead of `file_exists()`, which could return true for a stale file at the same path

### Core — Log

`Log::write()` previously called `mkdir()` only after `file_exists($logFile)` returned false — which also returned false when the path pointed to a file (not a directory). Fixed to check `is_dir(Path::LOGS)` before creating the directory.

### Core — Template Parser

#### `findTopLevelElse()` — New Helper

New `protected function findTopLevelElse(string $blockContent): int|false` scans a full `{% if %}...{% endif %}` block and returns the position of the **top-level** `{% else %}` — skipping any `{% else %}` that belongs to a nested `{% if %}`. Previously, `strpos()` was used which would match the first `{% else %}` regardless of nesting depth, corrupting the output of `{% if %}{% if %}{% else %}{% endif %}{% else %}{% endif %}` patterns.

#### `findTopLevelIfBlocks()` — Off-by-one + Else Fix

- `{% endif %}` token is 11 characters, not 12 — offset and substring length corrected throughout
- `{% else %}` boundary now found via `findTopLevelElse()` instead of `strpos()`
- `$elseEnd` computed as `strlen($blockContent) - 11` (the outer `{% endif %}` is always the last token), eliminating the nested `strpos()` that returned the first inner `{% endif %}`

#### Condition Regex Fix

Trailing space removed from the identifier character class in `preg_replace_callback`: was `[a-zA-Z0-9_.|\'\": ]` (space inside brackets could match whitespace), now `[a-zA-Z0-9_.|\'\":]`.

### Core — Upload

`UploadConfig::imageProfile()` preset max size raised from 2 MB to 5 MB to better suit CMS image uploads.

### CSS Framework

#### New Icons (`.pi` system)

Six new icons added:

| Class | Description |
| --- | --- |
| `.pi-bars` | Hamburger / menu icon |
| `.pi-chart-bar` | Bar chart icon |
| `.pi-video` | Video camera icon |
| `.pi-images` | Multiple images / gallery icon |
| `.pi-inbox` | Inbox / tray icon |
| `.pi-cog` | Settings / cog icon |

#### `.vtx-loading` Utility

New `.vtx-loading` class: `opacity: 0.45; pointer-events: none; transition: opacity .12s ease` — use on any element while an async operation is pending.

#### Date / Time Input Styling

Consistent styling for `input[type=date]`, `input[type=datetime-local]`, `input[type=time]`, `input[type=month]`, and `input[type=week]` inside `.form-control`:

- `color-scheme: light` (explicit, prevents OS mismatch)
- Webkit calendar picker indicator: `opacity: 0.5`, pointer cursor, hover to `0.85`
- Dark theme via `[data-theme=dark]` and `prefers-color-scheme: dark` media query: `color-scheme: dark`

---

## v1.2.4 (2026-06-22)

### Database Builder — SQL Injection Security Fixes

Backported security patches from the Vertext CMS database layer to the Phuse query builder.

#### `quoteIdentifier()` — Safe Identifier Quoting

Added a protected `quoteIdentifier(string $field): string` helper to `BuildersTrait`. It sanitizes column and table names by stripping any character that is not `[a-zA-Z0-9_]` and wrapping each part in database-appropriate quote characters:

- **MySQL** (default, backtick): `` `column_name` ``
- **PostgreSQL** (overridden in `PgSQL`): `"column_name"`

Table-qualified identifiers (e.g. `posts.title`) are split on `.` and each segment is quoted individually.

#### `bindValue()` — Parameterized Value Binding

Added a private `bindValue(mixed $value): string` helper to `BuildersTrait`. It registers a value in `$this->binds` under a unique `:qb_N` placeholder and returns the placeholder, ensuring all values flow through PDO prepared-statement binding rather than string interpolation.

#### Methods Fixed — Identifier Injection + Value Injection

All of the following methods previously accepted raw field names and/or values by concatenating them directly into the SQL string. They now call `quoteIdentifier()` for the field argument and `bindValue()` for every value argument:

**`BuildersTrait` (shared baseline):**
`month()`, `year()`, `day()`, `whereJsonContains()`, `dateFormat()`, `jsonExtract()`, `jsonContains()`, `caseWhen()`, `regexp()`, `fullTextSearch()`, `stringAgg()` (separator sanitized via `preg_replace`)

**`MySQL` driver (overrides):**
`dateFormat()`, `fullTextSearch()`, `jsonExtract()`, `jsonContains()`, `groupConcat()` (separator sanitized), `ifNull()`, `caseWhen()`, `regexp()`

**`PgSQL` driver (overrides):**
`quoteIdentifier()` — overrides trait default to use double-quote style;
`insertIgnore()` — overrides trait to emit `ON CONFLICT DO NOTHING` instead of MySQL-only `INSERT IGNORE`;
`dateFormat()`, `fullTextSearch()`, `jsonExtract()` (path sanitized), `jsonExtractPath()` (path parts sanitized), `jsonContains()` (path sanitized), `stringAgg()` (separator sanitized), `coalesce()`, `caseWhen()`, `regexp()`, `arrayContains()`, `ilike()`

#### `orderBy()` — Multi-Column Support

`BuildersTrait::orderBy()` now accumulates clauses instead of overwriting: calling `->orderBy('name', 'ASC')->orderBy('created_at', 'DESC')` produces `ORDER BY name ASC, created_at DESC`.

### ORM / Model

#### `get()` — Single-Record Detection Fix

The `limit == 1` branch previously checked `isset($result['id'])` to detect a single associative row — this broke any table whose primary key was not literally named `id`. The guard is now `empty($result) || !is_array(reset($result))`, which correctly detects any flat associative array regardless of its key names. The same fix is applied to the cache-hit path inside `executeGet()`.

#### `Model::on()` — Shared-Connection Factory

New static factory method `Model::on(Connection $conn, string $table, string $database = 'default'): self` creates a Model instance that reuses an existing `Connection` instead of acquiring one from the pool. Use this to run multiple queries on the same handle inside a transaction:

```php
$conn = $orderModel->db;
$lineModel = Model::on($conn, 'order_lines');
```

The injected connection is never returned to the pool when the instance is destroyed — the original owner remains responsible for it.

#### `withoutTimestamps()`

New fluent method that disables automatic `created_at` / `updated_at` stamping for the current query. Useful for tables that do not have timestamp columns or use non-standard names.

#### `whereRaw()` — Parameterized Raw Conditions

New `whereRaw(string $sql, array $binds = []): self` method for raw WHERE fragments that require explicit bind values — e.g. parenthesised OR groups that `where()` / `orWhere()` cannot express:

```php
->whereRaw('(title ILIKE :s1 OR body ILIKE :s2)', [':s1' => '%foo%', ':s2' => '%foo%'])
```

#### `distinct()` — Deferred Flag

`distinct()` now sets a private `$isDistinct` flag instead of calling `builder->distinct()` immediately. The flag is consumed in `executeGet()` by prepending `DISTINCT` to the SELECT fields string, which avoids double-DISTINCT issues when `select()` is called after `distinct()`.

#### `select()` — Stores Fields

`select()` now stores the resolved field string in `$this->fields` before passing it to the builder, so `executeGet()` picks up the correct column list when DISTINCT is active.

#### `orderBy()` — Default Direction

`Model::orderBy()` now defaults `$order` to `'DESC'` so `->orderBy('created_at')` works without a second argument.

#### `clearQueryCache()` on Write Operations

All three write paths (`save()`, `update()`, `delete()`) now call the new private `clearQueryCache()` helper immediately after a successful execute, ensuring stale SELECT cache entries are invalidated after any data change.

### CSS — New Icons + Dark Mode Refinement

#### New Icons (`.pi` system)

Four new icons added to the CSS icon system:

| Class | Description |
| --- | --- |
| `.pi-pencil` | Edit / pencil icon |
| `.pi-archive` | Archive box icon |
| `.pi-message` | Chat / message bubble |
| `.pi-sparkle` | Sparkle / AI indicator |

#### Dark Mode Palette Refresh

Dark mode CSS variables (`[data-theme=dark]` and the `prefers-color-scheme: dark` media query fallback) updated to a darker, less blue-shifted palette for improved visual comfort:

- Background levels: `#0F172A` / `#1E293B` / `#334155` → `#14171e` / `#191c26` / `#1f2330`
- Text: `#F1F5F9` / `#94A3B8` / `#64748B` → `#dce2f0` / `#8b93ac` / `#525c74`
- Borders: `#334155` / `#475569` → `#22273a` / `#2d3347`
- Primary light: `#1E3A8A` → `#162050`

---

## v1.2.3 (2026-05-24)

### JavaScript Components - Complete Overhaul

Full rewrite of `Public/assets/js/scripts.js`. All components are now **static methods** on the `Phuse` class (not constructors) and use a shared **WeakMap state store** to persist data across multiple handler calls on the same element.

#### WeakMap State Store

```js
static _store = new WeakMap();
static _get(el)       { return this._store.get(el) || {}; }
static _set(el, data) { this._store.set(el, { ...this._get(el), ...data }); }
```

Prevents stale closure data and carousel index resets on every click.

#### Carousel

- Persistent slide index stored per element via WeakMap - clicking prev/next no longer resets to slide 0
- `goTo(index)`, `next()`, `prev()` API
- Indicator dots stay in sync with active slide
- Carousel arrow icons added to CSS (SVG `background-image` on `.carousel-control-prev-icon` / `.carousel-control-next-icon`)

#### Offcanvas

- Dynamic `#phuse-offcanvas-backdrop` created on `show()`, removed on `hide()`
- Fade-in/out via `opacity` transition
- Body scroll locked (`overflow: hidden`) while open
- `data-toggle="offcanvas"` + `data-target="#id"` on trigger; `data-dismiss="offcanvas"` on close button

#### Popover

- `e.stopPropagation()` prevents the document listener from closing the popover immediately on open
- `data-popover-open` attribute tracks open state
- Closes on outside click via document listener
- Positioned below trigger with off-screen width measurement

#### Tooltip

- Fixed event delegation: `mouseenter`/`mouseleave` do not bubble - switched to `mouseover`/`mouseout`
- **Color variants**: reads trigger button's class (`btn-danger`, `btn-success`, etc.) and applies `tooltip-{variant}` class - matching CSS variants set the tooltip background color
- `data-placement` attribute (top/bottom/left/right)
- Saves/restores native `title` attribute to suppress browser native tooltip

#### Toast

- Fixed top-right `#phuse-toast-container` (`position:fixed; top:1rem; right:1rem; z-index:9999`)
- Four types: `success`, `error`, `warning`, `info` - all with white text and pi icon in header
- Slide-in animation (`translateX(110%)` → `translateX(0)`)
- Auto-dismiss after 4 seconds; `×` close button removes toast immediately
- `Phuse.toast(message, type, duration)` static API

#### Accordion

- Correct DOM traversal: `button.closest('.accordion-item').querySelector('.accordion-body')`
- `collapsed` class controls arrow rotation and header color via CSS
- `open()` restores inline padding before animating `max-height`; `close()` zeros padding alongside `max-height` to prevent border-box bleed-through
- CSS: `.accordion-body` gets `overflow: hidden; transition: max-height .3s ease`
- Initially-collapsed bodies require `style="max-height:0;padding-top:0;padding-bottom:0;overflow:hidden;"`

#### Modal (new - full implementation)

Previously a bare show/hide. Now a complete implementation:

- Dynamic `#phuse-modal-backdrop` with `.fade` → `.show` opacity transition
- Body scroll locked while open
- **Click-outside close**: click on `.modal` overlay (not `.modal-dialog`) hides the modal
- **Escape key**: `keydown` listener attached on `show()`, removed on `hide()`
- **Static backdrop**: `data-backdrop="static"` disables both click-outside and Escape key
- Three modal sizes via class on `.modal`: default (560 px), `.modal-lg` (800 px), `.modal-xl` (1140 px), `.modal-sm` (300 px)
- Backdrop element removed from DOM after closing animation

```html
<!-- Basic -->
<div class="modal fade" id="my-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Title</h5>
        <button class="btn-close" data-dismiss="modal"><i class="pi pi-x"></i></button>
      </div>
      <div class="modal-body">Content here.</div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button class="btn btn-primary">Confirm</button>
      </div>
    </div>
  </div>
</div>

<!-- Static backdrop (no Esc / click-outside) -->
<div class="modal fade" data-backdrop="static" ...>...</div>
```

#### Tabs

- Removed deprecated global `event` object - `show(targetId, triggerEl)` now receives the trigger element directly from the click handler

#### ScrollSpy

- Removed erroneous `new` keyword - `Phuse.scrollSpy(el)` is a static method, not a constructor
- Listens on the scrollable element itself (not `window`)
- Queries nav links from `data-target` nav element

### CSS Fixes

- **Carousel arrows**: Added SVG `background-image` to `.carousel-control-prev-icon` and `.carousel-control-next-icon`
- **Accordion body**: Added `overflow: hidden; transition: max-height .3s ease` to `.accordion-body`
- **Tooltip variants**: New CSS rules `.tooltip-primary`, `.tooltip-success`, `.tooltip-danger`, `.tooltip-warning`, `.tooltip-info` - each sets `.tooltip-inner { background-color: … }`
- **Global `.btn-close`**: Replaced scoped `.alert .btn-close` with a global rule (`display:inline-flex`, `width/height:1.75rem`, `border-radius`, hover background); scoped rule retained only for `position:absolute` in alerts. All `btn-close` buttons now contain `<i class="pi pi-x"></i>`
- **Toast info text**: Changed info toast text from `#000` to `#fff`

### Template Parser Fixes

- **`parseRawOutput()`**: Returns the original match when resolved value is `null`, array, or object - prevents `Array to string conversion` warning
- **`parseNestedProperties()`**: Same guard before `(string)` cast
- **`resolveExpressionValue()`**: Returns empty string when value is array or object

### New Assets

- `Public/assets/images/headphones.svg` - flat SVG headphone illustration for product page placeholder
- Asset version query strings (`?v=…`) added to all view files for reliable cache-busting

### Components Page (`/examples/components`)

Complete rewrite of `App/Views/examples/components.php`:

- Ten sections: Alert, Button, Carousel, Modal, Offcanvas, Popover, Tooltip, ScrollSpy, Toast, Accordion
- Pi icons on all section headings
- `<pre class="code-block">` snippets for every component
- All `btn-close` elements use `<i class="pi pi-x"></i>`
- Proper escaped HTML in code snippets (no template substitution inside `<pre>`)

### Emoji Removal

All remaining emoji characters removed from view files and controllers; replaced with `<i class="pi pi-…">` icon elements.

---

## v1.2.2 (2026-02-24)

### CSS Framework - Flat/Modern Rewrite + Icon System

Complete overhaul of `Public/assets/css/styles.css` with a flat, professional, light-first design system.

#### Design System

- **Design tokens**: All CSS custom properties use the `--ps-` prefix (Phuse System). Light-first with full dark mode via `[data-theme=dark]` attribute.
- **Color palette**: Primary `#2563EB`, Success `#16A34A`, Danger `#DC2626`, Warning `#D97706`, Info `#0891B2`
- **Flat aesthetic**: No gradients, minimal shadows, clean 1px borders, `border-radius: 6px`, system font stack

#### Icon System (`.pi`)

New CSS-based hollow SVG icon system - no icon fonts, no external files, no extra HTTP requests.

**50+ icons** covering: navigation, users, files, status, dev tools, time & location.

```html
<!-- Two-class pattern: base + icon name -->
<i class="pi pi-home"></i>
<i class="pi pi-check text-success"></i>
<i class="pi pi-download me-1"></i>Download

<!-- Size utilities -->
<i class="pi pi-star pi-2x"></i>   <!-- 2rem -->
<i class="pi pi-star pi-lg"></i>   <!-- 1.25rem -->
```

**How it works**: Each `.pi-name` class applies a URL-encoded inline SVG via `mask-image`. The `background-color: currentColor` provides the actual rendered color, so icons inherit their color from the surrounding text.

Available sizes: `pi-xs`, `pi-sm`, `pi-lg`, `pi-xl`, `pi-2x`, `pi-3x`, `pi-4x`

#### Template Engine - `protectHtmlBlocks` Removed

- Removed `protectHtmlBlocks()` / `restoreHtmlBlocks()` from `parseTemplate()` pipeline - no longer needed since double-brace `{{}}` syntax never conflicts with CSS/JS single `{}`.
- This eliminates the `___PROTECTED_CODE_0___` placeholder bug caused by nested `<code>` inside `<pre>` blocks.
- Parser pipeline simplified from 12 steps to 8 steps.
- **`parseForeach()` fix**: Added `parseRawOutput()` call inside loop iterations so `{!! var !!}` works correctly inside `{% foreach %}` blocks.

#### New Example: Icon System

- Added `App/Views/examples/icons.php` - live demonstration of all icons, sizes, colors, and button usage
- Accessible at `/examples/icons`

#### Inline Assets Example Fix

- `App/Views/examples/inline_assets.php` - all template syntax shown as code examples now correctly uses HTML entities (`&#123;&#123;var&#125;&#125;`) to prevent parser substitution.
- Added `userName` to `inlineAssets()` controller data for live demo completeness.

### Tests

- All 44 existing tests continue to pass (no breaking changes to parser API)

---

## v1.2.1 (2026-05-22)

### Core/Template - Double-Brace Syntax Overhaul

**Breaking change**: Variable placeholders changed from single `{variable}` to double `{{variable}}`, matching the syntax used by **Twig** and **Laravel Blade**. Control-flow tags (`{% if %}`, `{% foreach %}`, `{% for %}`) are unchanged.

#### Why

Single curly braces conflicted with inline CSS (`.class { color: red; }`) and inline JavaScript (`var obj = { key: val };`), corrupting templates that contained styles or scripts. Double braces eliminate all conflicts - only `{{ }}` triggers parsing.

#### New variable syntax

- **`{{variable}}`** - scalar variable output (replaces `{variable}`)
- **`{{user.profile.age}}`** - dot-notation nested access (replaces `{user.profile.age}`)
- **`{{name|upper}}`** - filter (replaces `{name|upper}`)
- **`{{name|substr:0:1|upper}}`** - chained filters with params (unchanged behaviour, new delimiter)

#### New v1.2.1 syntax additions

- **`{!! variable !!}`** - Raw / unescaped HTML output (Laravel Blade parity). Use for trusted rich-text content only.
- **`{# comment #}`** - Template comments stripped entirely from output (Twig parity). Supports multi-line.
- **`@{{variable}}`** - Escaped output tag - renders as the literal text `{{variable}}` without substitution (Blade `@{{ }}` parity).

#### Parser pipeline improvements

- `parseComments()` - strips `{# … #}` blocks before any other processing
- `parseRawOutput()` - resolves `{!! var !!}` expressions
- `parseEscapedSyntax()` / `restoreEscapedSyntax()` - protects `@{{…}}` blocks so they survive the full pipeline
- `parseArray()` - block-style array loop updated to `{{var}}…{{/var}}` delimiter; removed the old `str_replace(['{','}'],'',…)` hack that could corrupt CSS/JS inside loop bodies
- `restoreHtmlBlocks()` - updated to resolve `{{key}}` (was `{key}`) in `<script>` blocks on restore
- `parseForeach()` / `processNestedForeach()` - loop variable replacements now use `{{loopVar}}` keys
- `parseFor()` - numeric loop replacement updated to `{{i}}`
- All filter and nested-property regexes updated to match `{{…}}` double-brace delimiters

#### Inline CSS & JavaScript safety (the core fix)

```html
<style>
  /* ✅ CSS rules with { } are 100% safe - never parsed */
  .btn { color: red; border-radius: 0.25rem; }
  .hero { background: {{bgColor}}; }   /* dynamic value still works */
</style>

<script>
  // ✅ JS objects and control flow are completely safe
  var cfg = { debug: false };
  if (cfg.debug) { console.log("ok"); }

  // ✅ Inject PHP values with {{variable}}
  var apiUrl = "{{apiUrl}}";
</script>
```

#### Migration from v1.2.0

| Old | New |
| --- | --- |
| `{variable}` | `{{variable}}` |
| `{user.name}` | `{{user.name}}` |
| `{name\|upper}` | `{{name\|upper}}` |
| `{items}…{/items}` | `{{items}}…{{/items}}` |
| `{% if %}` | unchanged |
| `{% foreach %}` | unchanged |

### New example: Inline CSS & JS Safety

- Added `App/Views/examples/inline_assets.php` - live demonstration of single-brace CSS/JS safety, new syntax features, and dynamic value injection into style attributes and script blocks
- Accessible at `/examples/inline-assets`

### Documentation

- `docs/template-system.md` fully rewritten for v1.2.1 syntax with Twig/Blade comparison table, migration guide, inline CSS/JS safety section, and troubleshooting

### Test Suite

- `tests/Core/TemplateTest.php` updated - all template strings use `{{variable}}` syntax
- New test cases: inline CSS preservation, inline JS preservation, JS variable injection inside `<script>` tags, `{# comment #}` stripping, `{!! raw !!}` output, `@{{escaped}}` literal output, filter chaining, numeric for loop

---

## v1.2.0a (2026-05-18)

### Core/Http/Request.php

- **`extractResponseCode()` Scope Fix**: `$http_response_header` is a PHP local variable set only in the scope where `fopen()` runs - it was never accessible inside `extractResponseCode()`, causing it to always return the fallback `200`. The headers array is now passed as a parameter from the calling scope. This was the root cause of all HTTP response code detection being broken (401 checks, token refresh triggering, CMS token expiry detection)
- **`refreshRequest()` - `json_decode` fix**: Session token (`sesstoken`) is stored as a JSON string; the method was accessing it as an object without decoding first, causing property lookups to always fail and the refresh to throw before even attempting
- **`refreshRequest()` - refresh body fix**: The refresh endpoint receives the full token JSON (matching `Token.php`), not just `{"refresh_token":"..."}` - only `access_token` presence is now required before attempting the call
- **`updateSessionWithNewToken()` - session storage fix**: New token is now stored as `json_encode()`d string; previously stored as a raw PHP object, which broke subsequent JSON decoding of the session token

### Core/Http/URI.php

- **`redirect()` relative path support**: Method no longer rejects non-absolute URLs - relative paths are resolved to absolute URLs using the current scheme and host before validation, allowing controller redirects like `redirect('/admin/login')` to work correctly
- **`redirect()` loopback allowance**: Added explicit `$isLoopback` check so redirects to `127.*` / `::1` addresses are permitted (previously blocked by the private IP filter)

### Core/Template/ParserTrait.php

- **`restoreHtmlBlocks()` script variable substitution**: Protected `<script>` blocks are now processed for template variable substitution on restore - variables like `{adminUrl}`, `{apiUrl}` inside `<script>` tags now resolve correctly instead of being left as literal placeholders

---

## v1.2.0 (2026-05-15)

### Database Layer

- **Critical Query Parameter Fix**: Rewrote parameter binding in `BuildersTrait` to use unique placeholder names (`param_N`, `where_N`) - eliminates bind conflicts when the same column appears in multiple clauses or queries
- **`!=` Operator Support**: Added `!=` to the comparison operators list in query builders
- **PostgreSQL Driver Overhaul**: `PgSQL` now has its own `compile()` and `resetQuery()` implementations; `resetQuery()` also clears the binds array to prevent cross-query parameter leakage
- **Connection Tracking**: `Connection` now tracks bound parameters internally via `$boundParams`; `arrayBind()` correctly handles colon-prefixed parameter keys; `execute()` merges tracked and passed params
- **UUID Primary Key Support**: `save()` return type widened to `int|string|bool`; PostgreSQL `RETURNING` clause properly fetches string UUIDs without casting to int
- **Double-Execution Fix**: `Connection::single()` no longer re-executes a statement that already ran (fixes INSERT + RETURNING flow)
- **Build Safety**: `Model::build()` now ensures the `FROM` clause is always set before `compile()` is called
- **Bind Lifecycle Fix**: `save()` and `build()` capture binds before `compile()` resets them; `resetBoundParams()` is called after each query to prevent accumulation
- **`whereNull` / `whereNotNull` Fix**: Both now call `whereQuery()` instead of `where()` to avoid unintended parameter binding

### ORM / Model (v1.2.0)

- **Audit Fields**: Added `created_by`, `updated_by`, `deleted_by` column support with configurable nullable column properties (`createdByColumn`, `updatedByColumn`, `deletedByColumn`)
- **`setCurrentUser()` / `getCurrentUser()`**: New methods to set the current user ID for automatic audit trail population on insert and update
- **`primaryKey` Visibility**: Changed from `public` to `protected` to prevent accidental external mutation
- **Nullable Timestamp Columns**: `createdAtColumn`, `updatedAtColumn`, `deletedAtColumn` are now nullable - set to `null` to disable individual timestamp fields
- **`where()` Smart Detection**: Operator/value swap now only triggers when the second argument is an actual SQL operator string, preventing false positives with UUID values
- **Debug Properties**: Added `lastDebugQuery` and `lastDebugBinds` public properties for error reporting without echoing to output
- **Detailed Error Logging**: PDOException code, message, file, and line are now logged on save/query failure; update errors include the SQL and bound params

### Template System

- **Filter Chaining**: Filters can now be chained with `|` - e.g. `{name|substr:0:1|upper}`
- **Parameterized Filters**: Filters now accept colon-delimited parameters with quoted string support - e.g. `{date|date:'M d, Y'}`
- **New `substr` Filter**: `{variable|substr:start:length}` for substring extraction
- **New `date` Filter**: `{variable|date:'Y-m-d'}` supporting both Unix timestamps and date strings
- **Nested `{% if %}` Block Support**: Replaced regex-based if parsing with a proper nesting-aware parser (`parseNestedIfBlocks` / `findTopLevelIfBlocks`) - nested conditionals no longer break the outer block
- **`{% else %}` in Loop Conditionals**: `{% if %}...{% else %}...{% endif %}` blocks inside `{% foreach %}` loops now correctly render the else branch
- **Filter Order Fix**: Filters are now parsed after `{% foreach %}` processing using `$this->data`, ensuring loop variables are available to filters
- **Filters Inside Loops**: Filters are now also applied to content inside foreach loop iterations
- **Condition Filter Support**: Condition evaluation now resolves `variable|count` expressions and handles arrays (non-empty = `true`) and objects in conditions

### Utilities

- **`Str` Formatting Methods**: Added seven new static methods to `Core\Utilities\Text\Str`:
  - `formatBytes(int $bytes, int $precision)` - human-readable file sizes (B/KB/MB/GB/TB)
  - `formatNumber($number, int $decimals)` - thousands-separated number formatting
  - `formatCurrency($amount, string $currency, int $decimals)` - currency with symbol
  - `formatPercentage($value, int $decimals)` - percentage formatting
  - `formatDatetime($datetime, string $format)` - flexible datetime formatting
  - `slug(string $text)` - URL-safe slug generation
  - `formatPhone(string $phone, string $format)` - configurable phone number formatting

### Core / Config

- **Lazy URI Loading**: `Config` no longer instantiates `URI` in the constructor; it is created on demand via `getURI()` - prevents HTTP context errors during CLI or early bootstrap
- **Config Validation**: Added check that the config file returns an array; throws a clear exception if not
- **PHP Compatibility**: Removed `readonly` from `Config` properties for PHP 8.2/8.3 cross-version compatibility

### Session

- **Idempotent Initialization**: `Session` constructor and `ensureSessionStarted()` now check `session_status()` before calling `session_start()` - eliminates "session already active" warnings in environments that start sessions early
- **Fallback Save Path**: When the configured session save path is missing or not writable, the session falls back to a writable subdirectory under `sys_get_temp_dir()`

### Cache

- **Named Cache Directories**: `FileCache` now accepts a `cacheType` constructor argument passed from `CacheManager`, so each named cache (`query`, `templates`, etc.) writes to its own subdirectory
- **`CacheConfig` Key Fix**: Corrected subdirectory key `'template'` → `'templates'` to match the rest of the framework
- **`FileCache::clear()` Fix**: Fixed inverted deletion condition and switched to `DIRECTORY_SEPARATOR` for cross-platform path correctness; deleted file count is now accurate

### HTTP

- **`Input::post()` Array Fix**: When a POST value is itself an array, `post()` now calls `sanitizeArray()` instead of `sanitize()`, preventing a type error on multi-value fields (e.g. checkboxes)

---

## v1.1.6 (2026-03-11)

- **Cross-Platform Routing Compatibility**: Enhanced routing system to support both domain-based and subdirectory-based access patterns
  - **Dynamic URL Generation**: Automatic detection of access type (domain vs localhost/subdirectory)
  - **Windows/Linux Compatibility**: Proper handling of directory separators across different operating systems
  - **Asset URL Generation**: Correct asset path generation for both access methods
  - **Template Variable Consistency**: Unified baseUrl and assetsUrl handling across controllers
  - **Router Pattern Matching**: Fixed regex patterns to work with both access types
  - **URL Separator Constants**: Added URL_SEPARATOR constant for consistent URL handling
  - **Development Environment**: Seamless switching between local development and production deployments
  - **Backward Compatibility**: All existing localhost/subdirectory installations continue to work
  - **Performance**: Optimized URL generation with minimal overhead
  - **Documentation**: Updated routing documentation with cross-platform examples

## v1.1.5 (2025-11-19)

- **Core Http URI**: Updated core URI handling for local development
- **Core Parser**: Enhanced parsing tag for `<script>` elements
- **Theme Variable Integration**: Integrated all Bootstrap 5.3.8 CSS variables with Phuse-specific `--ps-` prefix
  - **Automatic Theme Switching**: Support for `data-theme="dark"` and `datas-theme="light"` attributes
  - **Dark Theme variables**: Complete dark theme variable set with optimized colors
  - **Light Theme Variables**: Complete light theme variable set with optimized colors
  - **Semantic Color System**: Full semantic color palette (primary, secondary, success, info, warning, danger)
  - **Component Compatibility**: All existing Phuse components now support Bootstrap theme switching
  - **Performance Optimized**: CSS variables for efficient runtime theme customization
  - **Documentation Updated**: Complete CSS framework documentation with theme system integration examples
- **Theme Switching Documentation**: Comprehensive documentation for Bootstrap theme system integration
  - **Theme Switching Examples**: HTML and JavaScript examples for dynamic theme switching

## v1.1.4 (2025-11-17)

- **Complete Bootstrap JavaScript Components Integration**: Full Bootstrap 5.3.8 JavaScript compatibility with Phuse-specific implementations
  - **Alert Component**: Dismissible alert notifications with fade animations and auto-cleanup
    - Supports all Bootstrap alert types (primary, success, warning, danger, info)
    - Click-to-dismiss functionality with smooth animations
    - Automatic alert element removal for memory efficiency
  - **Button Toggle Component**: Interactive button states with checkbox/radio synchronization
    - Active/inactive state toggling via data attributes
    - Form input synchronization for proper form handling
    - Bootstrap-compatible button groups and toolbars
  - **Carousel Component**: Image/media slider with full Bootstrap 5 features
    - Slide navigation with next/previous controls
    - Indicator dots for direct slide access
    - Automatic slide transitions and smooth animations
    - Keyboard navigation and touch support foundation
  - **Offcanvas Component**: Sliding sidebar panels with multiple positioning options
    - Flexible positioning (top, bottom, left, right)
    - Backdrop overlay with click-outside-to-close
    - Smooth slide animations and mobile-optimized sizing
    - Accessibility features with proper ARIA attributes
  - **Popover Component**: Rich content overlays triggered by clicks
    - Customizable title and content via data attributes
    - Smart positioning with viewport awareness
    - Click-outside-to-hide functionality
    - Fade animations with proper cleanup
  - **ScrollSpy Component**: Navigation highlighting based on scroll position
    - Automatic active link updates during scrolling
    - Configurable scroll offset for precision targeting
    - Smooth navigation integration with hash links
    - Performance-optimized scroll event handling
  - **Tooltip Component**: Hover-activated information displays
    - Multiple placement options (top, bottom, left, right)
    - Automatic positioning with collision detection
    - Lightweight implementation without external dependencies
    - Accessible tooltip experience with proper ARIA labels
- **Enhanced Modal System**: Improved modal functionality with better UX
  - Enhanced focus management and accessibility
  - Better backdrop handling and body scroll prevention
  - Improved modal stacking and z-index management
  - Cross-browser compatibility improvements
- **Complete Event Delegation System**: All components use Phuse's optimized event delegation
  - Efficient event binding with automatic cleanup
  - Dynamic component initialization
  - Memory leak prevention through proper event management
  - Performance-optimized selector matching
- **CSS Framework Enhancements**: Supporting styles for new JavaScript components
  - Complete carousel styles with controls and indicators
  - Offcanvas positioning with backdrop support
  - Tooltip and popover positioning with arrows
  - Enhanced alert animations and button states
  - ScrollSpy active state management
  - Dark theme optimizations for all new components
- **Interactive Components Demo**: Complete example page showcasing all features
  - Live component demonstrations at `/examples/components`
  - Code examples with syntax highlighting
  - Real-world usage patterns and best practices
  - Responsive design with mobile optimization
  - Performance monitoring and loading states
- **Framework Integration**: Zero-configuration component initialization
  - Auto-initialization on DOM ready
  - Data attribute-driven configuration
  - No external JavaScript dependencies beyond Phuse core
  - Backward compatibility with existing projects
  - Production-ready component implementations
- **Bootstrap 5.3.8 Full Compatibility**: Complete JavaScript feature parity
  - All Bootstrap 5.3.8 interactive components supported
  - Consistent API with Bootstrap data attributes
  - Enhanced accessibility features
  - Modern JavaScript patterns without jQuery dependency
  - Lightweight and performant implementations
- **Developer Documentation**: Comprehensive component usage guides
  - HTML markup examples for each component
  - JavaScript API documentation
  - Customization and configuration options
  - Accessibility guidelines and best practices
  - Migration guides for existing applications

## v1.1.3 (2025-11-12)

- **Complete CSS Framework Modernization**: Bootstrap 5+ compatible framework with dark theme optimization
  - **Modern Grid System**: Complete Bootstrap 5+ grid implementation with gap-based spacing
    - **12-Column Responsive Grid**: Full breakpoint support (xs, sm, md, lg, xl, xxl)
    - **Auto-sizing Columns**: `col-auto` classes for all responsive breakpoints
    - **Gap-based Spacing**: CSS variables `--ps-gutter-x` and `--ps-gutter-y` for modern spacing
    - **Row Columns Utilities**: `row-cols-*` classes for automatic column distribution
    - **Fixed 3-Card Layout**: Proper gap spacing ensures correct 3-card per row display
  - **Phuse-Specific Variables**: Renamed all CSS variables from `--bs-*` to `--ps-*` for framework branding
  - **Enhanced Component Library**: Modern components optimized for dark themes
    - **Dark Theme Alerts**: Highly visible alerts with light text, colored backgrounds, and left accent borders
    - **Compact Badges**: Content-width badges using `width: fit-content` instead of full width
    - **Modern Cards**: Enhanced card components with hover effects and proper dark theme styling
    - **Form Controls**: Dark theme optimized form inputs with proper focus states
  - **Extended Utility Classes**: Comprehensive spacing, flexbox, and display utilities
    - **Modern Spacing Scale**: Extended spacing utilities (m-6 through m-8, p-6 through p-8)
    - **Complete Responsive Display**: All breakpoints with flex, block, inline, and none utilities
    - **Enhanced Flexbox**: Complete flexbox control with alignment and justification
    - **Modern Typography**: Extended text utilities and color classes
  - **Dark Theme Optimization**: All components designed for excellent dark background visibility
    - **Color Hierarchy**: Primary, secondary, and tertiary background levels
    - **Text Contrast**: Optimized text colors for readability on dark backgrounds
    - **Component Visibility**: Enhanced contrast for alerts, buttons, and interactive elements
    - **Focus States**: Clear focus indicators for accessibility
  - **Performance Optimized**: Efficient CSS with modern selectors and minimal footprint
  - **Bootstrap 5.3.8 Compatibility**: Full compatibility while maintaining Phuse-specific enhancements
- **Interactive CSS Framework Examples**: Comprehensive demonstration system
  - **CSS Framework Examples Page**: Complete showcase at `/examples/css-framework`
  - **Grid System Demonstrations**: Basic grid, responsive 3-card layout, auto-sizing columns
  - **Component Showcases**: Enhanced alerts, compact badges, modern cards
  - **Utility Examples**: Spacing scales, flexbox utilities, responsive display
  - **Dark Theme Features**: Color hierarchy and contrast demonstrations
  - **CSS Variables Showcase**: Phuse-specific variable system explanation
- **Comprehensive CSS Documentation**: Complete framework guide in `docs/css-framework.md`
  - **Quick Start Guide**: Basic HTML structure and grid usage
  - **Component Documentation**: Cards, alerts, badges, buttons with code examples
  - **Utility Reference**: Spacing, flexbox, display, colors, borders, shadows
  - **Dark Theme Guide**: Optimization strategies and color system
  - **Migration Guide**: Converting from Bootstrap with variable name changes
  - **Browser Support**: Compatibility information and performance notes
- **Framework Integration**: Seamless integration with existing Phuse features
  - **Examples Controller Update**: Added CSS framework to examples system
  - **Routing Integration**: Proper URL routing for CSS framework examples
  - **Template System Compatibility**: Works with existing template rendering
  - **Asset Management**: Proper CSS file serving and caching

## v1.1.2 (2025-11-12)

- **Text Utilities System Overhaul**: Complete reorganization and enhancement of text processing utilities
  - **Relocated Core Classes**: Moved all text utilities from `Core/Text/` to `Core/Utilities/Text/` for better organization
  - **Enhanced String Utilities (Str)**: Comprehensive improvements to string manipulation and generation
    - **Advanced UUID Generation**: Multi-version UUID support (v1, v3, v4, v5) with maximum uniqueness guarantees
    - **Cryptographically Secure Random Strings**: Enhanced entropy using `random_bytes()` with additional mixing
    - **Improved Pluralization**: Support for irregular plurals and comprehensive linguistic rules
    - **Enhanced Time Formatting**: Better time elapsed strings with proper pluralization
    - **Base64 Validation Fix**: Corrected inverted logic in `isBase64()` method
    - **Meta Keywords Generation**: Improved keyword extraction with frequency-based sorting
    - **RFC 4122 Namespace Support**: Predefined namespaces for DNS, URL, OID, and X.500
    - **UUID Validation**: Built-in format validation for generated UUIDs
  - **Enhanced Number Utilities (Number)**: Improved number formatting and currency handling
    - **Negative Number Support**: Proper handling of negative values in `shortNumber()`
    - **International Phone Formatting**: Support for 10+ countries with automatic country code detection
    - **Improved Type Safety**: Better parameter validation and union types
    - **Enhanced Currency Formatting**: Flexible decimal and thousands separators
  - **HTML Processing Utilities**: Secure HTML minification with XSS protection
  - **CSS Minification Utilities**: Comprehensive CSS optimization and compression
  - **JavaScript Minification Utilities**: Safe JavaScript compression with string/regex handling
- **Framework Architecture Improvement**: Better utility organization and separation of concerns
  - **Namespace Restructuring**: Moved utility classes to dedicated `Core/Utilities/` namespace
  - **Updated Dependencies**: All framework components updated to use new utility namespaces
  - **Backward Compatibility**: Maintained API compatibility where possible
- **Security Enhancements**: Improved security across all text processing utilities
  - **XSS Protection**: Enhanced HTML escaping and validation
  - **Input Sanitization**: Comprehensive input validation and sanitization
  - **Secure Random Generation**: Cryptographically secure random number generation
- **Performance Optimizations**: Enhanced performance for text processing operations
  - **Efficient Algorithms**: Optimized string processing and UUID generation
  - **Memory Management**: Improved memory usage for large text operations
  - **Caching Compatibility**: Better integration with framework caching systems
- **Comprehensive Documentation**: Complete documentation for text utilities system
  - **Text Utilities Guide**: Detailed usage examples and API documentation in `docs/text-utilities.md`
  - **Migration Guide**: Instructions for upgrading from old `Core\Text` namespace
  - **Security Best Practices**: Guidelines for secure text processing
  - **Performance Tips**: Optimization recommendations for production use

## v1.1.1 (2025-11-12)

- **Complete ORM System Overhaul**: Modern Active Record implementation with enterprise features
  - Comprehensive Model class with relationships (hasOne, hasMany, belongsTo, belongsToMany)
  - Eager loading with `with()` method for relationship optimization
  - Model events/hooks system (saving, created, updated, deleted)
  - Scopes for query filtering and reusable query logic
  - Soft deletes with restore functionality and trashed record access
  - Automatic timestamps (created_at, updated_at) management
  - Attribute casting system (boolean, integer, string, array, json)
  - Accessors and mutators for data transformation
  - Mass assignment protection (fillable/guarded attributes)
  - Hidden attributes for API security
  - Global scopes for application-wide query modifications
- **Database Connection Pooling**: Performance optimization with connection reuse
  - ConnectionPool class for managing multiple database connections
  - Automatic connection health monitoring and cleanup
  - Configurable pool size and timeout settings
  - Improved concurrent request handling
- **Enhanced Database Builders**: Advanced query building capabilities
  - Improved BuildersTrait with additional aggregation methods
  - Enhanced MySQL and PostgreSQL driver support
  - Better query compilation and parameter binding
  - Support for complex joins and subqueries
- **Model Validation Integration**: Automatic validation before save operations
  - Integration with existing Validator system
  - Custom validation rules per model
  - Automatic validation error handling
  - Pre-save validation hooks
- **Query Result Caching**: Intelligent caching system for database queries
  - QueryCache integration with Model class
  - Automatic cache invalidation on data changes
  - Configurable cache lifetime and storage
  - Development-friendly cache management
- **Comprehensive ORM Examples**: Complete demonstration system
  - Full CRUD operations example with relationships
  - Model validation examples
  - Advanced query building demonstrations
  - Real-world usage scenarios
- **Database Documentation**: Complete setup and usage guides
  - ORM examples guide with database schema
  - Model configuration and relationship documentation
  - Performance optimization tips
  - Troubleshooting and best practices

## v1.1.0 (2025-11-10)

- **Refactor Exception System**: Complete overhaul with modern PHP practices and framework integration
  - New BaseException class with type categorization, severity levels, and context data
  - Enhanced Handler class with Core\Log integration and improved error categorization
  - Updated Error class with better template handling and logging integration
  - Enhanced CommonTrait with comprehensive exception throwing methods and assertion helpers
  - Updated Base.php with proper exception handling and SystemException usage
  - Updated Container.php to use new exception types for consistency
  - Removed deprecated E_STRICT references for PHP 8.0+ compatibility
  - Comprehensive error context and user-friendly messages throughout
- **Update HTML Components System**: Complete rebuild with enterprise-grade security
  - 30 secure HTML components with automatic XSS protection
  - Factory pattern architecture with fluent API
  - Enhanced ComponentTrait with bulk operations and CSS utilities
  - Comprehensive documentation and usage examples
  - Zero XSS vulnerabilities
- **Refactor Image Component**: Complete overhaul with modern PHP practices
  - Enhanced error handling and validation with comprehensive security checks
  - Integration with framework's Core\Log system for consistent logging
  - Configuration management with ImageConfig class
  - Support for JPEG, PNG, GIF, WebP formats with quality control
  - Advanced operations: resize, crop, rotate, compress, watermark
  - Comprehensive documentation moved to docs/image-utilities.md
  - Unit tests and usage examples
- **Refactor Upload Utility**: Complete security and functionality overhaul
  - Enhanced security with intelligent XSS protection for text files only
  - Integration with framework's Core\Log system for consistent logging
  - UploadConfig class for flexible configuration management with preset profiles
  - Improved validation with detailed error messages and MIME type checking
  - Secure filename handling with sanitization and unique naming
  - Comprehensive documentation moved to docs/upload-utilities.md
  - Unit tests and professional usage examples
  - Enhanced error handling and framework integration
- **Refactor Template System**: Complete overhaul with modern PHP practices and enhanced functionality
  - Enhanced error handling with proper exception throwing and catching capabilities
  - Improved security with safe variable extraction and input validation
  - Fixed condition evaluation logic for better template parsing reliability
  - Added comprehensive PHPDoc documentation throughout the template system
  - Updated ParserInterface with better type declarations and documentation
  - Enhanced template parsing with improved regex patterns for better performance
  - Added proper backward compatibility for Error class constructor
  - Integrated template system with framework's exception handling architecture
- **Add Template System Examples**: Comprehensive demonstration system with web interface
  - Created ExamplesController with 8 different example types showcasing all template features
  - Added interactive web interface at `/examples` for easy access to demonstrations
  - Created 5+ example templates in `App/Views/examples/` covering all use cases
  - Added routing configuration for all example endpoints
  - Updated documentation with comprehensive template system guide in `docs/template-system.md`
  - Enhanced README.md with examples section and access instructions
  - Provided real-world scenarios including e-commerce, dashboards, and blog templates

## v1.0.3 (2025-10-21)

- Added comprehensive Dependency Injection (DI) Container system with automatic dependency resolution
- Implemented Middleware System with stack-based processing and request/response modification
- Added unified Cache System with multiple drivers (File, Memory) and advanced features
- Added support for middleware groups in Router class for better organization
- Enhanced type safety with PHP 8.2+ type declarations throughout core classes
- Integrated DI container with middleware system for better dependency management
- Added comprehensive documentation for both DI container and middleware features
- Improved code organization and separation of concerns across the framework

## v1.0.2a (2025-10-21)

- Removed Versioning information on code files
- Fixed session issue on local machine

## v1.0.2 (2025-09-13)

- Added Database Query Caching system
- Added Template Caching for improved performance
- Enhanced cache configuration options
- Improved documentation for caching features
- Optimized template rendering performance
- Core/Router - Added handle for empty url
- Core/Template/ParserTrait : Update the parseForEach method to not replace string that is outside the brackets scope
- Core/Router : Handling local machine routing

## v1.0.1 (2025-2-28)

- Added support for multiple HTTP methods in Router class
- Added Route Caching in Router class
- Added DocBlock to Core files
- Fixed namespace resolution issues in Router class
- Improved session system for better security and performance
- Improved Router class for better performance and flexibility
- Improved Base class for better performance and flexibility

## v1.0.0 (2023-11-21)

- Initial release of Phuse 1, based on collective and collaborative framework named 'Orceztra'(discontinued personal project).
