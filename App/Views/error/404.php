<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Error 404 Not Found!</title>
  <style>
    body {
      background-color: #121212;
      margin: 0;
      padding: 0;
      font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      line-height: 1.5;
    }
    .error-container {
      background-color: #1e1e1e;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      padding: 2rem;
      max-width: 500px;
      width: 90%;
      text-align: center;
    }
    .error-template h1 {
      font-size: 4rem;
      font-weight: 300;
      margin: 0 0 1rem 0;
      color: #ffffff;
    }
    .error-template h2 {
      font-size: 1.5rem;
      font-weight: 400;
      margin: 0 0 1.5rem 0;
      color: #b0b0b0;
    }
    .error-details {
      margin-bottom: 2rem;
      color: #b0b0b0;
    }
    .btn {
      background-color: transparent;
      border: none;
      color: #ffffff;
      padding: 0.75rem 1.5rem;
      border-radius: 4px;
      font-size: 0.875rem;
      font-weight: 500;
      text-decoration: none;
      display: inline-block;
      transition: background-color 0.2s ease;
      cursor: pointer;
    }
    .btn:hover {
      background-color: rgba(255, 255, 255, 0.08);
    }
  </style>
</head>

<body>
  <div class="error-container">
    <div class="error-template">
      <h1>404</h1>
      <h2>Not Found</h2>
      <div class="error-details">
        Sorry, the page you are looking for does not exist.
      </div>
      <a href="#" onclick="history.back()" class="btn">Go Back</a>
    </div>
  </div>
</body>

</html>
