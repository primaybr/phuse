# Phuse CSS Framework

The Phuse CSS Framework is a modern, Bootstrap 5+ compatible CSS framework specifically designed for the Phuse PHP framework. It provides a comprehensive set of utilities, components, and responsive grid system optimized for dark themes and modern web development.

## Features

### 🎨 **Modern Design System**
- **Dark Theme Optimized**: All components designed for dark backgrounds with proper contrast
- **Bootstrap 5.3.8 Compatible**: Full compatibility with Bootstrap's grid system and utilities
- **Phuse-Specific Variables**: Uses `--ps-` prefixed CSS variables instead of Bootstrap's `--bs-`
- **Modern CSS Architecture**: CSS variables, calc() functions, and modern selectors

### 📱 **Responsive Grid System**
- **12-Column Grid**: Flexible 12-column responsive grid system
- **Breakpoint Support**: xs, sm, md, lg, xl, xxl breakpoints (576px, 768px, 992px, 1200px, 1400px)
- **Auto-sizing Columns**: `col-auto` classes for content-based sizing
- **Gap-based Spacing**: Modern gap utilities with CSS variables
- **Row Columns**: `row-cols-*` utilities for automatic column distribution

### 🧩 **Component Library**
- **Enhanced Cards**: Modern card components with hover effects and shadows
- **Dark Theme Alerts**: Highly visible alerts with left accent borders and proper contrast
- **Responsive Buttons**: Complete button system with variants and states
- **Form Controls**: Modern form inputs optimized for dark themes
- **Badges**: Compact badges that fit content width (not full width)
- **Lists and Navigation**: Comprehensive list and navigation components

### 🎯 **Utility Classes**
- **Spacing System**: Comprehensive margin and padding utilities (0-5 scale + modern 6-8)
- **Flexbox Utilities**: Complete flexbox control with alignment and justification
- **Display Utilities**: Responsive display controls (block, inline, flex, etc.)
- **Typography**: Modern typography scale with responsive text utilities
- **Color System**: Dark theme color palette with semantic color classes
- **Border & Shadow**: Modern border radius and shadow utilities

## Quick Start

### Basic HTML Structure

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Phuse App</title>
  <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
  <!-- Your content here -->
</body>
</html>
```

### Container and Grid

```html
<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Main Content</h5>
          <p class="card-text">Your main content here.</p>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Sidebar</h5>
          <p class="card-text">Sidebar content.</p>
        </div>
      </div>
    </div>
  </div>
</div>
```

## Grid System

### Basic Grid

```html
<!-- Equal width columns -->
<div class="row">
  <div class="col">Column 1</div>
  <div class="col">Column 2</div>
  <div class="col">Column 3</div>
</div>

<!-- Responsive columns -->
<div class="row">
  <div class="col-sm-6 col-lg-4">Responsive Column</div>
  <div class="col-sm-6 col-lg-8">Responsive Column</div>
</div>

<!-- Auto-sizing columns -->
<div class="row">
  <div class="col-auto">Auto-sized</div>
  <div class="col">Remaining space</div>
</div>
```

### Row Columns

```html
<!-- Automatic 3 columns per row -->
<div class="row row-cols-3 g-3">
  <div class="col">Column 1</div>
  <div class="col">Column 2</div>
  <div class="col">Column 3</div>
  <div class="col">Column 4</div>
  <div class="col">Column 5</div>
  <div class="col">Column 6</div>
</div>
```

## Components

### Cards

```html
<div class="card shadow">
  <div class="card-header">
    <h5 class="card-title">Card Title</h5>
  </div>
  <div class="card-body">
    <p class="card-text">Card content goes here.</p>
    <a href="#" class="btn btn-primary">Action Button</a>
  </div>
  <div class="card-footer text-secondary">
    Card footer
  </div>
</div>
```

### Alerts

```html
<div class="alert alert-primary">
  <strong>Primary Alert:</strong> This is a primary alert with high visibility on dark themes.
</div>

<div class="alert alert-success">
  <strong>Success:</strong> Operation completed successfully!
</div>

<div class="alert alert-danger">
  <strong>Error:</strong> Something went wrong.
</div>

<div class="alert alert-warning">
  <strong>Warning:</strong> Please check your input.
</div>

<div class="alert alert-info">
  <strong>Info:</strong> Here's some information.
</div>
```

### Badges

```html
<!-- Inline badges that fit content -->
<span class="badge bg-primary">Primary</span>
<span class="badge bg-success">Success</span>
<span class="badge bg-danger">Danger</span>

<!-- In buttons -->
<button class="btn btn-primary">
  Messages <span class="badge bg-light text-dark">4</span>
</button>
```

### Buttons

```html
<!-- Basic buttons -->
<button class="btn btn-primary">Primary</button>
<button class="btn btn-secondary">Secondary</button>
<button class="btn btn-success">Success</button>
<button class="btn btn-danger">Danger</button>

<!-- Outline buttons -->
<button class="btn btn-outline-primary">Outline Primary</button>

<!-- Button sizes -->
<button class="btn btn-primary btn-sm">Small</button>
<button class="btn btn-primary">Normal</button>
<button class="btn btn-primary btn-lg">Large</button>
```

## Utilities

### Spacing

```html
<!-- Margin utilities -->
<div class="m-3">Margin all around</div>
<div class="mt-4 mb-2">Top and bottom margins</div>
<div class="me-3">Right margin (end)</div>

