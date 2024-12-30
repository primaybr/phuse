<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Item List</title>
</head>
<body>
    <h1>Item List</h1>
    <ul>
        {% foreach items as item %}
            <li>{item.name}</li>
        {% endforeach %}
    </ul>
</body>
</html>