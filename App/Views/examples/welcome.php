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
          <p class="lead mb-0">Phuse Template System Example</p>
        </div>
      </div>

      <div class="card-body p-4">
        <div class="card p-4 mb-4 border-left-primary">
          <h5 class="mb-3">Template Variables Demo</h5>
          <p class="mb-2"><strong>Name:</strong> <span class="highlight">{name}</span></p>
          <p class="mb-2"><strong>Company:</strong> <span class="highlight">{company}</span></p>
          <p class="mb-0"><strong>Title:</strong> <span class="highlight">{title}</span></p>
        </div>

        <p class="mb-2">
          This example demonstrates the basic functionality of the Phuse template system. Variables are replaced with their actual values using the <span class="highlight">&lbrace;variable&rbrace;</span> syntax.
        </p>
        <p class="mb-0">
          The template system provides a clean separation between presentation and logic, making your code more maintainable and reusable.
        </p>
      </div>

      <div class="card-footer text-center text-secondary py-3">
        <p class="mb-0">Phuse Framework Template System &copy; {year}</p>
      </div>
    </div>
  </div>


</body>
</html>
