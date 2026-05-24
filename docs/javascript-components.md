# JavaScript Components

Phuse provides a full set of Bootstrap 5.3.8-compatible JavaScript components, implemented as **static methods** on the `Phuse` class. No jQuery, no external dependencies - all components auto-initialize via `data-*` attributes on DOM ready.

## Components

- [Alert](#alert)
- [Button](#button)
- [Carousel](#carousel)
- [Modal](#modal)
- [Offcanvas](#offcanvas)
- [Popover](#popover)
- [ScrollSpy](#scrollspy)
- [Tooltip](#tooltip)
- [Toast](#toast)
- [Accordion](#accordion)

## Data-Attribute API

All components are driven by `data-*` attributes - no JavaScript required for typical use.

| Attribute | Purpose |
| --- | --- |
| `data-toggle="modal"` | Open a modal |
| `data-toggle="offcanvas"` | Open an offcanvas panel |
| `data-toggle="popover"` | Toggle a popover |
| `data-toggle="tooltip"` | Activate tooltip on hover |
| `data-toggle="button"` | Toggle active state on a button |
| `data-toggle="tab"` | Switch tab pane |
| `data-dismiss="modal"` | Close the nearest modal |
| `data-dismiss="offcanvas"` | Close the nearest offcanvas |
| `data-dismiss="alert"` | Dismiss the nearest alert |
| `data-target="#id"` | Target element by ID |
| `data-slide="prev/next"` | Carousel navigation |
| `data-slide-to="N"` | Jump carousel to slide N |
| `data-spy="scroll"` | Mark element as ScrollSpy target |
| `data-backdrop="static"` | Disable backdrop-click dismiss on modal |

## Alert

Dismissible alert banners. Clicking the `btn-close` button fades and removes the alert.

```html
<div class="alert alert-primary alert-dismissible fade show" role="alert">
  <strong>Primary:</strong> Alert message here.
  <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
    <i class="pi pi-x"></i>
  </button>
</div>
```

**Variants**: `alert-primary` `alert-success` `alert-warning` `alert-danger` `alert-info`

**Programmatic:**

```js
Phuse.alert(closeButtonEl).close();
```

## Button

Toggles an `.active` class on buttons. Optionally syncs a hidden checkbox or radio input.

```html
<button type="button" class="btn btn-primary" data-toggle="button">Toggle me</button>
```

**Programmatic:**

```js
Phuse.button().toggle(element);
```

## Carousel

Cycled slide show with prev/next controls, indicator dots, and persistent slide index via WeakMap state.

```html
<div id="my-carousel" class="carousel">
  <div class="carousel-inner">
    <div class="carousel-item active">Slide 1</div>
    <div class="carousel-item">Slide 2</div>
    <div class="carousel-item">Slide 3</div>
  </div>
  <button class="carousel-control-prev" type="button" data-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
  <div class="carousel-indicators">
    <button type="button" data-slide-to="0" class="active"></button>
    <button type="button" data-slide-to="1"></button>
    <button type="button" data-slide-to="2"></button>
  </div>
</div>
```

**Programmatic:**

```js
const c = Phuse.carousel(document.getElementById('my-carousel'));
c.next();       // advance one slide
c.prev();       // go back one slide
c.goTo(2);      // jump to slide index 2
```

> The carousel uses a **WeakMap** to persist the current slide index across multiple handler calls - the index is never reset when the next/prev button is clicked.

## Modal

Full-featured dialog overlay with animated backdrop, scroll lock, Escape key support, and optional static backdrop.

```html
<!-- Trigger -->
<button class="btn btn-primary" data-toggle="modal" data-target="#my-modal">Open</button>

<!-- Modal -->
<div class="modal fade" id="my-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Title</h5>
        <button class="btn-close" data-dismiss="modal" aria-label="Close">
          <i class="pi pi-x"></i>
        </button>
      </div>
      <div class="modal-body">
        <p>Modal content goes here.</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
        <button class="btn btn-primary">Confirm</button>
      </div>
    </div>
  </div>
</div>
```

**Sizes** - add class to `.modal`:

| Class | Max-width |
| --- | --- |
| `.modal-sm` | 300 px |
| *(default)* | 560 px |
| `.modal-lg` | 800 px |
| `.modal-xl` | 1140 px |

**Static backdrop** - disables Escape key and click-outside:

```html
<div class="modal fade" data-backdrop="static" id="confirm-modal" ...>
```

**Programmatic:**

```js
const m = Phuse.modal(document.getElementById('my-modal'));
m.show();
m.hide();
```

## Offcanvas

Panel that slides in from the left or right edge of the screen with a dimmed backdrop.

```html
<!-- Trigger -->
<button class="btn btn-primary" data-toggle="offcanvas" data-target="#my-panel">Open</button>

<!-- Panel -->
<div class="offcanvas offcanvas-end" id="my-panel" tabindex="-1">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Panel Title</h5>
    <button class="btn-close" data-dismiss="offcanvas" aria-label="Close">
      <i class="pi pi-x"></i>
    </button>
  </div>
  <div class="offcanvas-body">
    Panel content here.
  </div>
</div>
```

**Position classes:**

| Class | Edge |
| --- | --- |
| `.offcanvas-end` | Right |
| `.offcanvas-start` | Left |
| `.offcanvas-top` | Top |
| `.offcanvas-bottom` | Bottom |

**Programmatic:**

```js
const oc = Phuse.offcanvas(document.getElementById('my-panel'));
oc.show();
oc.hide();
```

## Popover

Rich content overlay shown on click. Closes when clicking anywhere outside the popover.

```html
<button class="btn btn-primary" data-toggle="popover"
        title="Popover Title"
        data-content="Body content displayed inside the popover.">
  Click for Popover
</button>
```

**Programmatic:**

```js
const p = Phuse.popover(element);
p.show();
p.hide();
p.toggle();
```

## ScrollSpy

Highlights the matching nav link as the user scrolls through a content pane. The scrollable element itself (not `window`) is observed.

```html
<nav id="spy-nav">
  <a class="nav-link" href="#section-1">Section 1</a>
  <a class="nav-link" href="#section-2">Section 2</a>
</nav>

<div data-spy="scroll" data-target="#spy-nav" data-offset="0"
     style="height:300px; overflow-y:scroll;">
  <h4 id="section-1">Section 1</h4>
  <p>Content...</p>
  <h4 id="section-2">Section 2</h4>
  <p>Content...</p>
</div>
```

ScrollSpy is auto-initialized on DOM ready for every `[data-spy="scroll"]` element.

**Programmatic:**

```js
const spy = Phuse.scrollSpy(scrollableElement);
spy.update(); // re-check active position
```

## Tooltip

Lightweight hover tooltip supporting four placements. Color variant is automatically inherited from the trigger button's class.

```html
<!-- Default (top) placement -->
<button class="btn btn-primary" data-toggle="tooltip" title="Tooltip text">Hover me</button>

<!-- With explicit placement -->
<button class="btn btn-danger" data-toggle="tooltip" data-placement="bottom" title="Danger tip">
  Danger
</button>
```

**Placement values:** `top` (default) · `bottom` · `left` · `right`

**Color variants** - automatically applied from the trigger's button class:

| Button class | Tooltip color |
| --- | --- |
| `btn-primary` | Primary blue |
| `btn-success` | Green |
| `btn-danger` | Red |
| `btn-warning` | Orange |
| `btn-info` | Cyan |
| *(none / outline)* | Dark charcoal (default) |

**Programmatic:**

```js
const t = Phuse.tooltip(element);
t.show();
t.hide();
```

> Tooltips use **`mouseover`/`mouseout`** (not `mouseenter`/`mouseleave`) in event delegation because the latter do not bubble to the document level.

## Toast

Fixed top-right notification stack. Auto-dismisses after 4 seconds.

```js
Phuse.toast('Operation completed!', 'success');
Phuse.toast('An error occurred.',   'error');
Phuse.toast('Heads up!',            'info');
Phuse.toast('Check your input.',    'warning');

// Custom duration (ms)
Phuse.toast('Saved.', 'success', 6000);
```

**Types:**

| Type | Color | Icon |
| --- | --- | --- |
| `success` | Green | `pi-check-circle` |
| `error` | Red | `pi-x-circle` |
| `warning` | Orange | `pi-alert-triangle` |
| `info` | Cyan | `pi-info` |

All toasts slide in from the right and stack vertically. The container is created on first use and removed when empty.

## Accordion

Collapsible panels with animated `max-height` transitions. Operates independently per item by default.

```html
<div class="accordion">

  <!-- Expanded by default -->
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button" type="button">Item #1</button>
    </h2>
    <div class="accordion-body">
      Content shown by default.
    </div>
  </div>

  <!-- Collapsed by default -->
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" type="button">Item #2</button>
    </h2>
    <div class="accordion-body"
         style="max-height:0;padding-top:0;padding-bottom:0;overflow:hidden;">
      Hidden content revealed on click.
    </div>
  </div>

</div>
```

> **Important**: collapsed bodies require the inline style `max-height:0;padding-top:0;padding-bottom:0;overflow:hidden;` to prevent border-box padding from leaking through.

**Programmatic:**

```js
Phuse.accordion(buttonElement).toggle();
```

## Close Buttons

All `btn-close` buttons must include `<i class="pi pi-x"></i>` to display the pi icon:

```html
<button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
  <i class="pi pi-x"></i>
</button>
```

## Programmatic Initialization

Components auto-initialize on `DOMContentLoaded`. To manually trigger:

```js
Phuse.init();
```

To initialize a single component after dynamic DOM insertion:

```js
// All methods are static - call directly
Phuse.modal(el).show();
Phuse.toast('Hello!', 'info');
Phuse.accordion(btn).toggle();
```

## Migration from Bootstrap 5

| Bootstrap 5 | Phuse |
| --- | --- |
| `data-bs-toggle` | `data-toggle` |
| `data-bs-target` | `data-target` |
| `data-bs-dismiss` | `data-dismiss` |
| `new bootstrap.Modal(el)` | `Phuse.modal(el)` |
| `new bootstrap.Tooltip(el)` | `Phuse.tooltip(el)` |
| `new bootstrap.Toast(el)` | `Phuse.toast(message, type)` |

## Live Demo

Visit `/examples/components` for an interactive demonstration of all components with code snippets.

---

*For CSS variables and component styles, see the [CSS Framework documentation](css-framework.md).*
