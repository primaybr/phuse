<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Item List</title>
    <link rel="stylesheet" href="{{assetsUrl}}css/styles.css?v=140">
</head>
<body class="container py-5">
    <h1 class="mb-4">Item List</h1>
    <ul class="list-group">
        {% foreach items as item %}
            <li class="list-group-item">{{item.name}}</li>
        {% endforeach %}
    </ul>
  <script src="{{assetsUrl}}js/scripts.js?v=131"></script>
</body>
</html>
