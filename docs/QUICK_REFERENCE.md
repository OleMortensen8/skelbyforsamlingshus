# Design System Quick Reference

## Color Palette

| Color | CSS Variable | Hex Code | Usage |
|-------|-------------|----------|--------|
| Primary | `--color-primary` | #2a5caa | Main brand color (buttons, links) |
| Primary Light | `--color-primary-light` | #4a7cc9 | Hover states |
| Primary Dark | `--color-primary-dark` | #1a3d6b | Active states |
| Secondary | `--color-secondary` | #d4954e | Accents, badges |
| Success | `--color-success` | #27ae60 | Success messages |
| Warning | `--color-warning` | #f39c12 | Warnings |
| Danger | `--color-accent` | #e74c3c | Errors, dangerous actions |
| Info | `--color-info` | #3498db | Information |

## Spacing Scale

| Size | Value | Usage |
|------|-------|-------|
| xs | 4px | Tight spacing between elements |
| sm | 8px | Small gaps |
| base | 16px | Standard padding/margin |
| md | 24px | Medium spacing |
| lg | 32px | Large spacing |
| xl | 48px | Extra large spacing |

## Typography

| Type | Font | Size | Weight |
|------|------|------|--------|
| Headings | Georgia, serif | 30px-48px | 700 |
| Body | System fonts | 16px | 400 |
| Labels | System fonts | 16px | 600 |
| Small | System fonts | 14px | 400 |
| Code | Courier New | 16px | 400 |

## Common Classes

### Button Classes
```html
<!-- Button variants -->
<button class="btn btn-primary">Primary</button>
<button class="btn btn-secondary">Secondary</button>
<button class="btn btn-outline">Outline</button>
<button class="btn btn-danger">Danger</button>
<button class="btn btn-success">Success</button>

<!-- Button sizes -->
<button class="btn btn-sm">Small</button>
<button class="btn">Normal</button>
<button class="btn btn-lg">Large</button>

<!-- Special -->
<button class="btn btn-full">Full Width</button>
<button class="btn" disabled>Disabled</button>
```

### Form Classes
```html
<input class="form-control">          <!-- Normal input -->
<input class="form-control error">    <!-- Error state -->
<input class="form-control success">  <!-- Success state -->
<input class="form-control" disabled> <!-- Disabled -->

<label class="form-label">Label</label>
<div class="form-text">Helper text</div>
<div class="form-text error">Error text</div>
```

### Layout Classes
```html
<div class="container">               <!-- Responsive container -->
<div class="row">                     <!-- Grid row -->
<div class="col">                     <!-- Grid column -->
<div class="flex">                    <!-- Flexbox -->
<div class="flex flex-gap-4">         <!-- With gap -->
<div class="flex flex-justify-center"> <!-- Center aligned -->
```

### Text Utilities
```html
<p class="text-center">Centered text</p>
<p class="text-muted">Muted text</p>
<p class="text-success">Success text</p>
<p class="text-danger">Danger text</p>
<p class="text-xs">Extra small</p>
<p class="text-lg">Large text</p>
<p class="font-bold">Bold text</p>
```

### Spacing Utilities
```html
<div class="mb-4">Margin bottom</div>
<div class="mt-6">Margin top</div>
<div class="p-4">Padding all</div>
<div class="px-4">Padding horizontal</div>
<div class="py-4">Padding vertical</div>
```

## Component Examples

### Alert
```html
<div class="alert alert-success">
  <h4 class="alert-heading">Success!</h4>
  <p>Your changes have been saved.</p>
</div>

<div class="alert alert-danger alert-dismissible">
  <p>An error occurred</p>
  <button class="alert-close" aria-label="Close"></button>
</div>
```

### Card
```html
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Title</h3>
  </div>
  <div class="card-body">Content</div>
  <div class="card-footer">Footer</div>
</div>
```

### Badge
```html
<span class="badge badge-primary">Badge</span>
<span class="badge badge-success badge-pill">Pill</span>
```

