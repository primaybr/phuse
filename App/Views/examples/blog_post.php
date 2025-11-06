<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Blog Post Example - Phuse Template System</title>
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
    .blog-post {
      background: rgb(40, 40, 40);
      border-radius: 12px;
      padding: 2.5rem;
      margin: 1rem 0;
      border-left: 4px solid #fd7e14;
    }
    .post-title {
      color: #ffffff;
      margin-bottom: 1rem;
      font-size: 2.2rem;
      line-height: 1.3;
    }
    .post-meta {
      background: rgba(253, 126, 20, 0.1);
      border: 1px solid rgba(253, 126, 20, 0.3);
      border-radius: 8px;
      padding: 1rem;
      margin: 1.5rem 0;
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
    }
    .meta-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .meta-label {
      color: #fd7e14;
      font-weight: 600;
      font-size: 0.9rem;
    }
    .meta-value {
      color: #ffffff;
      font-size: 1rem;
    }
    .post-content {
      color: rgb(205, 205, 205);
      line-height: 1.8;
      font-size: 1.1rem;
      margin: 1.5rem 0;
    }
    .tags-section {
      background: rgba(13, 110, 253, 0.1);
      border: 1px solid rgba(13, 110, 253, 0.3);
      border-radius: 8px;
      padding: 1.5rem;
      margin: 1.5rem 0;
    }
    .tag {
      display: inline-block;
      background: #0d6efd;
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      margin: 0.25rem 0.5rem 0.25rem 0;
      font-size: 0.9rem;
      font-weight: 500;
      text-decoration: none;
    }
    .tag:hover {
      background: #0b5ed7;
      color: white;
    }
    .comments-section {
      background: rgba(25, 135, 84, 0.1);
      border: 1px solid rgba(25, 135, 84, 0.3);
      border-radius: 8px;
      padding: 1.5rem;
      margin: 1.5rem 0;
    }
    .comment {
      background: rgb(30, 30, 30);
      border-radius: 8px;
      padding: 1.5rem;
      margin: 1rem 0;
      border-left: 4px solid #198754;
    }
    .comment-author {
      color: #75d3a3;
      font-weight: 600;
      font-size: 1.1rem;
      margin-bottom: 0.5rem;
    }
    .comment-text {
      color: rgb(205, 205, 205);
      line-height: 1.6;
      margin: 0;
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
          <h1 class="display-5 fw-bold">Blog Post Example</h1>
          <p class="lead mb-0">Phuse Template System</p>
        </div>
      </div>

      <div class="example-content">
        <div class="features-list mb-4">
          <h6 class="mb-3">🎯 Template Features Demonstrated:</h6>
          <ul class="mb-0" style="color: rgb(205, 205, 205);">
            <li><span class="highlight">{title}</span> - Variable replacement</li>
            <li><span class="highlight">{author}</span> and <span class="highlight">{date}</span> - Multiple variables</li>
            <li><span class="highlight">{% foreach tags as tag %}</span> - Array iteration</li>
            <li><span class="highlight">{comments|length}</span> - Array length filter</li>
            <li><span class="highlight">{comment.author}</span> - Nested object access</li>
          </ul>
        </div>

        <article class="blog-post">
          <h1 class="post-title">{title}</h1>

          <div class="post-meta">
            <div class="meta-item">
              <span class="meta-label">👨‍💻 Author:</span>
              <span class="meta-value">{author}</span>
            </div>
            <div class="meta-item">
              <span class="meta-label">📅 Date:</span>
              <span class="meta-value">{date}</span>
            </div>
            <div class="meta-item">
              <span class="meta-label">💬 Comments:</span>
              <span class="meta-value">{comments|length}</span>
            </div>
          </div>

          <div class="post-content">
            <p>{content}</p>
          </div>

          <div class="tags-section">
            <h6 class="text-primary mb-3">🏷️ Tags</h6>
            {% foreach tags as tag %}
              <a href="#" class="tag">#{tag}</a>
            {% endforeach %}
          </div>

          <div class="comments-section">
            <h6 class="text-success mb-3">💬 Comments ({comments|length})</h6>
            {% foreach comments as comment %}
            <div class="comment">
              <div class="comment-author">{comment.author}</div>
              <div class="comment-text">{comment.text}</div>
            </div>
            {% endforeach %}
          </div>
        </article>

        <div class="alert alert-info mt-4">
          <strong>Multi-Feature Template:</strong> This example combines variable replacement, nested data access, array iteration, and filtering to create a complete blog post layout.
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
