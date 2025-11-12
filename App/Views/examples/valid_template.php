<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Simple Template Example - Phuse Template System</title>
  <link rel="stylesheet" href="{assetsUrl}css/styles.css">
</head>

<body>
  <div class="container py-2">
    <div class="card shadow mx-auto max-width-lg">
      <div class="card-header bg-secondary text-white p-4">
        <div class="text-center mb-2">
          <h1 class="display-5 fw-bold">Simple Template Example</h1>
          <p class="lead mb-0">Phuse Template System</p>
        </div>
      </div>

      <div class="card-body p-4">
        <div class="card p-5 text-center border-left-success mb-4">
          <h2 class="display-4 font-weight-light mb-4">Simple Template</h2>
          <p class="text-success h4 mb-0">{message}</p>
        </div>

        <div class="alert alert-success mt-4">
          <strong>Basic Template:</strong> This simple template demonstrates the most basic functionality of the Phuse template system using the <span class="highlight"><message></span> variable.
        </div>
      </div>

      <div class="card-footer text-center text-secondary py-3">
        <p class="mb-0">Phuse Framework Template System &copy; {year}</p>
      </div>
    </div>
  </div>


</body>
</html>
