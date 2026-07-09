# Design System Documentation

## Overview

The new Skelby Forsamlinghus Design System provides a comprehensive, modern foundation for all frontend development. It follows best practices for responsive design, accessibility, and maintainability.

## Table of Contents

1. [Architecture](#architecture)
2. [CSS Variables & Theming](#css-variables--theming)
3. [Components](#components)
4. [Layouts](#layouts)
5. [JavaScript Components](#javascript-components)
6. [Usage Examples](#usage-examples)
7. [Accessibility](#accessibility)
8. [Browser Support](#browser-support)

---

## Architecture

### Directory Structure

```
assets/
├── css/
│   ├── system/
│   │   ├── variables.css       # CSS custom properties
│   │   ├── base.css            # Reset & base styles
│   │   ├── components.css      # Button, form, card components
│   │   └── layout.css          # Grid, flexbox, layout utilities
│   ├── system.css              # Main import file
│   └── theme.css               # Brand customizations
├── js/
│   └── ui-components.js        # Interactive component library
└── view/
    ├── layouts/
    │   └── main.php            # Base page layout
    └── components/
        ├── header.php          # Navigation header
        └── footer.php          # Site footer
```

### CSS File Organization

- **variables.css**: All design tokens (colors, typography, spacing, etc.)
- **base.css**: Global reset and typography  
- **components.css**: Reusable UI components
- **layout.css**: Grid system and layout utilities
- **system.css**: Main file that imports everything
- **theme.css**: Brand-specific customizations

---

## CSS Variables & Theming

All design tokens are defined as CSS custom properties (CSS variables) in `variables.css`. This enables:
- Easy theme customization
- Consistent spacing and sizing
- Dark mode support
- Dynamic runtime changes

### Available Variables

#### Colors

```css
/* Primary */
--color-primary: #2a5caa;
--color-primary-light: #4a7cc9;
--color-primary-lighter: #6fa1e0;
--color-primary-dark: #1a3d6b;

/* Secondary */
--color-secondary: #d4954e;
--color-secondary-light: #e8b474;
--color-secondary-dark: #a86b2e;

/* Status Colors */
--color-success: #27ae60;
--color-warning: #f39c12;
--color-accent: #e74c3c;
--color-info: #3498db;

/* Neutrals */
--color-white: #ffffff;
--color-black: #2c3e50;
--color-gray: #95a5a6;
```

#### Typography

```css
--font-family-base: System fonts stack
--font-family-heading: Georgia, Garamond, serif
--font-size-xs through --font-size-5xl
--font-weight-light through --font-weight-extra-bold
--line-height-tight, --line-height-normal, etc.
```

#### Spacing Scale

```css
--spacing-1: 4px
--spacing-2: 8px
--spacing-4: 16px
--spacing-6: 24px
--spacing-8: 32px
--spacing-12: 48px
/* ... and more */
```

#### Shadows

```css
--shadow-sm through --shadow-2xl
--shadow-inset
```

### Customizing the Theme

To change colors globally, override variables in a CSS file loaded after system.css:

```css
:root {
  --color-primary: #your-blue;
  --color-secondary: #your-gold;
}
```

---

## Components

### Buttons

**HTML Structure:**
```html
<button class="btn btn-primary">Primary Button</button>
<button class="btn btn-secondary">Secondary Button</button>
<button class="btn btn-outline">Outline Button</button>
<button class="btn btn-danger">Danger Button</button>
```

**Variants:**
- `btn-primary` - Primary action (blue)
- `btn-secondary` - Secondary action (gold)
- `btn-outline` - Outlined style
- `btn-ghost` - Ghost/text style
- `btn-danger` - Destructive action (red)
- `btn-success` - Success action (green)

**Sizes:**
- `btn-sm` - Small button
- `btn-lg` - Large button
- `btn-full` - Full width button

**Groups:**
```html
<div class="btn-group">
  <button class="btn btn-primary">Save</button>
  <button class="btn btn-outline">Cancel</button>
</div>
```

### Forms

**Basic Form Group:**
```html
<div class="form-group">
  <label for="name" class="form-label">Full Name</label>
  <input type="text" id="name" name="name" class="form-control">
  <div class="form-text">Provide your full legal name</div>
</div>
```

**Form Row (Grid):**
```html
<div class="form-row form-row-2">
  <div class="form-group">
    <label class="form-label">First Name</label>
    <input type="text" class="form-control">
  </div>
  <div class="form-group">
    <label class="form-label">Last Name</label>
    <input type="text" class="form-control">
  </div>
</div>
```

**Validation States:**
```html
<input class="form-control error" type="text">
<input class="form-control success" type="text">
<div class="form-text error">This field is required</div>
<div class="form-text success">Looks good!</div>
```

### Cards

**Basic Card:**
```html
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Card Title</h3>
  </div>
  <div class="card-body">
    <p>Card content goes here</p>
  </div>
  <div class="card-footer">
    <button class="btn btn-primary">Action</button>
  </div>
</div>
```

### Alerts

**Alert Types:**
```html
<div class="alert alert-success">Success message</div>
<div class="alert alert-danger">Error message</div>
<div class="alert alert-warning">Warning message</div>
<div class="alert alert-info">Info message</div>
```

**Dismissible Alerts:**
```html
<div class="alert alert-info alert-dismissible">
  <h4 class="alert-heading">Info!</h4>
  <p>Alert content with close button</p>
  <button class="alert-close" aria-label="Close"></button>
</div>
```

### Modals

**Modal Structure:**
```html
<div class="modal" id="myModal">
  <div class="modal-content">
    <div class="modal-header">
      <h4 class="modal-title">Modal Title</h4>
      <button class="modal-close" aria-label="Close"></button>
    </div>
    <div class="modal-body">
      <p>Modal content here</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="ModalComponent.close('#myModal')">Cancel</button>
      <button class="btn btn-primary">Save</button>
    </div>
  </div>
</div>
```

**JavaScript:**
```javascript
// Open modal
ModalComponent.open('#myModal');

// Close modal
ModalComponent.close('#myModal');
```

---

## Layouts

### Grid System

**Responsive Grid:**
```html
<div class="row">
  <div class="col">Grid item</div>
  <div class="col">Grid item</div>
  <div class="col">Grid item</div>
</div>
```

**Column Spanning:**
```html
<div class="row row-4">
  <div class="col-2">2 columns wide</div>
  <div class="col-2">2 columns wide</div>
</div>
```

**Responsive Classes:**
- `col-md-*` - Medium screen up
- `col-lg-*` - Large screen up  
- `col-xl-*` - Extra large screen up

### Flexbox Utilities

**Basic Flex:**
```html
<div class="flex flex-gap-4">
  <div>Item 1</div>
  <div>Item 2</div>
</div>
```

**Flex Utilities:**
- `flex-row` / `flex-column` - Direction
- `flex-wrap` / `flex-nowrap` - Wrapping
- `flex-justify-center` - Main axis alignment
- `flex-align-center` - Cross axis alignment
- `flex-gap-*` - Gap between items
- `flex-1` - Flex grow

### Container

**Responsive Container:**
```html
<div class="container">
  <!-- Content constrained to max-width with responsive padding -->
</div>
```

Container widths:
- sm: 540px (576px+)
- md: 720px (768px+)
- lg: 960px (992px+)
- xl: 1140px (1200px+)
- 2xl: 1320px (1400px+)

---

## JavaScript Components

### Modal Component

```javascript
// Open a modal
ModalComponent.open('#modalId');

// Close a modal
ModalComponent.close('#modalId');

// Features:
// - Auto-initialization of all modals
// - Click outside to close
// - Escape key to close
// - Focus trapping for accessibility
```

### Form Validation

```javascript
const form = document.querySelector('form[data-validate-form]');

// Validate entire form
FormValidator.validate(form);

// Show error for a field
FormValidator.showError(field, 'Error message');

// Clear error
FormValidator.clearError(field);

// Available validators:
// - required
// - email
// - minLength:10
// - maxLength:50
// - pattern:[regex]
// - phone
```

**HTML Usage:**
```html
<form data-validate-form>
  <input type="email" 
         data-validate="required|email"
         data-erroremail="Please enter a valid email">
</form>
```

### Menu Toggle

```javascript
// Auto-initialized on page load
// Handles mobile menu toggle with:
// - Click outside to close
// - Auto-close on link click
// - ARIA attributes for accessibility
```

### AJAX Form Submission

```javascript
// Mark form with data-ajax-submit
<form data-ajax-submit action="/submit" method="POST">
  <!-- form fields -->
</form>

// Submits without page reload
// Handles validation, loading state, and responses
```

### Toast Notifications

```javascript
UI.toast('Success!', 'success', 3000);
UI.toast('An error occurred', 'danger', 5000);
UI.toast('Loading...', 'info', 0); // No auto-dismiss

// Types: success, danger, warning, info
```

---

## Usage Examples

### Complete Page Example

```php
<?php
// Set page variables
$page_title = 'Events';
$page_css = 'events.css';
$page_js = 'events.js';
$page_breadcrumb = [
  '/arrangementer.php' => 'Events',
  '' => 'List'
];

// Include layout
include 'assets/view/layouts/main.php';
$content_file = 'content/events-list.php';
?>
```

### Hero Section

```html
<section class="hero">
  <div class="hero-content">
    <h1>Welcome to Skelby Forsamlinghus</h1>
    <p>The heart of our community</p>
    <button class="btn btn-secondary">Learn More</button>
  </div>
</section>
```

### Event Card

```html
<div class="card event-card">
  <div style="position: relative;">
    <img src="event.jpg" alt="Event" class="event-card-image">
    <span class="event-category">Community</span>
  </div>
  <div class="card-body">
    <div class="event-card-date">15 Juni 2026</div>
    <h3 class="event-card-title">Summer Celebration</h3>
    <p class="event-card-description">Join us for our annual summer celebration...</p>
    <button class="btn btn-primary">Learn More</button>
  </div>
</div>
```

---

## Accessibility

### Features Included

- **Semantic HTML** - Proper heading hierarchy, landmark elements
- **ARIA Attributes** - Labels, roles, states for screen readers
- **Keyboard Navigation** - All components keyboard accessible
- **Focus Indicators** - Clear focus visible states
- **Color Contrast** - WCAG AA compliant color combinations
- **Motion** - Respects `prefers-reduced-motion` preference
- **Forms** - Proper labels and error messages

### Best Practices

1. **Use semantic HTML** - Prefer `<button>` over `<div>` for buttons
2. **Include alt text** - Always describe images
3. **Label form fields** - Use `<label>` elements with proper associations
4. **Test with keyboard** - Ensure all features work without mouse
5. **Check color contrast** - Use tools like WebAIM Contrast Checker

---

## Browser Support

- Chrome/Edge (latest 2 versions)
- Firefox (latest 2 versions)
- Safari 14+
- iOS Safari 14+
- Android Chrome

### Progressive Enhancement

- Design system works without JavaScript
- JavaScript components are enhancements
- Fallbacks for older browsers provided

---

## Migration Guide

### From Old System to New System

1. **Import new stylesheet** in page header:
   ```html
   <link rel="stylesheet" href="/assets/css/system.css">
   ```

2. **Update page structure** to use new layouts:
   ```php
   include 'assets/view/layouts/main.php';
   ```

3. **Replace old components** with new ones:
   - Old: `<div class="button">` → New: `<button class="btn btn-primary">`
   - Old: Custom forms → New: `form-control` classes

4. **Include JS library** for interactivity:
   ```html
   <script src="/assets/js/ui-components.js"></script>
   ```

---

## Responsive Breakpoints

```
Mobile:     320px - 575px
Tablet:     576px - 768px
Desktop:    769px - 992px
Large:      993px - 1200px
XL:         1201px+
```

Use CSS media queries or classes like:
```css
@media (min-width: 768px) { /* Tablet and up */ }
@media (max-width: 768px) { /* Tablet and down */ }
```

---

## Performance Considerations

- CSS is minified and optimized
- Critical CSS loaded inline (optional)
- JavaScript components lazy-loaded where possible
- Images use responsive sizing
- Font files minimized and cached

---

## Future Enhancements

- [ ] Dark mode toggle UI
- [ ] RTL language support
- [ ] Custom theme builder
- [ ] Storybook component library
- [ ] CSS-in-JS option
- [ ] Component versioning

---

## Support & Questions

For questions or issues with the design system:

1. Check this documentation
2. Review component HTML examples
3. Test with browser dev tools
4. Report issues with screenshots/reproduction steps

---

**Last Updated:** February 6, 2026  
**Version:** 1.0 (Phase 1 Complete)
