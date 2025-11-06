<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Product Page Example - Phuse Template System</title>
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
      max-width: 1200px;
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
    .product-layout {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 2rem;
      margin-bottom: 3rem;
    }
    .product-image {
      background: rgb(40, 40, 40);
      border-radius: 12px;
      padding: 2rem;
      text-align: center;
      border-left: 4px solid #198754;
    }
    .product-image img {
      max-width: 100%;
      height: auto;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    }
    .product-details {
      background: rgb(40, 40, 40);
      border-radius: 12px;
      padding: 2.5rem;
      border-left: 4px solid #fd7e14;
    }
    .product-title {
      color: #ffffff;
      margin-bottom: 1rem;
      font-size: 2.2rem;
      line-height: 1.3;
    }
    .product-price {
      background: rgba(25, 135, 84, 0.1);
      border: 2px solid rgba(25, 135, 84, 0.3);
      border-radius: 8px;
      padding: 1rem;
      margin: 1.5rem 0;
      text-align: center;
    }
    .price-amount {
      color: #198754;
      font-size: 2.5rem;
      font-weight: 700;
      margin: 0;
    }
    .price-hidden {
      background: rgba(220, 53, 69, 0.1);
      border-color: rgba(220, 53, 69, 0.3);
      color: #dc3545;
    }
    .price-hidden .price-amount {
      color: #dc3545;
    }
    .stock-status {
      border-radius: 8px;
      padding: 1rem;
      margin: 1.5rem 0;
      text-align: center;
      font-weight: 600;
    }
    .in-stock {
      background: rgba(25, 135, 84, 0.1);
      border: 1px solid rgba(25, 135, 84, 0.3);
      color: #198754;
    }
    .out-of-stock {
      background: rgba(220, 53, 69, 0.1);
      border: 1px solid rgba(220, 53, 69, 0.3);
      color: #dc3545;
    }
    .product-info {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin: 1.5rem 0;
    }
    .info-card {
      background: rgba(13, 110, 253, 0.1);
      border: 1px solid rgba(13, 110, 253, 0.3);
      border-radius: 8px;
      padding: 1rem;
      text-align: center;
    }
    .info-label {
      color: #0d6efd;
      font-size: 0.9rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 0.5rem;
    }
    .info-value {
      color: #ffffff;
      font-size: 1.1rem;
      font-weight: 500;
    }
    .rating-display {
      background: rgba(255, 193, 7, 0.1);
      border: 1px solid rgba(255, 193, 7, 0.3);
      border-radius: 8px;
      padding: 1rem;
      text-align: center;
      margin: 1rem 0;
    }
    .rating-stars {
      color: #ffc107;
      font-size: 1.2rem;
      margin-bottom: 0.5rem;
    }
    .add-to-cart-btn {
      background: #198754;
      border: none;
      border-radius: 8px;
      padding: 1rem 2rem;
      font-size: 1.1rem;
      font-weight: 600;
      width: 100%;
      transition: all 0.2s;
    }
    .add-to-cart-btn:hover {
      background: #157347;
      transform: translateY(-1px);
    }
    .related-section {
      background: rgba(111, 66, 193, 0.1);
      border: 1px solid rgba(111, 66, 193, 0.3);
      border-radius: 8px;
      padding: 2rem;
      margin-top: 2rem;
    }
    .related-product {
      background: rgb(30, 30, 30);
      border-radius: 8px;
      padding: 1.5rem;
      margin: 1rem 0;
      border-left: 4px solid #6f42c1;
      transition: transform 0.2s;
    }
    .related-product:hover {
      transform: translateY(-2px);
    }
    .related-name {
      color: #ffffff;
      margin-bottom: 0.5rem;
    }
    .related-price {
      color: #198754;
      font-weight: 600;
    }
    .highlight {
      background: rgba(13, 110, 253, 0.1);
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-family: 'Courier New', monospace;
      color:rgb(0, 128, 255);
    }
    .conditional-demo {
      background: rgba(0, 123, 255, 0.1);
      border-left: 3px solid #007bff;
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
          <h1 class="display-5 fw-bold">Product Page Example</h1>
          <p class="lead mb-0">Phuse Template System</p>
        </div>
      </div>

      <div class="example-content">
        <div class="conditional-demo mb-4">
          <h6 class="mb-3">🎯 Conditional Features Demonstrated:</h6>
          <ul class="mb-0" style="color: rgb(205, 205, 205);">
            <li><span class="highlight">{% if user_preferences.show_prices %}</span> - Conditional price display</li>
            <li><span class="highlight">{product.in_stock ? 'in-stock' : 'out-of-stock'}</span> - Dynamic class assignment</li>
            <li><span class="highlight">{% if product.in_stock %}</span> - Conditional button display</li>
            <li><span class="highlight">{product.image}</span> - Image with fallback handling</li>
          </ul>
        </div>

        <div class="product-layout">
          <div class="product-image">
            <img src="{product.image}" alt="{product.name}" onerror="this.src='https://via.placeholder.com/400x300/6f42c1/ffffff?text=No+Image'">
          </div>

          <div class="product-details">
            <h1 class="product-title">{product.name}</h1>

            <div class="product-price {% if not user_preferences.show_prices %}price-hidden{% endif %}">
              <div class="price-amount">
                {% if user_preferences.show_prices %}
                  ${product.price}
                {% endif %}

                {% if not user_preferences.show_prices %}
                  Price Hidden
                {% endif %}
              </div>
            </div>

            <p style="color: rgb(205, 205, 205); line-height: 1.6;">{product.description}</p>

            <div class="stock-status {% if product.in_stock %}in-stock{% else %}out-of-stock{% endif %}">
              {% if product.in_stock %}
                ✅ In Stock ({product.stock_quantity} available)
              {% endif %}

              {% if not product.in_stock %}
                ❌ Out of Stock
              {% endif %}
            </div>

            <div class="product-info">
              <div class="info-card">
                <div class="info-label">Brand</div>
                <div class="info-value">{product.brand}</div>
              </div>
              <div class="info-card">
                <div class="info-label">Category</div>
                <div class="info-value">{product.category}</div>
              </div>
            </div>

            <div class="rating-display">
              <div class="rating-stars">{'★' * (product.rating|round)}{'☆' * (5 - product.rating|round)}</div>
              <div style="color: #ffc107; font-weight: 600;">{product.rating}/5</div>
              <div style="color: #6c757d; font-size: 0.9rem;">({product.reviews} reviews)</div>
            </div>

            {% if product.in_stock %}
              <button class="add-to-cart-btn">🛒 Add to Cart</button>
            {% endif %}
          </div>
        </div>

        <div class="related-section">
          <h6 class="text-primary mb-3">🔗 Related Products</h6>
          {% foreach related_products as related %}
          <div class="related-product">
            <h6 class="related-name">{related.name}</h6>
            <div class="related-price">${related.price}</div>
          </div>
          {% endforeach %}
        </div>

        <div class="alert alert-info mt-4">
          <strong>E-commerce Template:</strong> This template demonstrates a complete product page with conditional pricing, stock management, ratings, and related product suggestions.
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
