<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Error 500 Internal Server Error!</title>
  <link rel="stylesheet" href="{{assetsUrl}}css/styles.css?v=141">
  <link rel="icon" type="image/svg+xml" href="{{assetsUrl}}images/favicon.svg">
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100">
  <div class="card p-5 shadow text-center mx-auto" style="max-width: 500px; width: 90%;">
    <div class="error-template">
      <img src="{{assetsUrl}}images/phuse-logo-light.svg" alt="Phuse" height="36" class="mb-3">
      <h1 class="display-4 font-weight-light">500</h1>
      <h2 class="h4 text-secondary">Internal Server Error</h2>
      <div class="error-details">
        Sorry, something went wrong on our end.
      </div>
      <a href="#" onclick="history.back()" class="btn btn-outline-primary">Go Back</a>
    </div>
  </div>
</body>
</html>
