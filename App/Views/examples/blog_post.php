<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Blog Post Example - Phuse Template System</title>
  <link rel="stylesheet" href="{{assetsUrl}}css/styles.css">
</head>

<body>
  <div class="container py-2">
    <div class="card shadow mx-auto max-width-lg">
      <div class="card-header bg-secondary text-white p-4">
        <div class="text-center mb-2">
          <h1 class="display-5 fw-bold">Blog Post Example</h1>
          <p class="lead mb-0">Phuse Template System</p>
        </div>
      </div>

      <div class="card-body p-4">
        <div class="card border-warning p-3 mb-4">
          <h6 class="mb-3">🎯 Template Features Demonstrated:</h6>
          <ul class="mb-0 text-secondary">
            <li><code>{{title}}</code> — Variable replacement</li>
            <li><code>{{author}}</code> and <code>{{date}}</code> — Multiple variables</li>
            <li><code>{% foreach tags as tag %}</code> — Array iteration</li>
            <li><code>{{comments|length}}</code> — Array length filter</li>
            <li><code>{{comment.author}}</code> — Nested object access</li>
          </ul>
        </div>

        <article class="card p-5 mb-4 border-warning">
          <h1 class="h1 text-primary mb-4">{{title}}</h1>

          <div class="card border-info p-3 mb-4">
            <div class="row g-3">
              <div class="col-md-4">
                <span class="text-warning small fw-bold me-2">👨‍💻 Author:</span>
                <span class="text-primary">{{author}}</span>
              </div>
              <div class="col-md-4">
                <span class="text-warning small fw-bold me-2">📅 Date:</span>
                <span class="text-primary">{{date}}</span>
              </div>
              <div class="col-md-4">
                <span class="text-warning small fw-bold me-2">💬 Comments:</span>
                <span class="text-primary">{{comments|length}}</span>
              </div>
            </div>
          </div>

          <div class="mb-4">
            <p class="text-secondary lead">{{content}}</p>
          </div>

          <div class="card border-primary p-3 mb-4">
            <h6 class="text-primary mb-3">🏷️ Tags</h6>
            {% foreach tags as tag %}
              <span class="badge bg-primary me-1 mb-1">#{{tag}}</span>
            {% endforeach %}
          </div>

          <div class="card border-success p-4">
            <h6 class="text-success mb-3">💬 Comments ({{comments|length}})</h6>
            {% foreach comments as comment %}
            <div class="card border-secondary p-3 mb-3">
              <div class="text-info fw-bold mb-2">{{comment.author}}</div>
              <div class="text-secondary">{{comment.text}}</div>
            </div>
            {% endforeach %}
          </div>
        </article>

        <div class="alert alert-info mt-4">
          <strong>Multi-Feature Template:</strong> This example combines variable replacement, nested data access, array iteration, and filtering to create a complete blog post layout.
        </div>
      </div>

      <div class="card-footer text-center text-secondary py-3">
        <p class="mb-0">Phuse Framework Template System &copy; {{year}}</p>
      </div>
    </div>
  </div>


</body>
</html>
