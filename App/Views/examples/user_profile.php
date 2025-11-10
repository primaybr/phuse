<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nested Data Example - Phuse Template System</title>
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
      max-width: 1000px;
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
    .user-card {
      background: rgb(40, 40, 40);
      border-radius: 12px;
      padding: 2rem;
      margin: 1.5rem 0;
      border-left: 4px solid #6f42c1;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .user-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }
    .user-name {
      color: #ffffff;
      margin-bottom: 1rem;
      font-size: 1.5rem;
      font-weight: 600;
    }
    .user-info {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    .info-item {
      background: rgba(111, 66, 193, 0.1);
      padding: 1rem;
      border-radius: 8px;
      text-align: center;
    }
    .info-label {
      color: #b197fc;
      font-size: 0.9rem;
      margin-bottom: 0.5rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .info-value {
      color: #ffffff;
      font-size: 1.1rem;
      font-weight: 500;
    }
    .skills-section {
      background: rgba(25, 135, 84, 0.1);
      border: 1px solid rgba(25, 135, 84, 0.3);
      border-radius: 8px;
      padding: 1.5rem;
      margin-top: 1rem;
    }
    .skill-tag {
      display: inline-block;
      background: #198754;
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      margin: 0.25rem 0.5rem 0.25rem 0;
      font-size: 0.9rem;
      font-weight: 500;
    }
    .highlight {
      background: rgba(13, 110, 253, 0.1);
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-family: 'Courier New', monospace;
      color:rgb(0, 128, 255);
    }
    .nested-indicator {
      background: rgba(111, 66, 193, 0.1);
      border-left: 3px solid #6f42c1;
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
          <h1 class="display-5 fw-bold">Nested Data Example</h1>
          <p class="lead mb-0">Phuse Template System</p>
        </div>
      </div>
      <div class="example-content">
        <p class="mb-4">
          This example demonstrates accessing <span class="highlight">nested data structures</span> using dot notation like <span class="highlight">&lbrace;user.profile.age&rbrace;</span> and <span class="highlight">&lbrace;user.skills&rbrace;</span>.
        </p>
        {% foreach users as user %} 
        <div class="user-card">
          <div class="user-name">{user.name}</div> 
          <div class="user-info">
            <div class="info-item">
              <div class="info-label">Age</div>
              <div class="info-value">{user.profile.age} years</div>
            </div>
            <div class="info-item">
              <div class="info-label">Location</div>
              <div class="info-value">{user.profile.city}</div>
            </div>
            <div class="info-item">
              <div class="info-label">Occupation</div>
              <div class="info-value">{user.profile.occupation}</div>
            </div>
          </div>
          <div class="skills-section">
            <h6 class="text-success mb-3">🛠️ Skills & Technologies</h6>
            {% foreach user.skills as skill %}
              <span class="skill-tag">{skill}</span>
            {% endforeach %}
          </div>
        </div>
        {% endforeach %}
        <div class="nested-indicator">
          <h6 class="mb-2">🔍 Nested Data Access Syntax:</h6>
          <ul class="mb-0" style="color: rgb(205, 205, 205);">
            <li><span class="highlight">&lbrace;user.profile.age&rbrace;</span> - Access nested object property</li>
            <li><span class="highlight">&lbrace;user.skills&rbrace;</span> - Access array property</li>
            <li><span class="highlight">&lbrace;users&rbrace;</span> - Loop through array of objects</li>
          </ul>
        </div>

        <div class="alert alert-info mt-4">
          <strong>Template Features:</strong> The template system supports deep nested data access using dot notation, making it easy to work with complex data structures.
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
