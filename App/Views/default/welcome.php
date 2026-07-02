<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Phuse - PHP Easy to Use</title>
  <link rel="stylesheet" href="{{assetsUrl}}css/styles.css?v=141">
  <link rel="icon" type="image/svg+xml" href="{{assetsUrl}}images/favicon.svg">
  <script>(function(){try{var t=localStorage.getItem('phuse-theme');if(t)document.documentElement.setAttribute('data-theme',t);}catch(e){}})()</script>
</head>

<body>
  <div class="container py-5">

    {# ── Hero ────────────────────────────────────────────────────────────── #}
    <div class="card mb-5">
      <div class="card-body text-center py-5 px-4">
        <div class="mb-3">
          <span class="badge bg-primary mb-2">v{{version}}</span>
        </div>
        <div class="mb-2">
          <img src="{{assetsUrl}}images/phuse-logo-light.svg" alt="" class="phuse-logo-light" height="48">
          <img src="{{assetsUrl}}images/phuse-logo-dark.svg" alt="" class="phuse-logo-dark" height="48">
        </div>
        <h1 class="display-4 fw-bold text-primary mb-2">Phuse</h1>
        <p class="lead text-secondary mb-1">PHP Easy to Use</p>
        <p class="mb-4 text-secondary">
          A lightweight PHP framework built around <strong>convention over configuration</strong>.<br>
          Write less boilerplate, ship more features.
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-2">
          <a href="/examples" class="btn btn-primary px-4">
            <i class="pi pi-grid me-1"></i> Browse Examples
          </a>
          <a href="https://github.com/primaybr/phuse" target="_blank" rel="noopener"
             class="btn btn-outline-secondary px-4">
            <i class="pi pi-github me-1"></i> GitHub
          </a>
        </div>
      </div>
    </div>

    {# ── Feature cards ─────────────────────────────────────────────────── #}
    <h6 class="text-muted text-uppercase fw-semibold mb-3 ls-wide">Core Features</h6>
    <div class="row g-3 mb-5">
      {% foreach features as feature %}
      <div class="col-12 col-sm-6 col-lg-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="fs-2 mb-2"><i class="pi {{feature.icon}} pi-2x text-primary"></i></div>
            <h6 class="fw-semibold text-primary mb-1">{{feature.title}}</h6>
            <p class="text-secondary mb-0 small">{!! feature.description !!}</p>
          </div>
        </div>
      </div>
      {% endforeach %}
    </div>

    {# ── Quick-start snippets ──────────────────────────────────────────── #}
    <h6 class="text-muted text-uppercase fw-semibold mb-3 ls-wide">Quick Start</h6>

    <div class="card mb-3">
      <div class="card-header">
        <span class="fw-semibold"><i class="pi pi-code me-1 text-primary"></i> Controller - <code>App/Controllers/Web/Hello.php</code></span>
      </div>
      <div class="card-body p-0">
        <pre class="m-0 p-3 code-block"><code>// App/Controllers/Web/Hello.php
namespace App\Controllers\Web;
use Core\Controller;

class Hello extends Controller {
    public function index(): void {
        $this->render('hello/index', [
            'name'  => 'World',
            'items' => ['PHP', 'MVC', 'Phuse'],
        ]);
    }
}</code></pre>
      </div>
    </div>

    <div class="card mb-5">
      <div class="card-header">
        <span class="fw-semibold"><i class="pi pi-layers me-1 text-primary"></i> Template - <code>App/Views/hello/index.php</code></span>
      </div>
      <div class="card-body p-0">
        <pre class="m-0 p-3 code-block"><code>&lt;h1&gt;Hello, &#123;&#123;name&#125;&#125;!&lt;/h1&gt;

&lt;!-- filters --&gt;
&lt;p&gt;&#123;&#123;name|upper&#125;&#125;&lt;/p&gt;

&lt;!-- loops --&gt;
&#123;% foreach items as item %&#125;
  &lt;span&gt;&#123;&#123;item&#125;&#125;&lt;/span&gt;
&#123;% endforeach %&#125;

&lt;!-- conditionals --&gt;
&#123;% if name %&#125;
  &lt;p&gt;Welcome, &#123;&#123;name&#125;&#125;!&lt;/p&gt;
&#123;% else %&#125;
  &lt;p&gt;Please introduce yourself.&lt;/p&gt;
&#123;% endif %&#125;</code></pre>
      </div>
    </div>

    {# ── Try it out ──────────────────────────────────────────────────────── #}
    <h6 class="text-muted text-uppercase fw-semibold mb-3 ls-wide">Try it Out</h6>
    <div class="row g-2 mb-5">
      {% foreach examples as example %}
      <div class="col-12 col-sm-6 col-md-4">
        <a href="{{example.url}}" class="card card-body text-decoration-none h-100 d-flex flex-row align-items-center gap-3">
          <span class="badge bg-primary" style="min-width:5rem;text-align:center;">{{example.badge}}</span>
          <span class="text-primary fw-semibold small">{{example.label}}</span>
        </a>
      </div>
      {% endforeach %}
    </div>

    {# ── Footer ──────────────────────────────────────────────────────────── #}
    <div class="text-center text-secondary small py-3 border-top">
      Phuse Framework &copy; {{date}} &nbsp;&middot;&nbsp;
      MIT License &nbsp;&middot;&nbsp;
      <a href="mailto:primaybr@gmail.com" class="text-secondary">primaybr@gmail.com</a>
    </div>

  </div>

  <script src="{{assetsUrl}}js/scripts.js?v=136"></script>
</body>
</html>
