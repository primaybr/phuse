<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{title} - Phuse Template System</title>
  <link rel="stylesheet" href="{assetsUrl}css/styles.css">
</head>

<body>
  <div class="container py-2">
  <div class="card shadow mx-auto max-width-lg">
      <div class="card-header bg-secondary text-white p-4">
        <div class="text-center mb-2">
          <h1 class="display-5 fw-bold">{title}</h1>
          <p class="lead mb-0">{description}</p>
        </div>
      </div>

      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h5 class="text-primary mb-1">Interactive Examples</h5>
            <p class="mb-0 text-secondary">
              Explore all features of the Phuse template system through these interactive demonstrations.
            </p>
          </div>
        </div>

        <div class="row g-4 mb-4">
          {% foreach examples as example %}
          <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 border-primary">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title text-primary mb-3">{example.name}</h5>
                <p class="card-text text-secondary flex-grow-1">{example.description}</p>
                <div class="mt-auto">
                  <a href="{example.url}" class="btn btn-primary w-100">
                    View Example
                  </a>
                </div>
              </div>
            </div>
          </div>
          {% endforeach %}
        </div>

        <div class="alert alert-info mt-4">
          <h6 class="alert-heading mb-2">💡 Template System Features:</h6>
          <p class="mb-0">
            These examples showcase all major template features including variable replacement (<span class="highlight">&lbrace;variable&rbrace;</span>),
            conditional logic (<span class="highlight">&lbrace;% if %&rbrace;</span>), loops (<span class="highlight">&lbrace;% foreach %&rbrace;</span>),
            and nested data access.
          </p>
        </div>
      </div>

      <div class="card-footer text-center text-secondary py-3">
        <p class="mb-0">Phuse Framework Template System &copy; {year}</p>
      </div>
    </div>
  </div>


</body>
</html>
