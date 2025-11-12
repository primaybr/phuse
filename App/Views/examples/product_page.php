<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Product Page Example - Phuse Template System</title>
  <link rel="stylesheet" href="{assetsUrl}css/styles.css">
</head>

<body>
  <div class="container py-2">
  <div class="card shadow mx-auto max-width-lg">
      <div class="card-header bg-secondary text-white p-4">
        <div class="text-center mb-2">
          <h1 class="display-5 fw-bold">Product Page Example</h1>
          <p class="lead mb-0">Phuse Template System</p>
        </div>
      </div>

      <div class="card-body p-4">
        <div class="card border-danger p-3 mb-4">
          <h6 class="mb-3">🎯 Conditional Features Demonstrated:</h6>
          <ul class="mb-0 text-secondary">
            <li><span class="highlight">&lbrace;% if user_preferences.show_prices %&rbrace;</span> - Conditional price display</li>
            <li><span class="highlight">&lbrace;product.in_stock ? 'in-stock' : 'out-of-stock'&rbrace;</span> - Dynamic class assignment</li>
            <li><span class="highlight">&lbrace;% if product.in_stock %&rbrace;</span> - Conditional button display</li>
            <li><span class="highlight">&lbrace;product.image&rbrace;</span> - Image with fallback handling</li>
          </ul>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="card p-4 text-center border-left-success">
              <img src="{product.image}" alt="{product.name}" class="img-fluid rounded shadow">
            </div>
          </div>

          <div class="col-md-6">
            <div class="card p-5 border-left-warning">
              <h1 class="h2 mb-3">{product.name}</h1>

              <div class="alert alert-success text-center mb-3 {% if not user_preferences.show_prices %}alert-danger{% endif %}">
                <div class="h3 mb-0">
                  {% if user_preferences.show_prices %}
                    ${product.price}
                  {% endif %}

                  {% if not user_preferences.show_prices %}
                    Price Hidden
                  {% endif %}
                </div>
              </div>

              <p class="text-secondary mb-4">{product.description}</p>

              <div class="alert {% if product.in_stock %}alert-success{% else %}alert-danger{% endif %} text-center mb-4">
                {% if product.in_stock %}
                  ✅ In Stock ({product.stock_quantity} available)
                {% endif %}

                {% if not product.in_stock %}
                  ❌ Out of Stock
                {% endif %}
              </div>

              <div class="row mb-4">
                <div class="col-6">
                  <div class="card text-center p-3">
                    <div class="text-primary text-uppercase small fw-bold mb-1">Brand</div>
                    <div class="h6 mb-0">{product.brand}</div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="card text-center p-3">
                    <div class="text-primary text-uppercase small fw-bold mb-1">Category</div>
                    <div class="h6 mb-0">{product.category}</div>
                  </div>
                </div>
              </div>

              <div class="card p-3 text-center mb-4">
                <div class="text-warning h5 mb-1">{product.rating|stars}</div>
                <div class="fw-bold">{product.rating}/5</div>
                <div class="text-muted small">({product.reviews} reviews)</div>
              </div>

              {% if product.in_stock %}
                <button class="btn btn-success w-100">🛒 Add to Cart</button>
              {% endif %}
            </div>
          </div>
        </div>

        <div class="card p-4 mt-4">
          <h6 class="text-primary mb-3">🔗 Related Products</h6>
          {% foreach related_products as related %}
          <div class="card p-3 mb-2 border-left-primary">
            <div class="d-flex justify-content-between align-items-center">
              <h6 class="mb-0">{related.name}</h6>
              <span class="text-success fw-bold">${related.price}</span>
            </div>
          </div>
          {% endforeach %}
        </div>

        <div class="alert alert-info mt-4">
          <strong>E-commerce Template:</strong> This template demonstrates a complete product page with conditional pricing, stock management, ratings, and related product suggestions.
        </div>
      </div>

      <div class="card-footer text-center text-secondary py-3">
        <p class="mb-0">Phuse Framework Template System &copy; {year}</p>
      </div>
    </div>
  </div>


</body>
</html>
