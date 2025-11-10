<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Simple Template Example - Phuse Template System</title>
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
    .simple-card {
      background: rgb(40, 40, 40);
      border-radius: 12px;
      padding: 3rem 2rem;
      text-align: center;
      border-left: 4px solid #198754;
      margin: 1rem 0;
    }
    .simple-title {
      color: #ffffff;
      font-size: 2.5rem;
      margin-bottom: 1.5rem;
      font-weight: 300;
    }
    .simple-message {
      color: #198754;
      font-size: 1.3rem;
      font-weight: 500;
      margin: 0;
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
          <h1 class="display-5 fw-bold">Simple Template Example</h1>
          <p class="lead mb-0">Phuse Template System</p>
        </div>
      </div>

      <div class="example-content">
        <div class="simple-card">
          <h2 class="simple-title">Simple Template</h2>
          <p class="simple-message">{message}</p>
        </div>

        <div class="alert alert-success mt-4">
          <strong>Basic Template:</strong> This simple template demonstrates the most basic functionality of the Phuse template system using the <span class="highlight">{message}</span> variable.
        </div>
      </div>

      <div class="example-footer">
        <p class="mb-0">Phuse Framework Template System &copy; {year}</p>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
