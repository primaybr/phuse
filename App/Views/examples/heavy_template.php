<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Heavy Template Example - Phuse Template System</title>
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
      max-width: 1100px;
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
    .feature-section {
      background: rgb(40, 40, 40);
      border-radius: 12px;
      padding: 2rem;
      margin: 1.5rem 0;
      border-left: 4px solid #6f42c1;
    }
    .performance-section {
      background: rgb(40, 40, 40);
      border-radius: 12px;
      padding: 2rem;
      margin: 1.5rem 0;
      border-left: 4px solid #dc3545;
    }
    .benefits-section {
      background: rgba(255, 193, 7, 0.1);
      border: 1px solid rgba(255, 193, 7, 0.3);
      border-radius: 12px;
      padding: 2rem;
      margin: 1.5rem 0;
    }
    .data-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin: 1.5rem 0;
    }
    .data-item {
      background: rgb(30, 30, 30);
      border-radius: 8px;
      padding: 1.5rem;
      text-align: center;
      border-left: 4px solid #198754;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .data-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    .item-title {
      color: #ffffff;
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 1rem;
    }
    .item-description {
      color: rgb(205, 205, 205);
      font-size: 0.9rem;
      line-height: 1.5;
      margin-bottom: 0.5rem;
    }
    .performance-indicator {
      background: rgba(220, 53, 69, 0.1);
      border: 1px solid rgba(220, 53, 69, 0.3);
      border-radius: 8px;
      padding: 1rem;
      margin: 1rem 0;
    }
    .highlight {
      background: rgba(13, 110, 253, 0.1);
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-family: 'Courier New', monospace;
      color:rgb(0, 128, 255);
    }
    .features-list {
      background: rgba(111, 66, 193, 0.1);
      border-left: 3px solid #6f42c1;
      padding: 1.5rem;
      margin: 1.5rem 0;
      border-radius: 6px;
    }
  </style>
</head>

<body>
  <div class="container py-2">
    <div class="example-container">
      <div class="example-header">
        <div class="text-center mb-2">
          <h1 class="display-5 fw-bold">{title}</h1>
          <p class="lead mb-0">Phuse Template System</p>
        </div>
      </div>

      <div class="example-content">
        <div class="features-list mb-4">
          <h6 class="mb-3">🎯 Complex Features Demonstrated:</h6>
          <ul class="mb-0" style="color: rgb(205, 205, 205);">
            <li><span class="highlight">{title}</span> - Dynamic title rendering</li>
            <li><span class="highlight">{content}</span> - Complex content display</li>
            <li><span class="highlight">Multiple Sections</span> - Organized content structure</li>
            <li><span class="highlight">Grid Layout</span> - Responsive data presentation</li>
            <li><span class="highlight">Performance Optimization</span> - Heavy template processing</li>
          </ul>
        </div>

        <p style="color: rgb(205, 205, 205); font-size: 1.1rem; margin-bottom: 2rem;">
          {content}
        </p>

        <div class="feature-section">
          <h5 class="mb-3">🔧 Template Features Demonstrated</h5>
          <div class="row">
            <div class="col-md-6">
              <ul style="color: rgb(205, 205, 205);">
                <li>Variable replacement with complex data structures</li>
                <li>Nested object property access</li>
                <li>Array iteration and filtering</li>
              </ul>
            </div>
            <div class="col-md-6">
              <ul style="color: rgb(205, 205, 205);">
                <li>Conditional logic and formatting</li>
                <li>Performance optimization through caching</li>
                <li>Responsive grid layouts</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="performance-section">
          <h5 class="mb-3">⚡ Why Caching Matters</h5>
          <div class="data-grid">
            <div class="data-item">
              <div class="item-title">Without Caching</div>
              <div class="item-description">Template parsed on every request</div>
              <div class="item-description">High CPU usage</div>
              <div class="item-description">Slower response times</div>
            </div>
            <div class="data-item">
              <div class="item-title">With Caching</div>
              <div class="item-description">Template parsed once, cached</div>
              <div class="item-description">Low CPU usage</div>
              <div class="item-description">Fast response times</div>
            </div>
          </div>
        </div>

        <div class="benefits-section">
          <h5 class="text-warning mb-3">🚀 Performance Benefits</h5>
          <p style="color: rgb(205, 205, 205); margin: 0;">
            This template demonstrates how the Phuse template system optimizes performance through intelligent caching strategies. Complex templates with multiple sections, grids, and dynamic content benefit significantly from template caching.
          </p>
        </div>

        <div class="performance-indicator">
          <div class="alert alert-danger">
            <strong>Performance Impact:</strong> This template contains multiple complex sections and styling rules, making it an ideal candidate for demonstrating caching performance improvements.
          </div>
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
