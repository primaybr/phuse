<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inline CSS &amp; JS Safety - Phuse Template System</title>
  <link rel="stylesheet" href="{{assetsUrl}}css/styles.css">

  {{# Inline <style> blocks are fully protected — CSS curly braces are never parsed #}}
  <style>
    /* CSS rules use { } freely — they are protected by the parser */
    .demo-box {
      border: 2px solid var(--ps-primary, #0d6efd);
      border-radius: 0.5rem;
      padding: 1.5rem;
      margin-bottom: 1rem;
      background-color: rgba(13, 110, 253, 0.05);
    }
    .demo-box h5 {
      color: var(--ps-primary, #0d6efd);
      font-weight: 600;
    }
    .code-block {
      background: #1e1e1e;
      color: #d4d4d4;
      border-radius: 0.4rem;
      padding: 1rem;
      font-family: monospace;
      font-size: 0.875rem;
      white-space: pre;
      overflow-x: auto;
    }
    .tag-new  { background: #198754; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 0.75rem; }
    .result-ok  { color: #198754; font-weight: 600; }
    .result-bad { color: #dc3545; font-weight: 600; }
  </style>
</head>

<body>
  <div class="container py-2">
    <div class="card shadow mx-auto max-width-lg">

      <div class="card-header bg-secondary text-white p-4">
        <div class="text-center mb-2">
          <h1 class="display-5 fw-bold">
            Inline CSS &amp; JavaScript Safety
            <span class="tag-new ms-2">v1.2.1</span>
          </h1>
          <p class="lead mb-0">
            Single <code style="background:rgba(255,255,255,0.2);padding:1px 4px;border-radius:3px;">{ }</code>
            are no longer parsed — CSS rules and JS objects are completely safe.
          </p>
        </div>
      </div>

      <div class="card-body p-4">

        {{# ------------------------------------------------------------------ #}}
        {{# Section 1 — the problem with single-brace parsers                  #}}
        {{# ------------------------------------------------------------------ #}}
        <div class="demo-box">
          <h5>⚠️ The Problem (Old Single-Brace Parsers)</h5>
          <p class="text-secondary mb-3">
            In old template parsers that use <code>{variable}</code> (single braces),
            CSS and JavaScript code containing curly braces would be corrupted or
            cause parser errors.
          </p>
          <div class="code-block">/* ❌ OLD PARSER — would corrupt this CSS */
.button {
  background-color: #007bff;   /* parser sees {  */
  color: white;                 /* and tries to parse it */
  border-radius: 0.25rem;
}

// ❌ OLD PARSER — would also corrupt this JS
var config = { debug: true, version: "1.0" };
if (condition) { doSomething(); }</div>
        </div>

        {{# ------------------------------------------------------------------ #}}
        {{# Section 2 — the solution with double braces                         #}}
        {{# ------------------------------------------------------------------ #}}
        <div class="demo-box">
          <h5>✅ The Solution (PHUSE v1.2.1 Double-Brace Syntax)</h5>
          <p class="text-secondary mb-3">
            PHUSE now uses <code>{{variable}}</code> (double braces) just like
            <strong>Twig</strong> and <strong>Laravel Blade</strong>.
            Only <code>{{ }}</code> triggers variable substitution — single
            <code>{ }</code> pass through completely unchanged.
          </p>
          <div class="code-block">/* ✅ PHUSE v1.2.1 — CSS is 100% safe */
.button {
  background-color: #007bff;
  color: white;
  border-radius: 0.25rem;
}

/* ✅ Dynamic values still work with double braces */
.hero {
  background-image: url('{{heroImageUrl}}');
  color: {{primaryColor}};
}

// ✅ JavaScript is also 100% safe
var config = { debug: true, version: "1.0" };
if (condition) { doSomething(); }

// ✅ Inject PHP values into JS with double braces
var apiUrl    = "{{apiUrl}}";
var userId    = {{userId}};
var userName  = "{{userName}}";</div>
        </div>

        {{# ------------------------------------------------------------------ #}}
        {{# Section 3 — live demo with actual injected values                   #}}
        {{# ------------------------------------------------------------------ #}}
        <div class="demo-box">
          <h5>🎯 Live Demo — Injected Values</h5>
          <p class="text-secondary mb-3">
            The values below are injected by the PHP controller via
            <code>{{variable}}</code>. The surrounding CSS and JS are untouched.
          </p>

          <table class="table table-sm mb-3">
            <thead><tr><th>Variable</th><th>Value</th><th>Status</th></tr></thead>
            <tbody>
              <tr>
                <td><code>{{siteName}}</code></td>
                <td><strong>{{siteName}}</strong></td>
                <td class="result-ok">✅ Replaced</td>
              </tr>
              <tr>
                <td><code>{{version}}</code></td>
                <td><strong>{{version}}</strong></td>
                <td class="result-ok">✅ Replaced</td>
              </tr>
              <tr>
                <td><code>{{primaryColor}}</code></td>
                <td>
                  <span style="display:inline-block;width:16px;height:16px;background:{{primaryColor}};border-radius:3px;vertical-align:middle;"></span>
                  <strong>{{primaryColor}}</strong>
                </td>
                <td class="result-ok">✅ Used in inline style</td>
              </tr>
              <tr>
                <td><code>{{userName}}</code></td>
                <td><strong>{{userName}}</strong></td>
                <td class="result-ok">✅ Replaced</td>
              </tr>
            </tbody>
          </table>

          {{# Inline style attribute using a template variable — safe! #}}
          <div style="border-left: 4px solid {{primaryColor}}; padding: 0.75rem 1rem; background: rgba(0,0,0,0.03); border-radius: 0 0.4rem 0.4rem 0;">
            This div has a dynamic <code>border-left</code> color set via
            <code>style="border-left: 4px solid {{primaryColor}};"</code>
          </div>
        </div>

        {{# ------------------------------------------------------------------ #}}
        {{# Section 4 — inline script with dynamic values                       #}}
        {{# ------------------------------------------------------------------ #}}
        <div class="demo-box">
          <h5>📜 Inline &lt;script&gt; with Dynamic Values</h5>
          <p class="text-secondary mb-3">
            Variables inside <code>&lt;script&gt;</code> tags work with
            <code>{{variable}}</code>. The parser resolves them on restore
            after the script block has been protected.
          </p>
          <div class="code-block">&lt;script&gt;
  // ✅ These {{variables}} are resolved by PHUSE
  var app = {
    name:    "{{siteName}}",
    version: "{{version}}",
    user:    "{{userName}}"
  };

  // ✅ Plain JS objects — single { } are untouched
  var config = { debug: false, timeout: 5000 };

  console.log("App:", app.name, "v" + app.version);
&lt;/script&gt;</div>
        </div>

        {{# ------------------------------------------------------------------ #}}
        {{# Section 5 — new v1.2.1 syntax features                              #}}
        {{# ------------------------------------------------------------------ #}}
        <div class="demo-box">
          <h5>✨ New v1.2.1 Syntax Features</h5>
          <div class="row g-3">

            <div class="col-md-6">
              <div class="card p-3 h-100">
                <h6 class="text-primary">Template Comments</h6>
                <p class="text-secondary small mb-2">Stripped from output — never visible in HTML source.</p>
                <div class="code-block">{# This comment is invisible #}
{# TODO: update this section #}</div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="card p-3 h-100">
                <h6 class="text-primary">Raw HTML Output</h6>
                <p class="text-secondary small mb-2">Use <code>{!! var !!}</code> for trusted HTML content.</p>
                <div class="code-block">{!! richTextContent !!}
{!! user.bio !!}</div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="card p-3 h-100">
                <h6 class="text-primary">Escaped Output Tag</h6>
                <p class="text-secondary small mb-2">Output a literal <code>{{variable}}</code> in the HTML.</p>
                <div class="code-block">@{{variable}}
→ renders as: {{variable}}</div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="card p-3 h-100">
                <h6 class="text-primary">Filters &amp; Chaining</h6>
                <p class="text-secondary small mb-2">Pipe filters just like Twig.</p>
                <div class="code-block">{{name|upper}}
{{name|substr:0:1|upper}}
{{date|date:'M d, Y'}}
{{rating|stars}}</div>
              </div>
            </div>

          </div>
        </div>

        <div class="alert alert-success mt-4">
          <strong>Summary:</strong> By switching from <code>{var}</code> to <code>{{var}}</code>,
          PHUSE v1.2.1 eliminates all CSS/JS conflicts while staying familiar to
          <strong>Twig</strong> and <strong>Laravel Blade</strong> developers.
        </div>

      </div>

      <div class="card-footer text-center text-secondary py-3">
        <p class="mb-0">Phuse Framework Template System &copy; {{year}}</p>
      </div>
    </div>
  </div>

  {{# Inline script — double-brace vars resolved, single-brace JS untouched #}}
  <script>
    // Variables injected from PHP via {{variable}} syntax
    var app = {
      name:    "{{siteName}}",
      version: "{{version}}",
      user:    "{{userName}}"
    };

    // Plain JavaScript — curly braces are completely safe
    var config = { debug: false, timeout: 5000 };

    function greetUser(name) {
      if (name) {
        console.log("Hello, " + name + "!");
      } else {
        console.log("Hello, stranger!");
      }
    }

    greetUser(app.user);
    console.log("Running " + app.name + " v" + app.version);
  </script>

</body>
</html>
