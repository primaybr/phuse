<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Error Template - Phuse Template System</title>
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
      max-width: 800px;
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
    .error-card {
      background: rgb(40, 40, 40);
      border-radius: 12px;
      padding: 3rem 2rem;
      text-align: center;
      border-left: 4px solid #dc3545;
      margin: 1rem 0;
    }
    .error-icon {
      font-size: 4rem;
      color: #dc3545;
      margin-bottom: 1rem;
      opacity: 0.8;
    }
    .error-title {
      color: #ffffff;
      font-size: 2rem;
      margin-bottom: 1rem;
    }
    .error-message {
      color: #dc3545;
      font-size: 1.1rem;
      margin: 1rem 0;
      font-weight: 500;
    }
    .error-details {
      background: rgba(220, 53, 69, 0.1);
      border: 1px solid rgba(220, 53, 69, 0.3);
      border-radius: 8px;
      padding: 1rem;
      margin: 1.5rem 0;
      font-size: 0.9rem;
    }
    .back-link {
      display: inline-block;
      background: rgba(13, 110, 253, 0.1);
      color: #0d6efd;
      text-decoration: none;
      padding: 0.75rem 1.5rem;
      border-radius: 6px;
      border: 1px solid rgba(13, 110, 253, 0.3);
      margin-top: 1rem;
      transition: all 0.2s;
    }
    .back-link:hover {
      background: #0d6efd;
      color: white;
      text-decoration: none;
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
          <h1 class="display-5 fw-bold">Error Template Example</h1>
          <p class="lead mb-0">Phuse Template System</p>
        </div>
      </div>

      <div class="example-content">
        <div class="error-card">
          <div class="error-icon">⚠️</div>
          <h2 class="error-title">Oops! Something is not right.</h2>
          <p class="error-message">{message}</p>

          <div class="error-details">
            <strong>Error Details:</strong><br>
            This error template demonstrates how the Phuse template system handles and displays error messages using the <span class="highlight">{message}</span> variable.
          </div>

          <p style="color: rgb(205, 205, 205); margin: 1.5rem 0;">
            If this problem persists, please contact support or check the application logs for more details.
          </p>

          <a href="/examples" class="back-link">← Back to Examples</a>
        </div>

        <div class="alert alert-info mt-4">
          <strong>Error Handling:</strong> The template system includes robust error handling that gracefully displays error messages while maintaining the application's user interface consistency.
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
