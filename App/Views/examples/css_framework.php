<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CSS Framework Examples - Phuse Framework</title>
  <link rel="stylesheet" href="{assetsUrl}css/styles.css">
</head>

<body>
  <div class="container py-4">
    <div class="row">
      <div class="col-12">
        <div class="card shadow">
          <div class="card-header bg-primary text-white">
            <h1 class="h3 mb-0">🎨 Phuse CSS Framework Examples</h1>
          </div>
          <div class="card-body">
            <p class="lead text-secondary mb-4">
              Explore the modern CSS framework features including grid system, components, and utilities optimized for dark themes.
            </p>

            <!-- Grid System Examples -->
            <section class="mb-5">
              <h2 class="h4 text-primary mb-3">📱 Grid System</h2>

              <h5 class="text-secondary mb-3">Basic Grid (Equal Columns)</h5>
              <div class="row g-3 mb-4">
                <div class="col">
                  <div class="card bg-secondary text-white">
                    <div class="card-body p-3 text-center">
                      <strong>Column 1</strong>
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="card bg-secondary text-white">
                    <div class="card-body p-3 text-center">
                      <strong>Column 2</strong>
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="card bg-secondary text-white">
                    <div class="card-body p-3 text-center">
                      <strong>Column 3</strong>
                    </div>
                  </div>
                </div>
              </div>

              <h5 class="text-secondary mb-3">Responsive Grid (3 Cards Per Row)</h5>
              <div class="row g-3 mb-4">
                <div class="col-lg-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <h6 class="card-title text-primary">Card 1</h6>
                      <p class="card-text text-secondary">This demonstrates the 3-card layout that was fixed in the grid system.</p>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <h6 class="card-title text-primary">Card 2</h6>
                      <p class="card-text text-secondary">Each card takes exactly 1/3 of the container width on large screens.</p>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="card h-100">
                    <div class="card-body">
                      <h6 class="card-title text-primary">Card 3</h6>
                      <p class="card-text text-secondary">Proper gap spacing ensures consistent visual rhythm.</p>
                    </div>
                  </div>
                </div>
              </div>

              <h5 class="text-secondary mb-3">Auto-sizing Columns</h5>
              <div class="row g-3 mb-4">
                <div class="col-auto">
                  <div class="card bg-tertiary">
                    <div class="card-body p-3">
                      <strong>Auto-sized</strong>
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="card bg-tertiary">
                    <div class="card-body p-3">
                      <strong>Remaining Space</strong><br>
                      <small class="text-secondary">This column takes up all remaining space</small>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <!-- Component Examples -->
            <section class="mb-5">
              <h2 class="h4 text-primary mb-3">🧩 Components</h2>

              <h5 class="text-secondary mb-3">Enhanced Alerts (Dark Theme Optimized)</h5>
              <div class="mb-4">
                <div class="alert alert-primary mb-3">
                  <strong>Primary Alert:</strong> Highly visible with light text and accent border for dark themes.
                </div>
                <div class="alert alert-success mb-3">
                  <strong>Success:</strong> Operation completed successfully with enhanced contrast.
                </div>
                <div class="alert alert-danger mb-3">
                  <strong>Error:</strong> Something went wrong - clearly visible on dark backgrounds.
                </div>
                <div class="alert alert-warning mb-3">
                  <strong>Warning:</strong> Please check your input with improved readability.
                </div>
                <div class="alert alert-info mb-3">
                  <strong>Info:</strong> Here's some important information with better visibility.
                </div>
              </div>

              <h5 class="text-secondary mb-3">Compact Badges (Content-width Only)</h5>
              <div class="mb-4">
                <span class="badge bg-primary me-2">Primary</span>
                <span class="badge bg-success me-2">Success</span>
                <span class="badge bg-danger me-2">Danger</span>
                <span class="badge bg-warning me-2">Warning</span>
                <span class="badge bg-info me-2">Info</span>
                <span class="badge bg-secondary me-2">Secondary</span>
              </div>

              <div class="mb-4">
                <button class="btn btn-primary me-2">
                  Messages <span class="badge bg-light text-dark ms-2">4</span>
                </button>
                <button class="btn btn-secondary">
                  Notifications <span class="badge bg-danger ms-2">12</span>
                </button>
              </div>
            </section>

            <!-- Utility Examples -->
            <section class="mb-5">
              <h2 class="h4 text-primary mb-3">🎯 Utilities</h2>

              <h5 class="text-secondary mb-3">Modern Spacing Scale</h5>
              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-body">
                      <h6 class="card-title">Standard Spacing (0-5)</h6>
                      <div class="p-3 bg-tertiary rounded mb-2">
                        <code>.p-3</code> - Standard padding
                      </div>
                      <div class="m-2 bg-secondary rounded text-white text-center">
                        <code>.m-2</code> - Standard margin
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-body">
                      <h6 class="card-title">Extended Spacing (6-8)</h6>
                      <div class="p-6 bg-tertiary rounded mb-2">
                        <code>.p-6</code> - Extended padding
                      </div>
                      <div class="mt-7 bg-secondary rounded text-white p-3">
                        <code>.mt-7</code> - Extended margin
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <h5 class="text-secondary mb-3">Flexbox Utilities</h5>
              <div class="card mb-4">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center p-3 bg-tertiary rounded mb-3">
                    <div class="badge bg-primary">Start</div>
                    <div class="badge bg-success">Center</div>
                    <div class="badge bg-danger">End</div>
                  </div>
                  <code>d-flex justify-content-between align-items-center</code>
                </div>
              </div>

              <h5 class="text-secondary mb-3">Responsive Display</h5>
              <div class="card mb-4">
                <div class="card-body">
                  <div class="d-none d-md-block badge bg-info mb-2">
                    Visible on medium screens and up
                  </div>
                  <div class="d-block d-lg-none badge bg-warning">
                    Hidden on large screens and up
                  </div>
                </div>
              </div>
            </section>

            <!-- Dark Theme Features -->
            <section class="mb-5">
              <h2 class="h4 text-primary mb-3">🌙 Dark Theme Optimizations</h2>

              <div class="row g-4">
                <div class="col-lg-6">
                  <div class="card h-100">
                    <div class="card-header">
                      <h6 class="mb-0">Color Hierarchy</h6>
                    </div>
                    <div class="card-body">
                      <div class="p-3 mb-2 bg-primary text-white rounded">Primary Background</div>
                      <div class="p-3 mb-2 bg-secondary text-white rounded">Secondary Background</div>
                      <div class="p-3 bg-tertiary text-white rounded">Tertiary Background</div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="card h-100">
                    <div class="card-header">
                      <h6 class="mb-0">Text Contrast</h6>
                    </div>
                    <div class="card-body">
                      <p class="text-primary mb-2">Primary text for headings</p>
                      <p class="text-secondary mb-2">Secondary text for subtitles</p>
                      <p class="text-muted">Muted text for less important content</p>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <!-- CSS Variables Demo -->
            <section class="mb-5">
              <h2 class="h4 text-primary mb-3">🎨 CSS Variables (Phuse System)</h2>

              <div class="card">
                <div class="card-body">
                  <p class="text-secondary mb-3">
                    The framework uses <code>--ps-</code> prefixed variables instead of Bootstrap's <code>--bs-</code> for Phuse-specific customization:
                  </p>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <div class="p-3 border rounded">
                        <strong>Grid Spacing:</strong><br>
                        <code>--ps-gutter-x: 1.5rem</code><br>
                        <code>--ps-gutter-y: 0</code>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="p-3 border rounded">
                        <strong>Colors:</strong><br>
                        <code>--primary: #0d6efd</code><br>
                        <code>--bg-primary: #121212</code>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <!-- Performance Note -->
            <div class="alert alert-info">
              <h6 class="alert-heading mb-2">⚡ Performance Optimized</h6>
              <p class="mb-0">
                The Phuse CSS Framework is optimized for performance with modern CSS techniques,
                efficient selectors, and minimal footprint while maintaining full Bootstrap 5+ compatibility.
              </p>
            </div>
          </div>

          <div class="card-footer text-center text-secondary">
            <p class="mb-0">
              Phuse CSS Framework - Modern, Dark Theme Optimized, Bootstrap Compatible
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html></content>
