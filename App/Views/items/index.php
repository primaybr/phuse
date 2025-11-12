<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Item List</title>
    <link rel="stylesheet" href="{assetsUrl}css/styles.css">
</head>
<body class="container py-5">
    <h1 class="mb-4">Item List</h1>
    <ul class="list-group">
        {% foreach items as item %}
            <li class="list-group-item">{item.name}</li>
        {% endforeach %}
    </ul>
</body>
</html>