### Modal
```html
<div class="modal" id="myModal">
  <div class="modal-content">
    <div class="modal-header">
      <h4 class="modal-title">Title</h4>
      <button class="modal-close"></button>
    </div>
    <div class="modal-body">Content</div>
    <div class="modal-footer">
      <button class="btn btn-outline">Cancel</button>
      <button class="btn btn-primary">Save</button>
    </div>
  </div>
</div>

<script>
  ModalComponent.open('#myModal');
  ModalComponent.close('#myModal');
</script>
```

## Breakpoints

```css
/* Mobile First Approach */
/* No query = Mobile (320px+) */

/* Tablet */
@media (min-width: 768px) { }

/* Desktop */
@media (min-width: 992px) { }

/* Large Desktop */
@media (min-width: 1200px) { }
```

## Form Validation

```html
<form data-validate-form>
  <input type="email" 
         class="form-control"
         data-validate="required|email"
         data-erroremail="Invalid email format">
  <button type="submit">Submit</button>
</form>

<!-- JavaScript auto-enables validation -->
<!-- Validates on blur and submit -->
```

## CSS Variables Usage

```css
/* In your custom CSS, use variables like: */
.my-element {
  color: var(--color-primary);
  padding: var(--spacing-4);
  font-size: var(--font-size-lg);
  box-shadow: var(--shadow-md);
  border-radius: var(--radius-lg);
  transition: all var(--transition-base);
}
```

## Common Grid Layouts

### Two-Column Layout
```html
<div class="row">
  <div class="col">Left column</div>
  <div class="col">Right column</div>
</div>
```

### Three-Column Layout
```html
<div class="row row-3">
  <div class="col">Column 1</div>
  <div class="col">Column 2</div>
  <div class="col">Column 3</div>
</div>
```

### Auto-Fit Grid
```html
<div class="row">
  <div class="col">Responsive</div>
  <div class="col">Grid Items</div>
  <div class="col">Auto Layout</div>
</div>
```

## JavaScript Utilities

```javascript
// Modals
ModalComponent.open('#modalId');
ModalComponent.close('#modalId');

// Alerts
AlertComponent.dismiss('#alertId');

// Validation
FormValidator.validate(form);

// Utilities
UI.toast('Message', 'success');
UI.showLoading();
UI.hideLoading();
UI.formatDate(date, 'DD/MM/YYYY');
```

## Tips for Developers

1. **Mobile First** - Write styles for mobile, add media queries for larger screens
2. **Use Variables** - Always use CSS variables instead of hard-coded values
3. **Semantic HTML** - Use appropriate HTML elements for accessibility
4. **Test Accessibility** - Use keyboard navigation and screen readers
5. **Check Responsiveness** - Test on multiple device sizes
6. **Validate Forms** - Use data-validate attributes for validation
7. **Keep It Simple** - Follow existing patterns instead of creating new styles
8. **Document Changes** - Update this guide when adding new components

## File Structure Reference

```
assets/
├── css/
│   ├── system/
│   │   ├── variables.css       ← Colors, spacing, fonts
│   │   ├── base.css            ← Global styles, typography
│   │   ├── components.css      ← Buttons, forms, cards
│   │   └── layout.css          ← Grid, flexbox, layout
│   ├── system.css              ← Imports everything above
│   ├── theme.css               ← Brand customizations
│   └── pages/
│       └── [page-name].css     ← Page-specific styles
├── js/
│   ├── ui-components.js        ← Component library
│   └── pages/
│       └── [page-name].js      ← Page-specific scripts
└── view/
    ├── layouts/
    │   └── main.php            ← Base template
    └── components/
        ├── header.php          ← Navigation
        └── footer.php          ← Footer
```

## Getting Help

- Review [DESIGN_SYSTEM.md](../docs/DESIGN_SYSTEM.md) for detailed documentation
- Check existing pages for implementation examples
- Test components in the browser DevTools
- Ask questions in code reviews