<!-- Padding utilities -->
<div class="p-3">Padding all around</div>
<div class="px-4 py-2">Horizontal and vertical padding</div>

<!-- Modern spacing scale -->
<div class="mt-6">Large top margin</div>
<div class="pb-8">Extra large bottom padding</div>
```

### Flexbox

```html
<!-- Flex container -->
<div class="d-flex justify-content-between align-items-center">
  <div>Item 1</div>
  <div>Item 2</div>
  <div>Item 3</div>
</div>

<!-- Flex direction -->
<div class="d-flex flex-column">
  <div>Vertical Item 1</div>
  <div>Vertical Item 2</div>
</div>

<!-- Responsive flex -->
<div class="d-flex d-lg-block">
  <!-- Flex on mobile, block on large screens -->
</div>
```

### Display

```html
<!-- Display utilities -->
<div class="d-none d-md-block">Hidden on mobile, visible on medium+</div>
<div class="d-flex d-xl-none">Flex on small screens, hidden on xl+</div>
```

### Colors

```html
<!-- Text colors -->
<p class="text-primary">Primary text</p>
<p class="text-success">Success text</p>
<p class="text-danger">Danger text</p>
<p class="text-muted">Muted text</p>

<!-- Background colors -->
<div class="bg-primary text-white">Primary background</div>
<div class="bg-success text-white">Success background</div>
<div class="bg-secondary">Secondary background</div>
```

### Borders & Shadows

```html
<!-- Borders -->
<div class="border">Basic border</div>
<div class="border-top border-primary">Top border only</div>
<div class="border-left-primary">Left accent border</div>

<!-- Border radius -->
<div class="rounded">Rounded corners</div>
<div class="rounded-circle">Fully rounded</div>
<div class="rounded-pill">Pill shape</div>

<!-- Shadows -->
<div class="shadow">Basic shadow</div>
<div class="shadow-lg">Large shadow</div>
```

## Dark Theme Optimization

The Phuse CSS Framework is specifically optimized for dark themes:

### Color System
- **Primary Colors**: High contrast blues for interactive elements
- **Semantic Colors**: Carefully chosen colors for success, danger, warning, and info states
- **Background Hierarchy**: Multiple background levels (primary, secondary, tertiary)
- **Text Colors**: Optimized for readability on dark backgrounds

### Component Design
- **Enhanced Contrast**: All components designed for dark theme visibility
- **Proper Spacing**: Generous padding and margins for better visual hierarchy
- **Modern Shadows**: Subtle shadows that work on dark backgrounds
- **Focus States**: Clear focus indicators for accessibility

### Alert Visibility
Alerts in dark themes can be hard to see. The Phuse framework addresses this with:
- **Light Text Colors**: High contrast text on colored backgrounds
- **Left Accent Borders**: 4px colored borders for instant recognition
- **Increased Opacity**: More visible background colors
- **Better Spacing**: Improved padding for readability

## CSS Variables

The framework uses CSS variables for easy customization:

```css
:root {
  /* Colors */
  --primary: #0d6efd;
  --success: #198754;
  --danger: #dc3545;
  --warning: #fd7e14;
  --info: #17a2b8;

  /* Backgrounds */
  --bg-primary: #121212;
  --bg-secondary: #1e1e1e;
  --bg-tertiary: #2a2a2a;

  /* Spacing */
  --ps-gutter-x: 1.5rem;
  --ps-gutter-y: 0;

  /* Other */
  --border-color: #333333;
  --text-primary: #ffffff;
  --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
```

## Migration from Bootstrap

### Variable Names
- Change `--bs-*` to `--ps-*` for Phuse-specific variables
- Example: `--bs-gutter-x` → `--ps-gutter-x`

### Dark Theme Considerations
- Bootstrap's light theme colors may not work well on dark backgrounds
- Use Phuse's semantic color classes instead of generic Bootstrap colors
- Adjust component spacing for better dark theme hierarchy

### Enhanced Features
- Phuse includes additional modern utilities not found in standard Bootstrap
- Extended spacing scale (m-6, m-7, m-8, etc.)
- Enhanced component variants optimized for dark themes
- Better responsive breakpoint coverage

## Browser Support

- **Modern Browsers**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **CSS Grid & Flexbox**: Full support for modern layout systems
- **CSS Variables**: Full support for custom properties
- **Dark Theme**: Optimized for modern dark theme implementations

## Performance

- **Minimal CSS**: Optimized for fast loading
- **CSS Variables**: Efficient runtime customization
- **Modern Selectors**: Optimized for browser performance
- **Responsive Images**: Proper scaling and performance

## Examples

Visit `/examples/css-framework` to see interactive examples of all CSS framework features including:
- Grid system demonstrations
- Component showcases
- Utility class examples
- Dark theme optimizations
- Responsive behavior examples

## Contributing

The Phuse CSS Framework welcomes contributions. Please ensure:
- Maintain dark theme compatibility
- Follow existing naming conventions
- Test across all supported browsers
- Update documentation for new features

## License

Licensed under the MIT License - see the main Phuse license for details.</content>
