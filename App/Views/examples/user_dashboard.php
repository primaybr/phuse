<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Conditional Logic Example - Phuse Template System</title>
  <link rel="stylesheet" href="{{assetsUrl}}css/styles.css?v=139">
  <script>(function(){try{var t=localStorage.getItem('phuse-theme');if(t)document.documentElement.setAttribute('data-theme',t);}catch(e){}})()</script>
</head>

<body>
  <div class="container py-2">
    <div class="card shadow mx-auto max-width-lg">
      <div class="card-header bg-secondary text-white p-4">
        <div class="text-center mb-2">
          <h1 class="display-5 fw-bold">Conditional Logic Example</h1>
          <p class="lead mb-0">Phuse Template System</p>
        </div>
      </div>

      <div class="card-body p-4">
        <p class="mb-3">
          This example demonstrates conditional statements in templates using <code>{% if %}</code> syntax.
        </p>

        {% if logged_in %}
        <div class="card p-4 border-left-success">
          <h5 class="mb-3 text-success"><i class="pi pi-check-circle me-1"></i> Authenticated User Dashboard</h5>
          <p class="mb-2"><strong>Username:</strong> <span class="highlight">{{username}}</span></p>
          <p class="mb-2"><strong>Role:</strong> <span class="highlight">{{role}}</span></p>
          <p class="mb-3"><strong>Notifications:</strong> <span class="highlight">{{notifications}}</span></p>

          <div class="alert alert-success mt-3">
            <strong>Quick Actions:</strong>
            <a href="/profile" class="text-primary me-2">Profile</a> |
            <a href="/settings" class="text-primary me-2">Settings</a> |
            <a href="/logout" class="text-primary">Logout</a>
          </div>
        </div>
        {% endif %}

        {% if not logged_in %}
        <div class="card p-4 border-left-warning">
          <h5 class="mb-3 text-warning"><i class="pi pi-user me-1"></i> Guest Access</h5>
          <p class="mb-2">You are not currently logged in.</p>
          <p class="mb-3">Please <a href="/login" class="text-warning">login</a> to access your personalized dashboard.</p>
        </div>
        {% endif %}

        <div class="alert alert-info mt-3">
          <strong>Template Syntax:</strong> The <code>{% if logged_in %}</code> condition shows different content based on the user's authentication status.
        </div>
      </div>

      <div class="card-footer text-center text-secondary py-3">
        <p class="mb-0">Phuse Framework Template System &copy; {{year}}</p>
      </div>
    </div>
  </div>

  <script src="{{assetsUrl}}js/scripts.js?v=136"></script>
</body>
</html>
