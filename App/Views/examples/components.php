<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>JS Components - Phuse Framework</title>
  <link rel="stylesheet" href="{{assetsUrl}}css/styles.css?v=141">
  <link rel="icon" type="image/svg+xml" href="{{assetsUrl}}images/favicon.svg">
  <script>
    (function() {
      try {
        var t = localStorage.getItem('phuse-theme');
        if (t) document.documentElement.setAttribute('data-theme', t);
      } catch (e) {}
    })()
  </script>
</head>

<body>
  <div class="container py-4">
    <div class="card shadow">

      <div class="card-header bg-secondary text-white p-4">
        <div class="text-center">
          <h1 class="display-5 fw-bold mb-1">JavaScript Components</h1>
          <p class="lead mb-0">
            Bootstrap 5.3.8-compatible - Alert, Button, Carousel, Modal, Offcanvas,
            Popover, ScrollSpy, Tooltip, Toast, Accordion
          </p>
        </div>
      </div>

      <div class="card-body p-4">

        {# - 1. Alerts --------------------------- #}
        <section class="mb-5">
          <h2 class="h4 text-primary mb-1"><i class="pi pi-info me-1"></i> Alert Components</h2>
          <p class="text-secondary small mb-3">
            Dismissible alerts via <code>data-dismiss="alert"</code> on the close button.
          </p>

          <div class="mb-3">
            <div class="alert alert-primary alert-dismissible fade show mb-2" role="alert">
              <strong>Primary:</strong> This is a primary alert with a dismiss button.
              <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"><i class="pi pi-x"></i></button>
            </div>
            <div class="alert alert-success alert-dismissible fade show mb-2" role="alert">
              <strong>Success:</strong> Operation completed successfully.
              <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"><i class="pi pi-x"></i></button>
            </div>
            <div class="alert alert-warning alert-dismissible fade show mb-2" role="alert">
              <strong>Warning:</strong> Please review before continuing.
              <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"><i class="pi pi-x"></i></button>
            </div>
            <div class="alert alert-danger alert-dismissible fade show mb-0" role="alert">
              <strong>Danger:</strong> Something went wrong. Please try again.
              <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"><i class="pi pi-x"></i></button>
            </div>
          </div>

          <pre class="code-block m-0"><code>&lt;div class="alert alert-primary alert-dismissible fade show" role="alert"&gt;
  &lt;strong&gt;Primary:&lt;/strong&gt; Alert message here.
  &lt;button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"&gt;&lt;i class="pi pi-x"&gt;&lt;/i&gt;&lt;/button&gt;
&lt;/div&gt;</code></pre>
        </section>

        {# - 2. Button Toggle -----------------------─ #}
        <section class="mb-5">
          <h2 class="h4 text-primary mb-1"><i class="pi pi-check me-1"></i> Button States &amp; Toggle</h2>
          <p class="text-secondary small mb-3">
            Buttons with <code>data-toggle="button"</code> maintain an active pressed state on click.
          </p>

          <div class="d-flex flex-wrap gap-2 mb-3">
            <button type="button" class="btn btn-primary" data-toggle="button">Primary</button>
            <button type="button" class="btn btn-secondary" data-toggle="button">Secondary</button>
            <button type="button" class="btn btn-success" data-toggle="button">Success</button>
            <button type="button" class="btn btn-danger" data-toggle="button">Danger</button>
            <button type="button" class="btn btn-outline-primary" data-toggle="button">Outline</button>
            <button type="button" class="btn btn-outline-secondary" data-toggle="button">Outline 2</button>
          </div>

          <pre class="code-block m-0"><code>&lt;button class="btn btn-primary" data-toggle="button"&gt;Toggle&lt;/button&gt;</code></pre>
        </section>

        {# - 3. Carousel -------------------------- #}
        <section class="mb-5">
          <h2 class="h4 text-primary mb-1"><i class="pi pi-arrow-right me-1"></i> Carousel</h2>
          <p class="text-secondary small mb-3">
            Cycled slide show with prev/next controls and dot indicators.
          </p>

          <div id="demo-carousel" class="carousel rounded overflow-hidden mb-3">
            <div class="carousel-inner">
              <div class="carousel-item active">
                <div class="d-flex align-items-center justify-content-center fw-bold fs-3 text-white py-5 bg-primary">
                  Slide 1
                </div>
              </div>
              <div class="carousel-item">
                <div class="d-flex align-items-center justify-content-center fw-bold fs-3 text-white py-5 bg-success">
                  Slide 2
                </div>
              </div>
              <div class="carousel-item">
                <div class="d-flex align-items-center justify-content-center fw-bold fs-3 text-white py-5 bg-info">
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

          <pre class="code-block m-0"><code>&lt;div id="my-carousel" class="carousel"&gt;
  &lt;div class="carousel-inner"&gt;
    &lt;div class="carousel-item active"&gt;...&lt;/div&gt;
    &lt;div class="carousel-item"&gt;...&lt;/div&gt;
  &lt;/div&gt;
  &lt;button class="carousel-control-prev" data-slide="prev"&gt;
    &lt;span class="carousel-control-prev-icon"&gt;&lt;/span&gt;
  &lt;/button&gt;
  &lt;button class="carousel-control-next" data-slide="next"&gt;
    &lt;span class="carousel-control-next-icon"&gt;&lt;/span&gt;
  &lt;/button&gt;
  &lt;div class="carousel-indicators"&gt;
    &lt;button data-slide-to="0" class="active"&gt;&lt;/button&gt;
    &lt;button data-slide-to="1"&gt;&lt;/button&gt;
  &lt;/div&gt;
&lt;/div&gt;</code></pre>
        </section>

        {# - 4. Offcanvas -------------------------─ #}
        <section class="mb-5">
          <h2 class="h4 text-primary mb-1"><i class="pi pi-menu me-1"></i> Offcanvas</h2>
          <p class="text-secondary small mb-3">
            A panel that slides in from the screen edge, triggered via <code>data-toggle="offcanvas"</code>.
          </p>

          <div class="d-flex flex-wrap gap-2 mb-3">
            <button class="btn btn-primary" type="button" data-toggle="offcanvas" data-target="#demo-offcanvas-end">
              <i class="pi pi-arrow-left me-1"></i> Open from Right
            </button>
            <button class="btn btn-outline-secondary" type="button" data-toggle="offcanvas" data-target="#demo-offcanvas-start">
              <i class="pi pi-arrow-right me-1"></i> Open from Left
            </button>
          </div>

          {# Right offcanvas #}
          <div class="offcanvas offcanvas-end" tabindex="-1" id="demo-offcanvas-end">
            <div class="offcanvas-header">
              <h5 class="offcanvas-title"><i class="pi pi-menu me-1"></i> Right Panel</h5>
              <button type="button" class="btn-close" data-dismiss="offcanvas" aria-label="Close"><i class="pi pi-x"></i></button>
            </div>
            <div class="offcanvas-body">
              <p class="text-secondary">This panel slides in from the right. Use it for navigation, filters, or any contextual content.</p>
              <hr>
              <div class="d-flex flex-column gap-2">
                <a href="#" class="btn btn-outline-primary btn-sm text-start"><i class="pi pi-home me-1"></i> Home</a>
                <a href="#" class="btn btn-outline-primary btn-sm text-start"><i class="pi pi-user me-1"></i> Profile</a>
                <a href="#" class="btn btn-outline-primary btn-sm text-start"><i class="pi pi-settings me-1"></i> Settings</a>
              </div>
            </div>
          </div>

          {# Left offcanvas #}
          <div class="offcanvas offcanvas-start" tabindex="-1" id="demo-offcanvas-start">
            <div class="offcanvas-header">
              <h5 class="offcanvas-title"><i class="pi pi-menu me-1"></i> Left Panel</h5>
              <button type="button" class="btn-close" data-dismiss="offcanvas" aria-label="Close"><i class="pi pi-x"></i></button>
            </div>
            <div class="offcanvas-body">
              <p class="text-secondary">This panel slides in from the left. Ideal for side navigation menus.</p>
              <hr>
              <nav class="d-flex flex-column gap-1">
                <a href="#" class="nav-link px-2 py-1 rounded"><i class="pi pi-grid me-1"></i> Dashboard</a>
                <a href="#" class="nav-link px-2 py-1 rounded"><i class="pi pi-file me-1"></i> Documents</a>
                <a href="#" class="nav-link px-2 py-1 rounded"><i class="pi pi-users me-1"></i> Team</a>
              </nav>
            </div>
          </div>

          <pre class="code-block m-0 mt-3"><code>&lt;button data-toggle="offcanvas" data-target="#my-panel"&gt;Open&lt;/button&gt;

&lt;div class="offcanvas offcanvas-end" id="my-panel"&gt;
  &lt;div class="offcanvas-header"&gt;
    &lt;h5 class="offcanvas-title"&gt;Panel Title&lt;/h5&gt;
    &lt;button class="btn-close" data-dismiss="offcanvas"&gt;&lt;i class="pi pi-x"&gt;&lt;/i&gt;&lt;/button&gt;
  &lt;/div&gt;
  &lt;div class="offcanvas-body"&gt;Panel content...&lt;/div&gt;
&lt;/div&gt;</code></pre>
        </section>

        {# - 5. Popover --------------------------─ #}
        <section class="mb-5">
          <h2 class="h4 text-primary mb-1"><i class="pi pi-info me-1"></i> Popover</h2>
          <p class="text-secondary small mb-3">
            Popovers appear on click and show a title and body content anchored to the trigger.
          </p>

          <div class="d-flex flex-wrap gap-2 mb-3">
            <button class="btn btn-primary" type="button"
              data-toggle="popover"
              title="Popover Title"
              data-content="This is a popover with content inside. Great for additional context.">
              Click for Popover
            </button>
            <button class="btn btn-secondary" type="button"
              data-toggle="popover"
              title="Another Popover"
              data-content="Popovers keep the UI clean by hiding details until requested.">
              Another Popover
            </button>
            <button class="btn btn-outline-primary" type="button"
              data-toggle="popover"
              title="Info"
              data-content="Popovers can hold any descriptive content you need.">
              Info Popover
            </button>
          </div>

          <pre class="code-block m-0"><code>&lt;button data-toggle="popover"
        title="Title"
        data-content="Body content here"&gt;
  Click
&lt;/button&gt;</code></pre>
        </section>

        {# - 6. Tooltip --------------------------─ #}
        <section class="mb-5">
          <h2 class="h4 text-primary mb-1"><i class="pi pi-eye me-1"></i> Tooltip</h2>
          <p class="text-secondary small mb-3">
            Tooltips appear on hover with a brief description. Supports top, bottom, left, and right placement.
          </p>

          <div class="d-flex flex-wrap gap-2 mb-3">
            <button class="btn btn-primary" data-toggle="tooltip" title="Primary tooltip">Primary</button>
            <button class="btn btn-success" data-toggle="tooltip" title="Success tooltip">Success</button>
            <button class="btn btn-danger" data-toggle="tooltip" title="Danger tooltip">Danger</button>
            <button class="btn btn-secondary" data-toggle="tooltip" data-placement="top" title="Top placement">Top</button>
            <button class="btn btn-outline-primary" data-toggle="tooltip" data-placement="bottom" title="Bottom placement">Bottom</button>
            <button class="btn btn-outline-secondary" data-toggle="tooltip" data-placement="left" title="Left placement">Left</button>
          </div>

          <pre class="code-block m-0"><code>&lt;button data-toggle="tooltip" title="Tooltip text"&gt;Hover me&lt;/button&gt;

&lt;!-- with placement --&gt;
&lt;button data-toggle="tooltip" data-placement="top"    title="Top"&gt;Top&lt;/button&gt;
&lt;button data-toggle="tooltip" data-placement="bottom" title="Bottom"&gt;Bottom&lt;/button&gt;</code></pre>
        </section>

        {# - 7. ScrollSpy -------------------------─ #}
        <section class="mb-5">
          <h2 class="h4 text-primary mb-1"><i class="pi pi-list me-1"></i> ScrollSpy</h2>
          <p class="text-secondary small mb-3">
            Highlights the active nav link as the user scrolls through the content pane.
          </p>

          <div class="row g-3 mb-3">
            <div class="col-12 col-sm-4 col-lg-3">
              <nav id="scrollspy-nav" class="card p-3">
                <p class="text-secondary fw-semibold small text-uppercase mb-2" style="letter-spacing:.05em;">Contents</p>
                <nav class="nav flex-column gap-1">
                  <a class="nav-link py-1 px-2 rounded" href="#spy-1">Item 1</a>
                  <a class="nav-link py-1 px-2 rounded" href="#spy-2">Item 2</a>
                  <a class="nav-link py-1 px-2 rounded" href="#spy-3">Item 3</a>
                  <a class="nav-link py-1 px-2 rounded" href="#spy-4">Item 4</a>
                </nav>
              </nav>
            </div>
            <div class="col-12 col-sm-8 col-lg-9">
              <div data-spy="scroll" data-target="#scrollspy-nav" data-offset="0"
                class="card p-3" style="height:220px;overflow-y:scroll;" tabindex="0">
                <h5 id="spy-1" class="text-primary mb-1">Item 1</h5>
                <p class="text-secondary small mb-4">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                <h5 id="spy-2" class="text-primary mb-1">Item 2</h5>
                <p class="text-secondary small mb-4">Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat.</p>
                <h5 id="spy-3" class="text-primary mb-1">Item 3</h5>
                <p class="text-secondary small mb-4">Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa.</p>
                <h5 id="spy-4" class="text-primary mb-1">Item 4</h5>
                <p class="text-secondary small mb-0">Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione.</p>
              </div>
            </div>
          </div>

          <pre class="code-block m-0"><code>&lt;nav id="spy-nav"&gt;
  &lt;a href="#section1"&gt;Section 1&lt;/a&gt;
  &lt;a href="#section2"&gt;Section 2&lt;/a&gt;
&lt;/nav&gt;

&lt;div data-spy="scroll" data-target="#spy-nav" style="height:200px;overflow-y:scroll;"&gt;
  &lt;h4 id="section1"&gt;Section 1&lt;/h4&gt;&lt;p&gt;...&lt;/p&gt;
  &lt;h4 id="section2"&gt;Section 2&lt;/h4&gt;&lt;p&gt;...&lt;/p&gt;
&lt;/div&gt;</code></pre>
        </section>

        {# - 8. Toast ---------------------------─ #}
        <section class="mb-5">
          <h2 class="h4 text-primary mb-1"><i class="pi pi-bell me-1"></i> Toast Notifications</h2>
          <p class="text-secondary small mb-3">
            Non-blocking, auto-dismissing messages via <code>Phuse.toast(message, type)</code>.
          </p>

          <div class="d-flex flex-wrap gap-2 mb-3">
            <button class="btn btn-success" onclick="showToast('success')">
              <i class="pi pi-check-circle me-1"></i> Success
            </button>
            <button class="btn btn-danger" onclick="showToast('error')">
              <i class="pi pi-x-circle me-1"></i> Error
            </button>
            <button class="btn btn-info" onclick="showToast('info')">
              <i class="pi pi-info me-1"></i> Info
            </button>
            <button class="btn btn-warning" onclick="showToast('warning')">
              <i class="pi pi-alert-triangle me-1"></i> Warning
            </button>
          </div>

          <pre class="code-block m-0"><code>Phuse.toast('Operation completed!', 'success');
Phuse.toast('An error occurred.',   'error');
Phuse.toast('Heads up!',            'info');
Phuse.toast('Please check input.',  'warning');</code></pre>
        </section>

        {# - 9. Modal ---------------------------─ #}
        <section class="mb-5">
          <h2 class="h4 text-primary mb-1"><i class="pi pi-layers me-1"></i> Modal</h2>
          <p class="text-secondary small mb-3">
            Dialog overlays with optional sizes and a static backdrop mode. Closes on
            <kbd>Esc</kbd>, backdrop click, or an explicit dismiss button.
          </p>

          <div class="d-flex flex-wrap gap-2 mb-3">
            <button class="btn btn-primary" type="button"
              data-toggle="modal" data-target="#demo-modal-basic">
              <i class="pi pi-layers me-1"></i> Basic Modal
            </button>
            <button class="btn btn-secondary" type="button"
              data-toggle="modal" data-target="#demo-modal-lg">
              <i class="pi pi-layers me-1"></i> Large Modal
            </button>
            <button class="btn btn-outline-danger" type="button"
              data-toggle="modal" data-target="#demo-modal-static">
              <i class="pi pi-shield me-1"></i> Static Backdrop
            </button>
          </div>

          {# Basic modal #}
          <div class="modal fade" id="demo-modal-basic" tabindex="-1" role="dialog" aria-labelledby="modal-basic-title" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="modal-basic-title">
                    <i class="pi pi-info me-1 text-primary"></i> Basic Modal
                  </h5>
                  <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"><i class="pi pi-x"></i></button>
                </div>
                <div class="modal-body">
                  <p class="mb-2">This is a standard modal dialog. It closes when you click the backdrop, press <kbd>Esc</kbd>, or click the close button.</p>
                  <p class="text-secondary small mb-0">Use modals for confirmations, forms, detail views, or any content that needs focused attention.</p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                    <i class="pi pi-x me-1"></i> Cancel
                  </button>
                  <button type="button" class="btn btn-primary" data-dismiss="modal">
                    <i class="pi pi-check me-1"></i> Confirm
                  </button>
                </div>
              </div>
            </div>
          </div>

          {# Large modal #}
          <div class="modal fade modal-lg" id="demo-modal-lg" tabindex="-1" role="dialog" aria-labelledby="modal-lg-title" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="modal-lg-title">
                    <i class="pi pi-file me-1 text-primary"></i> Large Modal
                  </h5>
                  <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"><i class="pi pi-x"></i></button>
                </div>
                <div class="modal-body">
                  <p class="mb-3">A <code>modal-lg</code> modal expands to 800 px max-width - ideal for data tables, image galleries, or rich forms.</p>
                  <div class="row g-3">
                    <div class="col-sm-6">
                      <div class="card p-3">
                        <h6 class="fw-semibold mb-1"><i class="pi pi-user me-1 text-primary"></i> User Info</h6>
                        <p class="text-secondary small mb-0">Name, email, role and account details would go here.</p>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="card p-3">
                        <h6 class="fw-semibold mb-1"><i class="pi pi-settings me-1 text-primary"></i> Preferences</h6>
                        <p class="text-secondary small mb-0">Notification settings, theme and locale options here.</p>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="card p-3">
                        <h6 class="fw-semibold mb-1"><i class="pi pi-shield me-1 text-primary"></i> Security</h6>
                        <p class="text-secondary small mb-0">Two-factor auth, active sessions, password change.</p>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="card p-3">
                        <h6 class="fw-semibold mb-1"><i class="pi pi-zap me-1 text-primary"></i> Activity</h6>
                        <p class="text-secondary small mb-0">Recent logins, actions and audit log entries.</p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                  <button type="button" class="btn btn-primary" data-dismiss="modal">
                    <i class="pi pi-check me-1"></i> Save Changes
                  </button>
                </div>
              </div>
            </div>
          </div>

          {# Static backdrop modal #}
          <div class="modal fade" id="demo-modal-static" tabindex="-1" role="dialog"
            data-backdrop="static" aria-labelledby="modal-static-title" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="modal-static-title">
                    <i class="pi pi-alert-triangle me-1 text-danger"></i> Static Backdrop
                  </h5>
                </div>
                <div class="modal-body">
                  <p class="mb-2">This modal uses <code>data-backdrop="static"</code>. Clicking the backdrop or pressing <kbd>Esc</kbd> will <strong>not</strong> close it.</p>
                  <p class="text-secondary small mb-0">Use for critical confirmations or unsaved-changes warnings where dismissal must be deliberate.</p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                    <i class="pi pi-x me-1"></i> Dismiss
                  </button>
                  <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <i class="pi pi-check me-1"></i> I Understand
                  </button>
                </div>
              </div>
            </div>
          </div>

          <pre class="code-block m-0 mt-3"><code>&lt;!-- Trigger --&gt;
&lt;button data-toggle="modal" data-target="#my-modal"&gt;Open&lt;/button&gt;

&lt;!-- Modal --&gt;
&lt;div class="modal fade" id="my-modal" tabindex="-1"&gt;
  &lt;div class="modal-dialog"&gt;
    &lt;div class="modal-content"&gt;
      &lt;div class="modal-header"&gt;
        &lt;h5 class="modal-title"&gt;Title&lt;/h5&gt;
        &lt;button class="btn-close" data-dismiss="modal"&gt;&lt;i class="pi pi-x"&gt;&lt;/i&gt;&lt;/button&gt;
      &lt;/div&gt;
      &lt;div class="modal-body"&gt;Content here.&lt;/div&gt;
      &lt;div class="modal-footer"&gt;
        &lt;button class="btn btn-secondary" data-dismiss="modal"&gt;Cancel&lt;/button&gt;
        &lt;button class="btn btn-primary"&gt;Confirm&lt;/button&gt;
      &lt;/div&gt;
    &lt;/div&gt;
  &lt;/div&gt;
&lt;/div&gt;

&lt;!-- Sizes: add class to .modal --&gt;
&lt;div class="modal fade modal-sm"&gt;...&lt;/div&gt;
&lt;div class="modal fade modal-lg"&gt;...&lt;/div&gt;
&lt;div class="modal fade modal-xl"&gt;...&lt;/div&gt;

&lt;!-- Static backdrop (no click/Esc dismiss) --&gt;
&lt;div class="modal fade" data-backdrop="static"&gt;...&lt;/div&gt;</code></pre>
        </section>

        {# - 10. Accordion -------------------------─ #}
        <section class="mb-4">
          <h2 class="h4 text-primary mb-1"><i class="pi pi-chevron-down me-1"></i> Accordion</h2>
          <p class="text-secondary small mb-3">
            Collapsible panels - click a header to expand or collapse its body.
          </p>

          <div class="accordion mb-3">
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button" type="button">Accordion Item #1</button>
              </h2>
              <div class="accordion-body">
                <strong>First item - expanded by default.</strong> These classes control the overall appearance
                as well as showing and hiding via CSS transitions. You can place any content here.
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button">Accordion Item #2</button>
              </h2>
              <div class="accordion-body" style="max-height:0;padding-top:0;padding-bottom:0;overflow:hidden;">
                <strong>Second item - collapsed by default.</strong> Click the header above to reveal this content.
                The collapse plugin adds the appropriate classes to toggle visibility.
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button">Accordion Item #3</button>
              </h2>
              <div class="accordion-body" style="max-height:0;padding-top:0;padding-bottom:0;overflow:hidden;">
                <strong>Third item - also collapsed.</strong> Each accordion item operates independently
                unless you wire them together to close siblings on open.
              </div>
            </div>
          </div>

          <pre class="code-block m-0"><code>&lt;div class="accordion"&gt;
  &lt;div class="accordion-item"&gt;
    &lt;h2 class="accordion-header"&gt;
      &lt;button class="accordion-button"&gt;Item #1&lt;/button&gt;
    &lt;/h2&gt;
    &lt;div class="accordion-body"&gt;Content here.&lt;/div&gt;
  &lt;/div&gt;
  &lt;div class="accordion-item"&gt;
    &lt;h2 class="accordion-header"&gt;
      &lt;button class="accordion-button collapsed"&gt;Item #2&lt;/button&gt;
    &lt;/h2&gt;
    &lt;div class="accordion-body" style="max-height:0;padding-top:0;padding-bottom:0;overflow:hidden;"&gt;Content here.&lt;/div&gt;
  &lt;/div&gt;
&lt;/div&gt;</code></pre>
        </section>

      </div>{# /card-body #}

      <div class="card-footer text-center text-secondary py-3">
        <p class="mb-0">Phuse Framework - JavaScript Components &copy; {{year}}</p>
      </div>

    </div>{# /card #}
  </div>{# /container #}

  <script>
    function showToast(type) {
      var messages = {
        success: 'Operation completed successfully!',
        error: 'An error occurred. Please try again.',
        info: 'This is an informational message.',
        warning: 'Please check your input before continuing.'
      };
      Phuse.toast(messages[type], type);
    }
  </script>

  <script src="{{assetsUrl}}js/scripts.js?v=136"></script>
</body>

</html>