<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Performance Test Example - Phuse Template System</title>
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
      max-width: 900px;
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
    .performance-card {
      background: rgb(40, 40, 40);
      border-radius: 12px;
      padding: 2rem;
      margin: 1rem 0;
      border-left: 4px solid #dc3545;
    }
    .iteration-display {
      background: rgba(13, 110, 253, 0.1);
      border: 1px solid rgba(13, 110, 253, 0.3);
      border-radius: 8px;
      padding: 1.5rem;
      margin: 1rem 0;
    }
    .iteration-number {
      color: #0d6efd;
      font-size: 1.3rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    .timestamp {
      color: #6c757d;
      font-size: 0.9rem;
      font-style: italic;
      margin-bottom: 1rem;
    }
    .performance-note {
      background: rgba(255, 193, 7, 0.1);
      border: 1px solid rgba(255, 193, 7, 0.3);
      border-radius: 8px;
      padding: 1.5rem;
      margin-top: 1.5rem;
    }
    .highlight {
      background: rgba(13, 110, 253, 0.1);
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-family: 'Courier New', monospace;
      color:rgb(0, 128, 255);
    }
    .speed-indicator {
      background: rgba(25, 135, 84, 0.1);
      border-left: 3px solid #198754;
      padding: 1rem;
      margin: 1rem 0;
      border-radius: 6px;
    }
  </style>
</head>

<body>
  <div class="container py-2">
    <div class="example-container">
      <div class="example-header">
        <div class="text-center mb-2">
          <h1 class="display-5 fw-bold">Performance Test Example</h1>
          <p class="lead mb-0">Phuse Template System</p>
        </div>
      </div>

      <div class="example-content">
        <div class="speed-indicator mb-4">
          <h6 class="mb-3">⚡ Performance Features Demonstrated:</h6>
          <ul class="mb-0" style="color: rgb(205, 205, 205);">
            <li><span class="highlight">{iteration}</span> - Dynamic iteration tracking</li>
            <li><span class="highlight">{timestamp}</span> - Real-time timestamp generation</li>
            <li><span class="highlight">Template Caching</span> - Performance optimization</li>
            <li><span class="highlight">Complex Logic</span> - Heavy template processing</li>
          </ul>
        </div>

        <div class="performance-card">
          <h5 class="mb-3">🚀 Template Performance Test</h5>
          <p class="mb-3">
            This template simulates a heavy template with complex nested logic and multiple data structures. It's designed to demonstrate the performance benefits of the Phuse template caching system.
          </p>

          <div class="iteration-display">
            <div class="iteration-number">Iteration #{iteration}</div>
            <div class="timestamp">Generated at: {timestamp}</div>
            <p style="margin: 0; color: rgb(205, 205, 205);">
              This demonstrates how template caching can significantly improve performance for complex templates in high-traffic applications.
            </p>
          </div>
        </div>

        <div class="performance-note">
          <h6 class="text-warning mb-2">📈 Performance Optimization</h6>
          <p class="mb-0" style="color: rgb(205, 205, 205);">
            Complex templates like this benefit greatly from caching, especially in high-traffic applications. The Phuse template system automatically caches compiled templates to reduce processing overhead.
          </p>
        </div>

        <div class="alert alert-success mt-4">
          <strong>Caching Benefits:</strong> Each template render with caching enabled provides significant performance improvements, especially noticeable with complex templates containing multiple loops and calculations.
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
