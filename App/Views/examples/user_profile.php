<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nested Data Example - Phuse Template System</title>
  <link rel="stylesheet" href="{{assetsUrl}}css/styles.css?v=140">
  <script>(function(){try{var t=localStorage.getItem('phuse-theme');if(t)document.documentElement.setAttribute('data-theme',t);}catch(e){}})()</script>
</head>
<body>
  <div class="container py-2">
    <div class="card shadow mx-auto max-width-lg">
      <div class="card-header bg-secondary text-white p-4">
        <div class="text-center mb-2">
          <h1 class="display-5 fw-bold">Nested Data Example</h1>
          <p class="lead mb-0">Phuse Template System</p>
        </div>
      </div>
      <div class="card-body p-4">
        <p class="mb-4">
          This example demonstrates accessing <strong>nested data structures</strong> using dot notation like <code>{{user.profile.age}}</code> and <code>{{user.skills}}</code>.
        </p>
        {% foreach users as user %}
        <div class="card p-4 mb-4 border-left-info">
          <h4 class="mb-3">{{user.name}}</h4>
          <div class="row mb-3">
            <div class="col-md-4">
              <div class="card p-3 text-center">
                <div class="text-info small fw-bold mb-1">Age</div>
                <div class="h6 mb-0">{{user.profile.age}} years</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card p-3 text-center">
                <div class="text-info small fw-bold mb-1">Location</div>
                <div class="h6 mb-0">{{user.profile.city}}</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card p-3 text-center">
                <div class="text-info small fw-bold mb-1">Occupation</div>
                <div class="h6 mb-0">{{user.profile.occupation}}</div>
              </div>
            </div>
          </div>
          <div class="card p-3">
            <h6 class="text-success mb-3"><i class="pi pi-code me-1"></i> Skills &amp; Technologies</h6>
            {% foreach user.skills as skill %}
              <span class="badge bg-success me-1 mb-1">{{skill}}</span>
            {% endforeach %}
          </div>
        </div>
        {% endforeach %}
        <div class="card p-3 border-left-info">
          <h6 class="mb-2"><i class="pi pi-search me-1"></i> Nested Data Access Syntax:</h6>
          <ul class="mb-0 text-secondary">
            <li><code>{{user.profile.age}}</code> - Access nested object property</li>
            <li><code>{{user.skills}}</code> - Access array property</li>
            <li><code>{{users}}</code> - Loop through array of objects</li>
          </ul>
        </div>

        <div class="alert alert-info mt-4">
          <strong>Template Features:</strong> The template system supports deep nested data access using dot notation, making it easy to work with complex data structures.
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
