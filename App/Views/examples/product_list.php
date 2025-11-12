<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Foreach Loop Example - Phuse Template System</title>
  <link rel="stylesheet" href="{assetsUrl}css/styles.css">
</head>

<body>
  <div class="container py-2">
    <div class="card shadow mx-auto max-width-lg">
      <div class="card-header bg-secondary text-white p-4">
        <div class="text-center mb-2">
          <h1 class="display-5 fw-bold">Foreach Loop Example</h1>
          <p class="lead mb-0">Phuse Template System</p>
        </div>
      </div>

      <div class="card-body p-4">
        <p class="mb-3">
          This example demonstrates <span class="highlight">&lbrace;% foreach products as product %&rbrace;</span> loops for iterating over arrays and displaying collections.
        </p>

        <div class="row">
          <div class="col-md-8">
            <h5 class="mb-3">Products in <span class="badge bg-primary">{category_filter}</span> Category</h5>

            {% foreach products as product %}
            <div class="card p-3 mb-3 border-primary">
              <h6 class="text-primary mb-2">{product.name}</h6>
              <div class="text-success fw-bold h5 mb-1">${product.price}</div>
              <div class="text-muted small">Category: {product.category}</div>
            </div>
            {% endforeach %}
          </div>

          <div class="col-md-4">
            <div class="card p-4 text-center border-success">
              <h6 class="text-success mb-3">📊 Statistics</h6>
              <p class="mb-2"><strong>Total Products:</strong></p>
              <span class="badge bg-success fs-6 mb-3">{products_count}</span>
              <p class="mb-2"><strong>Average Price:</strong></p>
              <span class="badge bg-info fs-6">
                ${average_price_rounded}
              </span>
            </div>

            <div class="alert alert-info mt-3">
              <strong>Template Syntax:</strong><br>
              <span class="highlight">&lbrace;% foreach products as product %&rbrace;</span><br>
              Iterates through the products array and creates a card for each item.
            </div>
          </div>
        </div>
      </div>

      <div class="card-footer text-center text-secondary py-3">
        <p class="mb-0">Phuse Framework Template System &copy; {year}</p>
      </div>
    </div>
  </div>


</body>
</html>
