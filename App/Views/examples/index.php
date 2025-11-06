<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{title} - Phuse Template System</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: rgb(25, 25, 25);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .example-container {
      max-width: 1200px;
      margin: 2rem auto;
      background: rgb(75, 75, 75);
      border-radius: 8px;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }
    .example-header {
      background: rgb(40, 40, 40);
      color: white;
      padding: 2rem 1.5rem;
      margin-bottom: 1rem;
    }
    .example-content {
      padding: 0 2rem 2rem;
      line-height: 1.7;
      color: rgb(205, 205, 205);
    }
    .example-footer {
      text-align: center;
      padding: 1rem;
      margin-top: auto;
      color: rgb(205, 205, 205);
      font-size: 0.9rem;
      border-top: 1px solid rgb(177, 177, 177);
    }
    .example-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 1.5rem;
      margin-top: 2rem;
    }
    .example-card {
      background: rgb(40, 40, 40);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      padding: 2rem 1.5rem;
      transition: transform 0.2s, box-shadow 0.2s;
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    .example-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
      border-color: rgba(13, 110, 253, 0.3);
    }
    .example-name {
      color: #ffffff;
      font-size: 1.3rem;
      margin-bottom: 1rem;
      font-weight: 600;
    }
    .example-description {
      color: rgb(205, 205, 205);
      margin-bottom: 1.5rem;
      line-height: 1.6;
      flex-grow: 1;
    }
    .example-links {
      display: flex;
      gap: 0.75rem;
      flex-wrap: wrap;
    }
    .example-link {
      display: inline-block;
      background: #0d6efd;
      color: white;
      text-decoration: none;
      padding: 0.75rem 1.5rem;
      border-radius: 6px;
      font-size: 0.9rem;
      font-weight: 500;
      transition: all 0.2s;
      border: 1px solid #0d6efd;
    }
    .example-link:hover {
      background: #0b5ed7;
      color: white;
      text-decoration: none;
      transform: translateY(-1px);
    }
    .code-link {
      background: #198754;
      border-color: #198754;
    }
    .code-link:hover {
      background: #157347;
      border-color: #157347;
    }
    .stats-badge {
      background: rgba(25, 135, 84, 0.1);
      color: #198754;
      border: 1px solid rgba(25, 135, 84, 0.3);
      border-radius: 20px;
      padding: 0.5rem 1rem;
      font-size: 0.9rem;
      font-weight: 600;
    }
    .highlight {
      background: rgba(13, 110, 253, 0.1);
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-family: 'Courier New', monospace;
      color:rgb(0, 128, 255);
    }
  </style>
</head>

<body>
  <div class="container py-2">
    <div class="example-container">
      <div class="example-header">
        <div class="text-center mb-2">
          <h1 class="display-5 fw-bold">{title}</h1>
          <p class="lead mb-0">{description}</p>
        </div>
      </div>

      <div class="example-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h5 class="text-primary mb-1">🚀 Interactive Examples</h5>
            <p class="mb-0" style="color: rgb(205, 205, 205);">
              Explore all features of the Phuse template system through these interactive demonstrations.
            </p>
          </div>
          <div class="stats-badge">
            {examples|length} Examples
          </div>
        </div>

        <div class="example-grid">
          {% foreach examples as example %}
          <div class="example-card">
            <div class="example-name">{example.name}</div>
            <div class="example-description">{example.description}</div>
            <div class="example-links">
              <a href="{example.url}" class="example-link">
                View Example
              </a>
              <a href="/examples/run/{example.template}" class="example-link code-link">
                Run Code
              </a>
            </div>
          </div>
          {% endforeach %}
        </div>

        <div class="alert alert-info mt-4">
          <h6 class="alert-heading mb-2">💡 Template System Features:</h6>
          <p class="mb-0">
            These examples showcase all major template features including variable replacement (<span class="highlight">{variable}</span>),
            conditional logic (<span class="highlight">{% if %}</span>), loops (<span class="highlight">{% foreach %}</span>),
            nested data access, error handling, and performance optimization.
          </p>
        </div>

        <div class="text-center mt-4">
          <p style="color: rgb(205, 205, 205); margin-bottom: 1rem;">
            For comprehensive documentation, visit the
            <a href="/docs/template-system" style="color: #0d6efd;">Template System Documentation</a>
          </p>
        </div>
      </div>

      <div class="example-footer">
        <p class="mb-0">Phuse Framework Template System &copy; 2025</p>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
