<!DOCTYPE html>
<html lang="da">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Skelby Forsamlinghus - Design System Showcase</title>
  <link rel="stylesheet" href="/assets/css/system.css">
  <style>
    .showcase-section {
      margin: var(--spacing-12) 0;
      padding: var(--spacing-8);
      border-top: 3px solid var(--color-primary);
    }
    
    .showcase-section h2 {
      margin-bottom: var(--spacing-6);
      color: var(--color-primary);
    }
    
    .component-grid {
      display: grid;
      gap: var(--spacing-6);
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      margin-bottom: var(--spacing-6);
    }
    
    .component-demo {
      border: 1px solid var(--color-light);
      padding: var(--spacing-4);
      border-radius: var(--radius-md);
      background-color: var(--color-off-white);
    }
    
    .component-label {
      font-size: var(--font-size-sm);
      color: var(--color-gray);
      margin-bottom: var(--spacing-2);
      font-weight: var(--font-weight-semibold);
    }
    
    code {
      display: block;
      background-color: var(--color-white);
      padding: var(--spacing-2);
      margin-top: var(--spacing-2);
      font-size: var(--font-size-xs);
      border-radius: var(--radius-sm);
      overflow-x: auto;
    }
    
    .color-swatch {
      display: inline-block;
      width: 60px;
      height: 60px;
      border-radius: var(--radius-md);
      margin-right: var(--spacing-4);
      margin-bottom: var(--spacing-4);
      border: 1px solid var(--color-light);
      vertical-align: top;
    }
    
    .color-info {
      display: inline-block;
      vertical-align: top;
    }
    
    .spacing-demo {
      display: inline-block;
      background-color: var(--color-primary);
      margin-right: var(--spacing-2);
      margin-bottom: var(--spacing-6);
      vertical-align: bottom;
    }
    
    .toc {
      background-color: var(--color-off-white);
      padding: var(--spacing-6);
      border-radius: var(--radius-md);
      margin-bottom: var(--spacing-8);
    }
    
    .toc ul {
      columns: 2;
      gap: var(--spacing-4);
    }
    
    .toc a {
      display: block;
      padding: var(--spacing-2) 0;
    }
    
    .device-test {
      border: 2px solid var(--color-primary);
      padding: var(--spacing-4);
      margin: var(--spacing-4) 0;
      border-radius: var(--radius-md);
    }
    
    @media (max-width: 768px) {
      .component-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="container header-content">
      <a href="/" class="logo">
        <span>🎨</span>
        <span>Design System</span>
      </a>
      <p style="margin: 0; color: var(--color-gray);">Phase 1 Showcase & Review</p>
    </div>
  </header>

  <main class="container" style="padding-top: var(--spacing-12); padding-bottom: var(--spacing-12);">
    <!-- INTRO -->
    <section class="showcase-section">
      <h1>Skelby Forsamlinghus - Design System Showcase</h1>
      <p style="font-size: var(--font-size-lg); color: var(--color-gray);">
        Welcome to the Phase 1 design system review. This page demonstrates all components, patterns, and utilities available for building consistent, responsive interfaces.
      </p>
      <div class="alert alert-info">
        <h4 class="alert-heading">📱 Test Responsiveness</h4>
        <p>
          Open DevTools (F12) and test the responsive design at different breakpoints:
          <br><strong>Mobile (320px)</strong> • <strong>Tablet (768px)</strong> • <strong>Desktop (1200px)</strong> • <strong>XL (1400px+)</strong>
        </p>
      </div>
    </section>

    <!-- TABLE OF CONTENTS -->
    <section class="toc">
      <h3>Quick Navigation</h3>
      <ul>
        <li><a href="#colors">🎨 Color Palette</a></li>
        <li><a href="#typography">📝 Typography</a></li>
        <li><a href="#spacing">📐 Spacing Scale</a></li>
        <li><a href="#buttons">🔘 Buttons</a></li>
        <li><a href="#forms">📋 Forms</a></li>
        <li><a href="#cards">🎴 Cards</a></li>
        <li><a href="#alerts">⚠️ Alerts</a></li>
        <li><a href="#modals">🪟 Modals</a></li>
        <li><a href="#badges">🏷️ Badges</a></li>
        <li><a href="#grids">🔲 Grid Layouts</a></li>
        <li><a href="#accessibility">♿ Accessibility</a></li>
      </ul>
    </section>

    <!-- COLORS -->
    <section class="showcase-section" id="colors">
      <h2>🎨 Color Palette</h2>
      <p>Our carefully chosen colors balance brand identity with accessibility.</p>
      
      <div style="margin: var(--spacing-6) 0;">
        <h4>Primary Colors</h4>
        <div>
          <div class="color-swatch" style="background-color: var(--color-primary);"></div>
          <div class="color-info">
            <strong>Primary</strong> #2a5caa<br>
            <span style="font-size: var(--font-size-sm); color: var(--color-gray);">Main brand color</span>
          </div>
        </div>
        <div style="margin-top: var(--spacing-4);">
          <div class="color-swatch" style="background-color: var(--color-secondary);"></div>
          <div class="color-info">
            <strong>Secondary</strong> #d4954e<br>
            <span style="font-size: var(--font-size-sm); color: var(--color-gray);">Accents & highlights</span>
          </div>
        </div>
      </div>

      <div style="margin: var(--spacing-6) 0;">
        <h4>Status Colors</h4>
        <div>
          <div class="color-swatch" style="background-color: var(--color-success);"></div>
          <div class="color-info"><strong>Success</strong> #27ae60</div>
        </div>
        <div style="margin-top: var(--spacing-4);">
          <div class="color-swatch" style="background-color: var(--color-warning);"></div>
          <div class="color-info"><strong>Warning</strong> #f39c12</div>
        </div>
        <div style="margin-top: var(--spacing-4);">
          <div class="color-swatch" style="background-color: var(--color-accent);"></div>
          <div class="color-info"><strong>Danger</strong> #e74c3c</div>
        </div>
        <div style="margin-top: var(--spacing-4);">
          <div class="color-swatch" style="background-color: var(--color-info);"></div>
          <div class="color-info"><strong>Info</strong> #3498db</div>
        </div>
      </div>
    </section>

    <!-- TYPOGRAPHY -->
    <section class="showcase-section" id="typography">
      <h2>📝 Typography</h2>
      <p>Carefully selected fonts for readability and professional appearance.</p>
      
      <div style="margin: var(--spacing-6) 0;">
        <h3 style="font-family: var(--font-family-heading);">Heading 1 (Georgia, 48px)</h3>
        <h2 style="font-family: var(--font-family-heading);">Heading 2 (Georgia, 36px)</h2>
        <h3 style="font-family: var(--font-family-heading);">Heading 3 (Georgia, 30px)</h3>
        <h4>Heading 4 (System Font, 24px)</h4>
        <h5>Heading 5 (System Font, 20px)</h5>
        <h6>Heading 6 (System Font, 18px)</h6>
      </div>

      <div style="margin: var(--spacing-6) 0;">
        <p style="font-size: var(--font-size-lg);">Large paragraphs (18px) - Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        <p style="font-size: var(--font-size-base);">Base paragraph (16px) - The default body text size used throughout the site.</p>
        <p style="font-size: var(--font-size-sm);">Small text (14px) - Used for captions and helper text.</p>
      </div>

      <div style="margin: var(--spacing-6) 0;">
        <p style="font-weight: var(--font-weight-light);">Light weight (300)</p>
        <p style="font-weight: var(--font-weight-normal);">Normal weight (400) - Regular text</p>
        <p style="font-weight: var(--font-weight-semibold);">Semibold weight (600)</p>
        <p style="font-weight: var(--font-weight-bold);">Bold weight (700)</p>
      </div>
    </section>

    <!-- SPACING -->
    <section class="showcase-section" id="spacing">
      <h2>📐 Spacing Scale</h2>
      <p>Consistent spacing creates visual harmony and improves readability.</p>
      
      <div>
        <p>Spacing units (hover to see values):</p>
        <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-4);">
          <div class="spacing-demo" style="width: 20px; height: 20px; title='4px';" title="4px (--spacing-1)"></div>
          <div class="spacing-demo" style="width: 40px; height: 20px;" title="8px (--spacing-2)"></div>
          <div class="spacing-demo" style="width: 60px; height: 20px;" title="12px (--spacing-3)"></div>
          <div class="spacing-demo" style="width: 80px; height: 20px;" title="16px (--spacing-4)"></div>
          <div class="spacing-demo" style="width: 100px; height: 20px;" title="20px (--spacing-5)"></div>
          <div class="spacing-demo" style="width: 120px; height: 20px;" title="24px (--spacing-6)"></div>
          <div class="spacing-demo" style="width: 140px; height: 20px;" title="28px (--spacing-7)"></div>
          <div class="spacing-demo" style="width: 160px; height: 20px;" title="32px (--spacing-8)"></div>
        </div>
      </div>
    </section>

    <!-- BUTTONS -->
    <section class="showcase-section" id="buttons">
      <h2>🔘 Buttons</h2>
      <p>Six button variants for different use cases and hierarchy.</p>
      
      <h4>Primary Buttons</h4>
      <div class="component-grid">
        <div class="component-demo">
          <div class="component-label">Primary Button</div>
          <button class="btn btn-primary">Primary Button</button>
          <code>class="btn btn-primary"</code>
        </div>
        <div class="component-demo">
          <div class="component-label">Primary Small</div>
          <button class="btn btn-primary btn-sm">Small</button>
          <code>class="btn btn-primary btn-sm"</code>
        </div>
        <div class="component-demo">
          <div class="component-label">Primary Large</div>
          <button class="btn btn-primary btn-lg">Large</button>
          <code>class="btn btn-primary btn-lg"</code>
        </div>
      </div>

      <h4>Other Variants</h4>
      <div class="component-grid">
        <div class="component-demo">
          <div class="component-label">Secondary</div>
          <button class="btn btn-secondary">Secondary</button>
          <code>class="btn btn-secondary"</code>
        </div>
        <div class="component-demo">
          <div class="component-label">Outline</div>
          <button class="btn btn-outline">Outline</button>
          <code>class="btn btn-outline"</code>
        </div>
        <div class="component-demo">
          <div class="component-label">Success</div>
          <button class="btn btn-success">Success</button>
          <code>class="btn btn-success"</code>
        </div>
        <div class="component-demo">
          <div class="component-label">Danger</div>
          <button class="btn btn-danger">Danger</button>
          <code>class="btn btn-danger"</code>
        </div>
        <div class="component-demo">
          <div class="component-label">Ghost</div>
          <button class="btn btn-ghost">Ghost Link</button>
          <code>class="btn btn-ghost"</code>
        </div>
        <div class="component-demo">
          <div class="component-label">Disabled</div>
          <button class="btn btn-primary" disabled>Disabled</button>
          <code>class="btn btn-primary" disabled</code>
        </div>
      </div>

      <h4>Button Groups</h4>
      <div class="component-demo">
        <div class="component-label">Horizontal Group</div>
        <div class="btn-group">
          <button class="btn btn-primary">Save</button>
          <button class="btn btn-outline">Cancel</button>
          <button class="btn btn-danger">Delete</button>
        </div>
      </div>
    </section>

    <!-- FORMS -->
    <section class="showcase-section" id="forms">
      <h2>📋 Forms</h2>
      <p>Complete form styling with multiple states and validation.</p>
      
      <div class="component-demo">
        <div class="component-label">Basic Form</div>
        <form style="max-width: 400px;">
          <div class="form-group">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" id="name" class="form-control" placeholder="Enter name">
            <div class="form-text">Your full legal name</div>
          </div>
          <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" id="email" class="form-control" placeholder="your@email.com">
          </div>
          <div class="form-group">
            <label for="message" class="form-label">Message</label>
            <textarea id="message" class="form-control" placeholder="Your message here..."></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Submit</button>
        </form>
      </div>

      <h4 style="margin-top: var(--spacing-8);">Form States</h4>
      <div class="row">
        <div class="col">
          <div class="component-demo">
            <div class="component-label">Normal State</div>
            <input type="text" class="form-control" placeholder="Normal input" value="Normal">
          </div>
        </div>
        <div class="col">
          <div class="component-demo">
            <div class="component-label">Error State</div>
            <input type="text" class="form-control error" value="Invalid input">
            <div class="form-text error">This field has an error</div>
          </div>
        </div>
      </div>
      
      <div class="row">
        <div class="col">
          <div class="component-demo">
            <div class="component-label">Success State</div>
            <input type="text" class="form-control success" value="Valid input">
            <div class="form-text success">This looks great!</div>
          </div>
        </div>
        <div class="col">
          <div class="component-demo">
            <div class="component-label">Disabled State</div>
            <input type="text" class="form-control" disabled value="Disabled input">
          </div>
        </div>
      </div>
    </section>

    <!-- CARDS -->
    <section class="showcase-section" id="cards">
      <h2>🎴 Cards</h2>
      <p>Flexible card component for grouping related content.</p>
      
      <div class="row">
        <div class="col">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Card Title</h3>
            </div>
            <div class="card-body">
              <p>This card contains structured content with a header, body, and optional footer.</p>
            </div>
            <div class="card-footer">
              <button class="btn btn-primary btn-sm">Action</button>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card event-card">
            <div style="position: relative; overflow: hidden;">
              <div style="width: 100%; height: 150px; background: linear-gradient(135deg, var(--color-primary-light), var(--color-primary)); border-radius: var(--radius-lg) var(--radius-lg) 0 0;"></div>
            </div>
            <div class="card-body">
              <div class="event-card-date">15. Juni 2026</div>
              <h4 class="event-card-title">Sommerbegivenhed</h4>
              <p class="event-card-description">Slut dig til vores årlige sommerfest...</p>
              <button class="btn btn-primary btn-sm">Læs mere</button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ALERTS -->
    <section class="showcase-section" id="alerts">
      <h2>⚠️ Alerts</h2>
      <p>Status messages for feedback and important information.</p>
      
      <div>
        <div class="alert alert-success">
          <h4 class="alert-heading">✓ Success!</h4>
          <p>Your changes have been saved successfully.</p>
        </div>

        <div class="alert alert-info">
          <h4 class="alert-heading">ℹ️ Information</h4>
          <p>This is an informational message.</p>
        </div>

        <div class="alert alert-warning">
          <h4 class="alert-heading">⚠️ Warning</h4>
          <p>This action may have unexpected consequences.</p>
        </div>

        <div class="alert alert-danger alert-dismissible">
          <h4 class="alert-heading">✗ Error</h4>
          <p>An error occurred while processing your request.</p>
          <button class="alert-close" aria-label="Close"></button>
        </div>
      </div>
    </section>

    <!-- MODALS -->
    <section class="showcase-section" id="modals">
      <h2>🪟 Modals</h2>
      <p>Dialog boxes for important user interactions.</p>
      
      <button onclick="ModalComponent.open('#demoModal')" class="btn btn-primary">
        Open Demo Modal
      </button>

      <div class="modal" id="demoModal">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Modal Title</h4>
            <button class="modal-close" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>This is a modal dialog for important user interactions. It captures user attention and focus.</p>
            <p>Features:</p>
            <ul>
              <li>Focus trapping (keyboard navigation stays within modal)</li>
              <li>Backdrop trigger to close</li>
              <li>Escape key to close</li>
              <li>Accessible with ARIA attributes</li>
            </ul>
          </div>
          <div class="modal-footer">
            <button onclick="ModalComponent.close('#demoModal')" class="btn btn-outline">Cancel</button>
            <button class="btn btn-primary">Confirm</button>
          </div>
        </div>
      </div>
    </section>

    <!-- BADGES -->
    <section class="showcase-section" id="badges">
      <h2>🏷️ Badges</h2>
      <p>Small labeled components for highlighting status or categories.</p>
      
      <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-4);">
        <span class="badge badge-primary">Primary</span>
        <span class="badge badge-secondary">Secondary</span>
        <span class="badge badge-success">Success</span>
        <span class="badge badge-warning">Warning</span>
        <span class="badge badge-danger">Danger</span>
        <span class="badge badge-info">Info</span>
        <span class="badge badge-light">Light</span>
      </div>

      <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-4); margin-top: var(--spacing-6);">
        <span class="badge badge-primary badge-pill">Primary Pill</span>
        <span class="badge badge-success badge-pill">Success Pill</span>
        <span class="badge badge-danger badge-pill">Danger Pill</span>
      </div>
    </section>

    <!-- GRIDS -->
    <section class="showcase-section" id="grids">
      <h2>🔲 Grid Layouts</h2>
      <p>Responsive grid system that adapts to screen size.</p>
      
      <h4>2-Column Layout</h4>
      <div class="row">
        <div class="col">
          <div class="card">
            <div class="card-body">
              <h5>Column 1</h5>
              <p>This is the first column. On mobile, it stacks vertically.</p>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card">
            <div class="card-body">
              <h5>Column 2</h5>
              <p>This is the second column. Resize your browser to see it change.</p>
            </div>
          </div>
        </div>
      </div>

      <h4 style="margin-top: var(--spacing-8);">3-Column Layout</h4>
      <div class="row row-3">
        <div class="col">
          <div class="card">
            <div class="card-body">Item 1</div>
          </div>
        </div>
        <div class="col">
          <div class="card">
            <div class="card-body">Item 2</div>
          </div>
        </div>
        <div class="col">
          <div class="card">
            <div class="card-body">Item 3</div>
          </div>
        </div>
      </div>

      <h4 style="margin-top: var(--spacing-8);">4-Column Layout (Desktop Only)</h4>
      <div class="row" style="grid-template-columns: repeat(4, 1fr);">
        <div class="col">
          <div class="card">
            <div class="card-body">Item 1</div>
          </div>
        </div>
        <div class="col">
          <div class="card">
            <div class="card-body">Item 2</div>
          </div>
        </div>
        <div class="col">
          <div class="card">
            <div class="card-body">Item 3</div>
          </div>
        </div>
        <div class="col">
          <div class="card">
            <div class="card-body">Item 4</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ACCESSIBILITY -->
    <section class="showcase-section" id="accessibility">
      <h2>♿ Accessibility Features</h2>
      <p>All components are built with accessibility in mind.</p>
      
      <div class="row">
        <div class="col">
          <div class="card">
            <div class="card-body">
              <h4>Keyboard Navigation</h4>
              <p>All interactive elements are keyboard accessible:</p>
              <ul>
                <li><strong>Tab</strong> - Navigate forward</li>
                <li><strong>Shift+Tab</strong> - Navigate backward</li>
                <li><strong>Enter/Space</strong> - Activate buttons</li>
                <li><strong>Escape</strong> - Close modals</li>
                <li><strong>Arrow keys</strong> - Menu navigation</li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card">
            <div class="card-body">
              <h4>Screen Reader Support</h4>
              <p>Built with semantic HTML and ARIA attributes:</p>
              <ul>
                <li>Proper heading hierarchy</li>
                <li>Form labels associated with inputs</li>
                <li>ARIA labels for icons</li>
                <li>Role attributes for custom components</li>
                <li>Status regions for dynamic content</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="row" style="margin-top: var(--spacing-6);">
        <div class="col">
          <div class="card">
            <div class="card-body">
              <h4>Color Contrast</h4>
              <p>Color combinations meet WCAG AA standards:</p>
              <ul>
                <li>Normal text: 4.5:1 ratio</li>
                <li>Large text: 3:1 ratio</li>
                <li>Color not sole identifier</li>
                <li>Focus indicators visible</li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card">
            <div class="card-body">
              <h4>Responsive Design</h4>
              <p>Works well on all devices:</p>
              <ul>
                <li>Touch targets: 44x44px minimum</li>
                <li>Mobile-first approach</li>
                <li>Flexible text sizing</li>
                <li>Responsive images</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="device-test">
        <h4>🔍 Quick Accessibility Test</h4>
        <p>Try these on this page:</p>
        <ol>
          <li><strong>Hit Tab</strong> repeatedly - you should see focus indicators on all interactive elements</li>
          <li><strong>Press Escape</strong> - it closes the modal at the top</li>
          <li><strong>Right-click and Inspect</strong> - check that form labels are properly associated with inputs</li>
          <li><strong>Resize browser</strong> - layouts should adapt smoothly</li>
          <li><strong>Try keyboard only</strong> - navigate the entire page without mouse</li>
        </ol>
      </div>
    </section>

    <!-- RESPONSIVE TEST -->
    <section class="showcase-section">
      <h2>📱 Responsive Breakpoints</h2>
      <p>The design system responds to different screen sizes:</p>
      
      <div class="row row-3">
        <div class="col">
          <div class="card">
            <div class="card-body">
              <h5>📱 Mobile</h5>
              <p><strong>320px - 575px</strong></p>
              <p>Single column layouts, stacked elements, full-width components</p>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card">
            <div class="card-body">
              <h5>📋 Tablet</h5>
              <p><strong>576px - 992px</strong></p>
              <p>2-column layouts, responsive grids, optimized spacing</p>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card">
            <div class="card-body">
              <h5>🖥️ Desktop</h5>
              <p><strong>993px+</strong></p>
              <p>3+ column layouts, full horizontal menus, optimal whitespace</p>
            </div>
          </div>
        </div>
      </div>

      <div class="alert alert-info" style="margin-top: var(--spacing-8);">
        <h4 class="alert-heading">💡 Test Now</h4>
        <p>Open DevTools (F12 or Ctrl+Shift+I), click the responsive design mode button, and test different device sizes!</p>
      </div>
    </section>

    <!-- FOOTER INFO -->
    <section class="showcase-section" style="border-top: 3px solid var(--color-secondary);">
      <h2>✅ Review Checklist</h2>
      <p>Use this to verify Phase 1 completeness:</p>
      
      <div class="row row-2">
        <div class="col">
          <div class="card">
            <div class="card-body">
              <h5>Design System</h5>
              <ul>
                <li>✓ 100+ CSS variables</li>
                <li>✓ Color palette</li>
                <li>✓ Typography scale</li>
                <li>✓ Spacing system</li>
                <li>✓ Shadows & borders</li>
                <li>✓ Transitions & animations</li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card">
            <div class="card-body">
              <h5>Components</h5>
              <ul>
                <li>✓ Buttons (6 variants)</li>
                <li>✓ Forms & inputs</li>
                <li>✓ Cards</li>
                <li>✓ Alerts</li>
                <li>✓ Modals</li>
                <li>✓ Badges</li>
              </ul>
            </div>
          </div>
      </div>

      <div class="row row-2" style="margin-top: var(--spacing-6);">
        <div class="col">
          <div class="card">
            <div class="card-body">
              <h5>Accessibility</h5>
              <ul>
                <li>✓ Keyboard navigation</li>
                <li>✓ Screen reader support</li>
                <li>✓ Color contrast (WCAG AA)</li>
                <li>✓ Focus indicators</li>
                <li>✓ Touch targets (44x44px)</li>
                <li>✓ Semantic HTML</li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card">
            <div class="card-body">
              <h5>Responsive</h5>
              <ul>
                <li>✓ Mobile (320px)</li>
                <li>✓ Tablet (768px)</li>
                <li>✓ Desktop (1200px)</li>
                <li>✓ XL (1400px+)</li>
                <li>✓ Mobile-first approach</li>
                <li>✓ Flexible layouts</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <h4 style="margin-top: var(--spacing-8);">📚 Documentation Complete</h4>
      <ul>
        <li>✓ DESIGN_SYSTEM.md - Complete guide</li>
        <li>✓ QUICK_REFERENCE.md - Developer reference</li>
        <li>✓ PHASE1_SUMMARY.md - Phase 1 details</li>
        <li>✓ PHASE2_PLAN.md - Next phase plan</li>
        <li>✓ REDESIGN_README.md - Project overview</li>
      </ul>
    </section>

  </main>

  <footer>
    <div class="container footer-content">
      <div class="footer-section">
        <h3>Design System</h3>
        <p>Phase 1 - Design System & Components</p>
        <p style="font-size: var(--font-size-sm);">Status: ✅ Complete</p>
      </div>
      <div class="footer-section">
        <h3>Documentation</h3>
        <ul>
          <li><a href="/docs/DESIGN_SYSTEM.md">Design System Guide</a></li>
          <li><a href="/docs/QUICK_REFERENCE.md">Quick Reference</a></li>
          <li><a href="/docs/PHASE1_SUMMARY.md">Phase 1 Summary</a></li>
        </ul>
      </div>
      <div class="footer-section">
        <h3>Next Phase</h3>
        <p>Phase 2 - Backend Security & Authentication</p>
        <p style="font-size: var(--font-size-sm);">Ready to start after review approval</p>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2026 Skelby Forsamlinghus - Design System Showcase</p>
    </div>
  </footer>

  <script src="/assets/js/ui-components.js"></script>
</body>
</html>
