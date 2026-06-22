<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{title}} - Phuse Framework</title>
  <link rel="stylesheet" href="{{assetsUrl}}css/styles.css?v=139">
  <script>
    (function() {
      try {
        var t = localStorage.getItem('phuse-theme');
        if (t) document.documentElement.setAttribute('data-theme', t);
      } catch (e) {}
    })()
  </script>
  <style>
    .icon-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
      gap: .75rem;
    }

    .icon-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: .4rem;
      padding: .75rem .5rem;
      border: 1px solid var(--ps-border, #E2E8F0);
      border-radius: 6px;
      cursor: default;
      transition: background .15s, border-color .15s;
      text-align: center;
    }

    .icon-item:hover {
      background: var(--ps-bg-surface, #F8FAFC);
      border-color: var(--ps-primary, #2563EB);
    }

    .icon-item .pi {
      font-size: 1.5rem;
      color: var(--ps-primary, #2563EB);
    }

    .icon-item .icon-name {
      font-family: monospace;
      font-size: .7rem;
      color: var(--ps-text-muted, #94A3B8);
      word-break: break-all;
    }

    .demo-section {
      margin-bottom: 2.5rem;
    }

    .demo-section h5 {
      font-weight: 600;
      margin-bottom: 1rem;
      color: var(--ps-text-primary, #0F172A);
    }

    .color-row {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 1rem;
    }

    .size-row {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-end;
      gap: 1.25rem;
    }

    .size-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: .3rem;
    }

    .size-label {
      font-size: .7rem;
      color: var(--ps-text-muted, #94A3B8);
      font-family: monospace;
    }

    .code-block {
      background: #1e1e2e;
      color: #cdd6f4;
      border-radius: 6px;
      padding: 1rem 1.25rem;
      font-family: monospace;
      font-size: .875rem;
      line-height: 1.6;
      overflow-x: auto;
      white-space: pre;
    }
  </style>
</head>

<body>
  <div class="container py-4">

    {# - Header - #}
    <div class="card mb-4">
      <div class="card-body text-center py-4">
        <span class="badge bg-primary mb-2">v1.2.4</span>
        <h1 class="fw-bold mb-1">
          <i class="pi pi-zap me-2 text-primary"></i>Phuse Icon System
        </h1>
        <p class="lead text-secondary mb-0">
          Flat hollow SVG icons as CSS classes - no icon font, no external files.
        </p>
        <p class="text-muted small mt-1">
          Uses CSS <code>mask-image</code> with inline data URIs. Color inherits from <code>currentColor</code>.
        </p>
      </div>
    </div>

    {# - How to use - #}
    <h6 class="text-muted text-uppercase fw-semibold mb-3 ls-wide">How to Use</h6>
    <div class="card mb-5">
      <div class="card-body p-0">
        <div class="code-block"><!-- Basic usage: two classes - base .pi + icon name .pi-{name} -->
          &lt;i class="pi pi-home"&gt;&lt;/i&gt;

          <!-- Size utilities: pi-sm, pi-lg, pi-xl, pi-2x, pi-3x, pi-4x -->
          &lt;i class="pi pi-star pi-2x"&gt;&lt;/i&gt;

          <!-- Color comes from the parent text color (currentColor) -->
          &lt;i class="pi pi-check text-success"&gt;&lt;/i&gt;
          &lt;i class="pi pi-x-circle text-danger"&gt;&lt;/i&gt;

          <!-- Icons inside buttons work seamlessly -->
          &lt;button class="btn btn-primary"&gt;
          &lt;i class="pi pi-download me-1"&gt;&lt;/i&gt; Download
          &lt;/button&gt;
        </div>
      </div>
    </div>

    {# - All icons grid - #}
    <h6 class="text-muted text-uppercase fw-semibold mb-3 ls-wide">All Icons</h6>

    <div class="card mb-5">
      <div class="card-header">
        <span class="fw-semibold"><i class="pi pi-grid me-1 text-primary"></i> Navigation &amp; UI</span>
      </div>
      <div class="card-body">
        <div class="icon-grid">
          <div class="icon-item"><i class="pi pi-home"></i><span class="icon-name">pi-home</span></div>
          <div class="icon-item"><i class="pi pi-menu"></i><span class="icon-name">pi-menu</span></div>
          <div class="icon-item"><i class="pi pi-x"></i><span class="icon-name">pi-x</span></div>
          <div class="icon-item"><i class="pi pi-search"></i><span class="icon-name">pi-search</span></div>
          <div class="icon-item"><i class="pi pi-filter"></i><span class="icon-name">pi-filter</span></div>
          <div class="icon-item"><i class="pi pi-grid"></i><span class="icon-name">pi-grid</span></div>
          <div class="icon-item"><i class="pi pi-list"></i><span class="icon-name">pi-list</span></div>
          <div class="icon-item"><i class="pi pi-chevron-left"></i><span class="icon-name">pi-chevron-left</span></div>
          <div class="icon-item"><i class="pi pi-chevron-right"></i><span class="icon-name">pi-chevron-right</span></div>
          <div class="icon-item"><i class="pi pi-chevron-up"></i><span class="icon-name">pi-chevron-up</span></div>
          <div class="icon-item"><i class="pi pi-chevron-down"></i><span class="icon-name">pi-chevron-down</span></div>
          <div class="icon-item"><i class="pi pi-arrow-left"></i><span class="icon-name">pi-arrow-left</span></div>
          <div class="icon-item"><i class="pi pi-arrow-right"></i><span class="icon-name">pi-arrow-right</span></div>
          <div class="icon-item"><i class="pi pi-arrow-up"></i><span class="icon-name">pi-arrow-up</span></div>
          <div class="icon-item"><i class="pi pi-arrow-down"></i><span class="icon-name">pi-arrow-down</span></div>
          <div class="icon-item"><i class="pi pi-external-link"></i><span class="icon-name">pi-external-link</span></div>
          <div class="icon-item"><i class="pi pi-sliders"></i><span class="icon-name">pi-sliders</span></div>
        </div>
      </div>
    </div>

    <div class="card mb-5">
      <div class="card-header">
        <span class="fw-semibold"><i class="pi pi-user me-1 text-primary"></i> Users &amp; Auth</span>
      </div>
      <div class="card-body">
        <div class="icon-grid">
          <div class="icon-item"><i class="pi pi-user"></i><span class="icon-name">pi-user</span></div>
          <div class="icon-item"><i class="pi pi-users"></i><span class="icon-name">pi-users</span></div>
          <div class="icon-item"><i class="pi pi-lock"></i><span class="icon-name">pi-lock</span></div>
          <div class="icon-item"><i class="pi pi-settings"></i><span class="icon-name">pi-settings</span></div>
          <div class="icon-item"><i class="pi pi-bell"></i><span class="icon-name">pi-bell</span></div>
          <div class="icon-item"><i class="pi pi-mail"></i><span class="icon-name">pi-mail</span></div>
          <div class="icon-item"><i class="pi pi-phone"></i><span class="icon-name">pi-phone</span></div>
          <div class="icon-item"><i class="pi pi-shield"></i><span class="icon-name">pi-shield</span></div>
        </div>
      </div>
    </div>

    <div class="card mb-5">
      <div class="card-header">
        <span class="fw-semibold"><i class="pi pi-check-circle me-1 text-primary"></i> Status &amp; Feedback</span>
      </div>
      <div class="card-body">
        <div class="icon-grid">
          <div class="icon-item"><i class="pi pi-check"></i><span class="icon-name">pi-check</span></div>
          <div class="icon-item"><i class="pi pi-check-circle"></i><span class="icon-name">pi-check-circle</span></div>
          <div class="icon-item"><i class="pi pi-x-circle"></i><span class="icon-name">pi-x-circle</span></div>
          <div class="icon-item"><i class="pi pi-info"></i><span class="icon-name">pi-info</span></div>
          <div class="icon-item"><i class="pi pi-alert-triangle"></i><span class="icon-name">pi-alert-triangle</span></div>
          <div class="icon-item"><i class="pi pi-plus"></i><span class="icon-name">pi-plus</span></div>
          <div class="icon-item"><i class="pi pi-minus"></i><span class="icon-name">pi-minus</span></div>
          <div class="icon-item"><i class="pi pi-eye"></i><span class="icon-name">pi-eye</span></div>
          <div class="icon-item"><i class="pi pi-eye-off"></i><span class="icon-name">pi-eye-off</span></div>
          <div class="icon-item"><i class="pi pi-heart"></i><span class="icon-name">pi-heart</span></div>
          <div class="icon-item"><i class="pi pi-star"></i><span class="icon-name">pi-star</span></div>
          <div class="icon-item"><i class="pi pi-bookmark"></i><span class="icon-name">pi-bookmark</span></div>
        </div>
      </div>
    </div>

    <div class="card mb-5">
      <div class="card-header">
        <span class="fw-semibold"><i class="pi pi-file me-1 text-primary"></i> Files &amp; Data</span>
      </div>
      <div class="card-body">
        <div class="icon-grid">
          <div class="icon-item"><i class="pi pi-file"></i><span class="icon-name">pi-file</span></div>
          <div class="icon-item"><i class="pi pi-folder"></i><span class="icon-name">pi-folder</span></div>
          <div class="icon-item"><i class="pi pi-image"></i><span class="icon-name">pi-image</span></div>
          <div class="icon-item"><i class="pi pi-tag"></i><span class="icon-name">pi-tag</span></div>
          <div class="icon-item"><i class="pi pi-copy"></i><span class="icon-name">pi-copy</span></div>
          <div class="icon-item"><i class="pi pi-save"></i><span class="icon-name">pi-save</span></div>
          <div class="icon-item"><i class="pi pi-download"></i><span class="icon-name">pi-download</span></div>
          <div class="icon-item"><i class="pi pi-upload"></i><span class="icon-name">pi-upload</span></div>
          <div class="icon-item"><i class="pi pi-edit"></i><span class="icon-name">pi-edit</span></div>
          <div class="icon-item"><i class="pi pi-trash"></i><span class="icon-name">pi-trash</span></div>
          <div class="icon-item"><i class="pi pi-refresh"></i><span class="icon-name">pi-refresh</span></div>
          <div class="icon-item"><i class="pi pi-database"></i><span class="icon-name">pi-database</span></div>
        </div>
      </div>
    </div>

    <div class="card mb-5">
      <div class="card-header">
        <span class="fw-semibold"><i class="pi pi-code me-1 text-primary"></i> Dev &amp; Tech</span>
      </div>
      <div class="card-body">
        <div class="icon-grid">
          <div class="icon-item"><i class="pi pi-code"></i><span class="icon-name">pi-code</span></div>
          <div class="icon-item"><i class="pi pi-terminal"></i><span class="icon-name">pi-terminal</span></div>
          <div class="icon-item"><i class="pi pi-layers"></i><span class="icon-name">pi-layers</span></div>
          <div class="icon-item"><i class="pi pi-cpu"></i><span class="icon-name">pi-cpu</span></div>
          <div class="icon-item"><i class="pi pi-github"></i><span class="icon-name">pi-github</span></div>
          <div class="icon-item"><i class="pi pi-link"></i><span class="icon-name">pi-link</span></div>
          <div class="icon-item"><i class="pi pi-globe"></i><span class="icon-name">pi-globe</span></div>
          <div class="icon-item"><i class="pi pi-zap"></i><span class="icon-name">pi-zap</span></div>
        </div>
      </div>
    </div>

    <div class="card mb-5">
      <div class="card-header">
        <span class="fw-semibold"><i class="pi pi-calendar me-1 text-primary"></i> Time &amp; Location</span>
      </div>
      <div class="card-body">
        <div class="icon-grid">
          <div class="icon-item"><i class="pi pi-calendar"></i><span class="icon-name">pi-calendar</span></div>
          <div class="icon-item"><i class="pi pi-clock"></i><span class="icon-name">pi-clock</span></div>
          <div class="icon-item"><i class="pi pi-map-pin"></i><span class="icon-name">pi-map-pin</span></div>
        </div>
      </div>
    </div>

    <div class="card mb-5">
      <div class="card-header">
        <span class="fw-semibold"><i class="pi pi-sun me-1 text-primary"></i> Theme</span>
      </div>
      <div class="card-body">
        <div class="icon-grid">
          <div class="icon-item"><i class="pi pi-sun"></i><span class="icon-name">pi-sun</span></div>
          <div class="icon-item"><i class="pi pi-moon"></i><span class="icon-name">pi-moon</span></div>
        </div>
      </div>
    </div>

    {# - Sizes - #}
    <h6 class="text-muted text-uppercase fw-semibold mb-3 ls-wide">Sizes</h6>
    <div class="card mb-5">
      <div class="card-body">
        <div class="size-row">
          <div class="size-item"><i class="pi pi-star pi-xs text-primary"></i><span class="size-label">pi-xs<br>.75rem</span></div>
          <div class="size-item"><i class="pi pi-star pi-sm text-primary"></i><span class="size-label">pi-sm<br>.875rem</span></div>
          <div class="size-item"><i class="pi pi-star text-primary"></i><span class="size-label">default<br>1em</span></div>
          <div class="size-item"><i class="pi pi-star pi-lg text-primary"></i><span class="size-label">pi-lg<br>1.25rem</span></div>
          <div class="size-item"><i class="pi pi-star pi-xl text-primary"></i><span class="size-label">pi-xl<br>1.5rem</span></div>
          <div class="size-item"><i class="pi pi-star pi-2x text-primary"></i><span class="size-label">pi-2x<br>2rem</span></div>
          <div class="size-item"><i class="pi pi-star pi-3x text-primary"></i><span class="size-label">pi-3x<br>3rem</span></div>
          <div class="size-item"><i class="pi pi-star pi-4x text-primary"></i><span class="size-label">pi-4x<br>4rem</span></div>
        </div>
      </div>
    </div>

    {# - Colors - #}
    <h6 class="text-muted text-uppercase fw-semibold mb-3 ls-wide">Colors via currentColor</h6>
    <div class="card mb-5">
      <div class="card-body">
        <div class="color-row">
          <div class="d-flex align-items-center gap-2">
            <i class="pi pi-check-circle pi-xl text-primary"></i>
            <span class="small text-muted">text-primary</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i class="pi pi-check-circle pi-xl text-success"></i>
            <span class="small text-muted">text-success</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i class="pi pi-x-circle pi-xl text-danger"></i>
            <span class="small text-muted">text-danger</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i class="pi pi-alert-triangle pi-xl text-warning"></i>
            <span class="small text-muted">text-warning</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i class="pi pi-info pi-xl text-info"></i>
            <span class="small text-muted">text-info</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i class="pi pi-user pi-xl text-secondary"></i>
            <span class="small text-muted">text-secondary</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i class="pi pi-sun pi-xl text-warning"></i>
            <span class="small text-muted">text-warning</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i class="pi pi-moon pi-xl text-muted"></i>
            <span class="small text-muted">text-muted</span>
          </div>
        </div>
      </div>
    </div>

    {# - Buttons - #}
    <h6 class="text-muted text-uppercase fw-semibold mb-3 ls-wide">Icons in Buttons</h6>
    <div class="card mb-5">
      <div class="card-body d-flex flex-wrap gap-2">
        <button class="btn btn-primary"><i class="pi pi-download me-1"></i> Download</button>
        <button class="btn btn-success"><i class="pi pi-check me-1"></i> Save</button>
        <button class="btn btn-danger"><i class="pi pi-trash me-1"></i> Delete</button>
        <button class="btn btn-outline-secondary"><i class="pi pi-edit me-1"></i> Edit</button>
        <button class="btn btn-outline-primary"><i class="pi pi-refresh me-1"></i> Refresh</button>
        <button class="btn btn-light"><i class="pi pi-filter me-1"></i> Filter</button>
        <a href="https://github.com/primaybr/phuse" target="_blank" rel="noopener" class="btn btn-dark">
          <i class="pi pi-github me-1"></i> GitHub
        </a>
      </div>
    </div>

    {# - Code examples - #}
    <h6 class="text-muted text-uppercase fw-semibold mb-3 ls-wide">Usage Examples</h6>
    <div class="card mb-5">
      <div class="card-header"><code>HTML</code></div>
      <div class="card-body p-0">
        <div class="code-block">&lt;!-- Basic icon --&gt;
          &lt;i class="pi pi-home"&gt;&lt;/i&gt;

          &lt;!-- With size --&gt;
          &lt;i class="pi pi-star pi-2x"&gt;&lt;/i&gt;

          &lt;!-- With color (inherits from text color) --&gt;
          &lt;i class="pi pi-check text-success"&gt;&lt;/i&gt;
          &lt;span class="text-primary"&gt;&lt;i class="pi pi-heart"&gt;&lt;/i&gt; Like&lt;/span&gt;

          &lt;!-- In a button --&gt;
          &lt;button class="btn btn-primary"&gt;
          &lt;i class="pi pi-download me-1"&gt;&lt;/i&gt; Download
          &lt;/button&gt;

          &lt;!-- In a badge --&gt;
          &lt;span class="badge bg-danger"&gt;
          &lt;i class="pi pi-bell me-1"&gt;&lt;/i&gt; 3
          &lt;/span&gt;</div>
      </div>
    </div>

    <div class="card mb-5">
      <div class="card-header"><code>CSS - How it works</code></div>
      <div class="card-body p-0">
        <div class="code-block">/* Base class - defines the mask infrastructure */
          .pi {
          display: inline-block;
          width: 1em;
          height: 1em;
          background-color: currentColor; /* ← the actual rendered color */
          -webkit-mask-repeat: no-repeat;
          mask-repeat: no-repeat;
          -webkit-mask-position: center;
          mask-position: center;
          -webkit-mask-size: contain;
          mask-size: contain;
          vertical-align: -0.125em;
          }

          /* Each icon provides its own SVG shape as a mask */
          .pi-home {
          -webkit-mask-image: url("data:image/svg+xml,...");
          mask-image: url("data:image/svg+xml,...");
          }

          /* Size variants just change font-size */
          .pi-2x { font-size: 2rem; }
          .pi-lg { font-size: 1.25rem; }</div>
      </div>
    </div>

    {# - Footer - #}
    <div class="text-center text-secondary small py-3 border-top">
      Phuse Framework Icon System &copy; {{year}} &nbsp;&middot;&nbsp;
      <a href="/examples" class="text-secondary">← All Examples</a>
    </div>

  </div>

  <script src="{{assetsUrl}}js/scripts.js?v=136"></script>
</body>

</html>