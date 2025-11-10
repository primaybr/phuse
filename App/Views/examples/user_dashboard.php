<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Conditional Logic Example - Phuse Template System</title>
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
    .dashboard-card {
      background: rgb(40, 40, 40);
      border-radius: 8px;
      padding: 1.5rem;
      margin: 1rem 0;
      border-left: 4px solid #198754;
    }
    .guest-card {
      background: rgb(40, 40, 40);
      border-radius: 8px;
      padding: 1.5rem;
      margin: 1rem 0;
      border-left: 4px solid #fd7e14;
    }
    .nav-links {
      background: rgba(25, 135, 84, 0.1);
      padding: 1rem;
      border-radius: 6px;
      margin-top: 1rem;
    }
    .nav-links a {
      color: #75d3a3;
      text-decoration: none;
      margin: 0 0.5rem;
    }
    .nav-links a:hover {
      color: #198754;
      text-decoration: underline;
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
          <h1 class="display-5 fw-bold">Conditional Logic Example</h1>
          <p class="lead mb-0">Phuse Template System</p>
        </div>
      </div>

      <div class="example-content">
        <p class="mb-3">
          This example demonstrates conditional statements in templates using <span class="highlight">&lbrace;% if %&rbrace;</span> syntax.
        </p>

        {% if logged_in %}  
        <div class="dashboard-card">
          <h5 class="mb-3 text-success">✅ Authenticated User Dashboard</h5>
          <p class="mb-2"><strong>Username:</strong> <span class="highlight">{username}</span></p>
          <p class="mb-2"><strong>Role:</strong> <span class="highlight">{role}</span></p>
          <p class="mb-3"><strong>Notifications:</strong> <span class="highlight">{notifications}</span></p>

          <div class="nav-links">
            <strong>Quick Actions:</strong>
            <a href="/profile">Profile</a> |
            <a href="/settings">Settings</a> |
            <a href="/logout">Logout</a>
          </div>
        </div>
        {% endif %}

        {% if not logged_in %}
        <div class="guest-card">
          <h5 class="mb-3 text-warning">👤 Guest Access</h5>
          <p class="mb-2">You are not currently logged in.</p>
          <p class="mb-3">Please <a href="/login" style="color: #fd7e14;">login</a> to access your personalized dashboard.</p>
        </div>
        {% endif %}

        <div class="alert alert-info mt-3">
          <strong>Template Syntax:</strong> The <span class="highlight">{&percnt; if logged_in &percnt;}</span> condition shows different content based on the user's authentication status.
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
