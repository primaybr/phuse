<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Foreach Loop Example - Phuse Template System</title>
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
    .product-card {
      background: rgb(40, 40, 40);
      border-radius: 8px;
      padding: 1.5rem;
      margin: 1rem 0;
      border-left: 4px solid #0d6efd;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .product-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    .product-name {
      color: #ffffff;
      margin-bottom: 0.5rem;
      font-size: 1.2rem;
    }
    .product-price {
      color: #198754;
      font-weight: bold;
      font-size: 1.1rem;
      margin-bottom: 0.5rem;
    }
    .product-category {
      color: #6c757d;
      font-style: italic;
      font-size: 0.9rem;
    }
    .highlight {
      background: rgba(0, 102, 255, 0.1);
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-family: 'Courier New', monospace;
      color:rgb(0, 128, 255);
    }
    .stats-card {
      background: rgba(25, 135, 84, 0.1);
      border: 1px solid rgba(25, 135, 84, 0.3);
      border-radius: 8px;
      padding: 1rem;
      margin-top: 1.5rem;
      text-align: center;
    }
  </style>
</head>

<body>
  <div class="container py-2">
    <div class="example-container">
      <div class="example-header">
        <div class="text-center mb-2">
          <h1 class="display-5 fw-bold">Foreach Loop Example</h1>
          <p class="lead mb-0">Phuse Template System</p>
        </div>
      </div>

      <div class="example-content">
        <p class="mb-3">
          This example demonstrates <span class="highlight">{&percnt; foreach &percnt;}</span> loops for iterating over arrays and displaying collections.
        </p>

        <div class="row">
          <div class="col-md-8">
            <h5 class="mb-3">Products in <span class="badge bg-primary">{category_filter}</span> Category</h5>

            {% foreach products as product %}
            <div class="product-card">
              <h6 class="product-name">{product.name}</h6>
              <div class="product-price">${product.price}</div>
              <div class="product-category">Category: {product.category}</div>
            </div>
            {% endforeach %}
          </div>

          <div class="col-md-4">
            <div class="stats-card">
              <h6 class="text-success mb-2">📊 Statistics</h6>
              <p class="mb-1"><strong>Total Products:</strong></p>
              <span class="badge bg-success fs-6">{products_count}</span>
              <p class="mb-1 mt-3"><strong>Average Price:</strong></p>
              <span class="badge bg-info fs-6">
                ${average_price_rounded}
              </span>
            </div>

            <div class="alert alert-info mt-3">
              <strong>Template Syntax:</strong><br>
              <span class="highlight">{&percnt; foreach products as product &percnt;}</span><br>
              Iterates through the products array and creates a card for each item.
            </div>
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
