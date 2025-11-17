# JavaScript Components - Bootstrap 5.3.8 Compatibility

The Phuse framework includes complete Bootstrap 5.3.8 JavaScript component compatibility, implemented as lightweight, dependency-free modules within the Phuse JavaScript library.

## Components Overview

Phuse provides the following Bootstrap-compatible components:
- [Alert](#alert-component)
- [Button](#button-component)
- [Carousel](#carousel-component)
- [Offcanvas](#offcanvas-component)
- [Popover](#popover-component)
- [ScrollSpy](#scrollspy-component)
- [Tooltip](#tooltip-component)

## Alert Component

Dismissible alert notifications with smooth animations.

### HTML Usage
```html
<div class="alert alert-primary alert-dismissible fade show" role="alert">
  <strong>Primary Alert:</strong> This is a primary alert with a dismissible close button.
  <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>
</div>

<div class="alert alert-success alert-dismissible fade show" role="alert">
  <strong>Success Alert:</strong> Operation completed successfully.
  <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>
</div>
```

### JavaScript API
```javascript
// Programmatic alert dismissal
const alertElement = document.querySelector('.alert');
const alert = new Phuse.alert(alertElement);
alert.close();
```

### CSS Classes
- `.alert`: Base alert styles
- `.alert-primary/.alert-success/etc.`: Alert variants
- `.alert-dismissible`: Supports dismissible functionality
- `.fade`: Enables fade animations
- `.show`: Shows the alert

## Button Component

Interactive button states with toggle functionality.

### HTML Usage
```html
<button type="button" class="btn btn-primary" data-toggle="button">Primary Button</button>
<button type="button" class="btn btn-secondary" data-toggle="button">Secondary Button</button>

<!-- Button group -->
<div class="btn-group" role="group">
  <input type="radio" class="btn-check" name="options" id="option1" autocomplete="off" checked>
  <label class="btn btn-outline-primary" for="option1">Radio 1</label>

  <input type="radio" class="btn-check" name="options" id="option2" autocomplete="off">
  <label class="btn btn-outline-primary" for="option2">Radio 2</label>
</div>
```

### JavaScript API
```javascript
// Toggle button state programmatically
const button = new Phuse.button();
button.toggle(element);
```

### CSS Classes
- `.btn`: Base button styles
- `.btn-primary/.btn-secondary/etc.`: Button variants
- `.active`: Active/toggled state

## Carousel Component

Image and content slider with navigation and indicators.

### HTML Usage
```html
<div class="carousel">
  <div class="carousel-inner">
    <div class="carousel-item active">
      <div style="height: 200px; background: linear-gradient(45deg, var(--primary), var(--primary-dark));">
        Slide 1
      </div>
    </div>
    <div class="carousel-item">
      <div style="height: 200px; background: linear-gradient(45deg, var(--success), var(--success-dark));">
        Slide 2
      </div>
    </div>
    <div class="carousel-item">
      <div style="height: 200px; background: linear-gradient(45deg, var(--info), var(--primary));">
        Slide 3
      </div>
    </div>
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

### JavaScript API
```javascript
const carousel = new Phuse.carousel(carouselElement);

// Navigation methods
carousel.next();
carousel.prev();
carousel.goTo(index);
```

### CSS Classes
- `.carousel`: Main carousel container
- `.carousel-inner`: Slides container
- `.carousel-item`: Individual slides
- `.active`: Current slide
- `.carousel-control-prev/.carousel-control-next`: Navigation buttons
- `.carousel-indicators`: Indicator dots
- `.fade`: Slide transition animation

## Offcanvas Component

Sliding sidebar panels with backdrop overlay.

### HTML Usage
```html
<button class="btn btn-primary" type="button" data-toggle="offcanvas" data-target="#demo-offcanvas">
  Open Offcanvas
</button>

<div class="offcanvas offcanvas-end" tabindex="-1" id="demo-offcanvas">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Offcanvas Menu</h5>
    <button type="button" class="btn-close" data-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <p>Content goes here...</p>
  </div>
</div>
```

### JavaScript API
```javascript
const offcanvas = new Phuse.offcanvas(offcanvasElement);
offcanvas.show();
offcanvas.hide();
```

### Position Classes
- `.offcanvas-start`: Left side (default)
- `.offcanvas-end`: Right side
- `.offcanvas-top`: Top side
- `.offcanvas-bottom`: Bottom side

### CSS Classes
- `.offcanvas`: Main container
- `.offcanvas-header`: Header section
- `.offcanvas-body`: Content area
- `.offcanvas-backdrop`: Background overlay

## Popover Component

Rich content overlays triggered by clicks.

### HTML Usage
```html
<button class="btn btn-primary" type="button" data-toggle="popover"
        title="Popover Title"
        data-content="This is a popover with some content inside.">
  Click for Popover
</button>
```

### JavaScript API
```javascript
const popover = new Phuse.popover(element);
popover.show();
popover.hide();
```

### CSS Classes
- `.popover`: Main popover container
- `.popover-arrow`: Arrow element
- `.popover-header`: Title area
- `.popover-body`: Content area

## ScrollSpy Component

Navigation highlighting based on scroll position.

### HTML Usage
```html
<nav>
  <ul class="nav nav-pills flex-column">
    <li class="nav-item">
      <a class="nav-link" href="#item-1">Item 1</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="#item-2">Item 2</a>
    </li>
  </ul>
</nav>

<div data-spy="scroll" data-target="#navbar">
  <h4 id="item-1">Item 1</h4>
  <p>Content...</p>

  <h4 id="item-2">Item 2</h4>
  <p>Content...</p>
</div>
```

### JavaScript API
```javascript
const spy = new Phuse.scrollSpy(element, {
  offset: 100 // scroll offset
});
```

### CSS Classes
- `.active`: Active navigation link

## Tooltip Component

Hover-activated information displays.

### HTML Usage
```html
<button class="btn btn-primary"
        data-toggle="tooltip"
        data-placement="top"
        title="This is a tooltip">
  Hover me
</button>
```

### JavaScript API
```javascript
const tooltip = new Phuse.tooltip(element);
tooltip.show();
tooltip.hide();
```

### Placement Options
- `top` (default)
- `bottom`
- `left`
- `right`

### CSS Classes
- `.tooltip`: Main tooltip container
- `.tooltip-arrow`: Arrow element
- `.tooltip-inner`: Content area

## Implementation Details

### Auto-Initialization

Phuse components are automatically initialized when the DOM is ready:

```javascript
// Manual initialization
document.addEventListener('DOMContentLoaded', () => {
  Phuse.init();
});
```

### Event Delegation

Components use efficient event delegation for better performance:

```javascript
// Delegate click events
Phuse.on('click', '[data-toggle="modal"]', function() {
  const target = document.querySelector(this.dataset.target);
  if (target) {
    const modal = new Phuse.modal(target);
    modal.show();
  }
});
```

### Accessibility

All components include proper ARIA attributes and keyboard navigation support.

### Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Migration from Bootstrap

### Code Changes

**Bootstrap 5:**
```javascript
const popover = new bootstrap.Popover(element, options);
```

**Phuse:**
```javascript
const popover = new Phuse.popover(element);
```

### HTML Changes

Most HTML markup remains the same, with a few naming convention differences:

| Bootstrap | Phuse |
|-----------|-------|
| `data-bs-toggle` | `data-toggle` |
| `data-bs-target` | `data-target` |
| `data-bs-dismiss` | `data-dismiss` |

## Performance Features

- **Lightweight**: No external dependencies beyond Phuse core
- **Memory Efficient**: Automatic cleanup and garbage collection
- **Optimized Event Handling**: Smart event delegation
- **Efficient Animations**: Hardware-accelerated CSS transitions

## Examples and Demos

View live component examples at `/examples/components` or check the interactive examples in the `App/Views/examples/components.php` file.

## Contributing

All components follow consistent coding patterns and include comprehensive error handling. Refer to the `Phuse` class in `scripts.js` for implementation details.

---

*For CSS styling details, see the [CSS Framework Documentation](css-framework.md).*"
