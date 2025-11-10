<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Welcome to Phuse! PHP Easy to Use</title>
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
    .welcome-container {
      background-color: #1e1e1e;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      padding: 2rem;
      max-width: 600px;
      width: 90%;
      text-align: center;
    }
    .welcome-container h1 {
      font-size: 3rem;
      font-weight: 300;
      margin: 0 0 1rem 0;
      color: #ffffff;
    }
    .welcome-container h2 {
      font-size: 1.25rem;
      font-weight: 400;
      margin: 0 0 1.5rem 0;
      color: #b0b0b0;
    }
    .welcome-content {
      margin-bottom: 2rem;
      color: #b0b0b0;
      text-align: left;
    }
    .welcome-content p {
      margin: 0 0 1rem 0;
    }
    .welcome-content p:last-child {
      margin-bottom: 0;
    }
    .welcome-footer {
      color: #b0b0b0;
      font-size: 0.9rem;
      border-top: 1px solid #333333;
      padding-top: 1rem;
    }
  </style>
</head>

<body>
  <div class="welcome-container">
    <h1>Welcome to Phuse!</h1>
    <h2>PHP Easy to Use</h2>
    <div class="welcome-content">
      <p>
        Phuse is a PHP framework that simplifies web development with conventions and helpers. It follows the convention over configuration principle, which means that it has sensible defaults for most settings and features, reducing the need for manual configuration.
      </p>
      <p>
        It provides a variety of helpers that perform common tasks such as formatting, validation, pagination, and more. Phuse aims to make web development more enjoyable and productive with PHP.
      </p>
    </div>
    <div class="welcome-footer">
      Phuse Framework &copy; {date}
    </div>
  </div>
</body>
</html>
