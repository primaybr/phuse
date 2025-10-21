# Phuse: A User-Friendly and Intuitive PHP Framework

Phuse is a PHP framework that simplifies web development with conventions and helpers. It follows the convention over configuration principle, which means that it has sensible defaults for most settings and features, reducing the need for manual configuration. It also provides a variety of helpers, which are functions that perform common tasks such as formatting, validation, pagination, and more. Phuse aims to make web development more enjoyable and productive with PHP.

## Features

### Core Features

- **MVC Pattern**: Phuse implements the Model-View-Controller pattern, which separates the application logic from the presentation layer. This makes the code more organized, maintainable, and testable.
- **Active Record ORM**: Phuse uses the Active Record pattern, which maps database tables to PHP classes and objects. This makes the database operations easier, faster, and more secure.
- **Routing**: Phuse handles routing efficiently, allowing developers to define clean and understandable URLs for their applications.
- **Formatting**: Phuse provides a formatting class that allows you to format the output data in various ways. It supports date and time, number, currency, and string formatting.

### Performance Optimizations

#### Database Query Caching

- **Automatic Caching**: Query results are automatically cached based on configuration
- **Intelligent Cache Invalidation**: Cache is automatically invalidated when data changes
- **Selective Caching**: Control which queries and tables are cached
- **Development Mode**: Automatic cache clearing in development environment

#### Template Caching

- **Compiled Templates**: Templates are compiled and cached for faster rendering
- **Auto-Refresh**: Cache is automatically cleared when templates are modified
- **Development Friendly**: Easy to disable in development mode
- **Configurable**: Control cache lifetime and storage location
- **And more**: Phuse also offers other features such as logging, sessions, security, email, and more.

## Installation
To install Phuse, follow these steps:
1. Clone the repository:
   ```bash
   git clone https://github.com/primaybr/phuse.git
   ```
2. Navigate to the project directory:
   ```bash
   cd phuse
   ```
3. Set up your web server to point to the `Public` directory.

## Basic Usage
To create a new controller, create a new PHP file in the `App/Controllers/Web` directory. For example, to create a `UserController`, create a file named `User.php` and define your controller class.

### Example:
```php
<?php

namespace App\Controllers\Web;

use Core\Controller;

class User extends Controller
{
    public function index()
    {
        // Your logic here
    }
}
```

## Directory Structure
The Phuse framework follows a standard directory structure:
- **App**: Contains controllers, models, and views.
- **Core**: Contains the core framework classes.
- **Config**: Configuration files for the application.
- **Public**: Publicly accessible files (entry point).
- **Logs**: Log files for debugging and error tracking.
- **Cache**: Caching files to improve performance.

## Controllers
Controllers handle incoming requests and contain the business logic for your application. They extend the base `Controller` class found in the `Core` directory.

## Models
Models interact with the database and handle data-related logic. Ensure to create model classes in the `App/Models` directory to manage your data effectively.

## Views
Views are responsible for rendering the user interface. They are located in the `App/Views` directory and can use dynamic data passed from controllers.

## Routing
Routing is managed by the `Router.php` class in the `Core` directory. Define your routes and link them to the appropriate controllers.

## Configuration
Configuration settings are stored in the `Config` directory. Modify these files to customize your application settings.

## Logging
Phuse provides a logging mechanism to track application errors and events. Log files are stored in the `Logs` directory.

## Minimum Requirement

To install Phuse, you need to have PHP 8.2 or higher.

## License

Phuse is licensed under the MIT license, which means that you can use it for any purpose, even for commercial projects, as long as you give credit to the original author.

## Support

If you have any questions, issues, or feedback regarding Phuse, you can contact the developer through the following channels:

- Email: primaybr@gmail.com

## Contributing

Phuse is an open-source project, and you are welcome to contribute to its development. You can fork the repository, make your changes, and submit a pull request. Please follow the coding standards and guidelines before submitting your code.

## Latest Changes

### v1.0.2a (2025-10-21)
- Removed Versioning information on code files
- Fixed session issue on local machine

### v1.0.2 (2025-09-13)
- Added Database Query Caching system
- Added Template Caching for improved performance
- Enhanced cache configuration options
- Improved documentation for caching features
- Optimized template rendering performance
- Core/Router - Added handle for empty url
- Core/Template/ParserTrait : Update the parseForEach method to not replace string that is outside the brackets scope
- Core/Router : Handling local machine routing

### v1.0.1 (2025-2-28)
- Added support for multiple HTTP methods in Router class
- Added Route Caching in Router class
- Added DocBlock to Core files
- Fixed namespace resolution issues in Router class
- Improved session system for better security and performance
- Improved Router class for better performance and flexibility
- Improved Base class for better performance and flexibility

### v1.0.0 (2023-11-21)
- Initial release of Phuse 1, based on collective and collaborative framework named 'Orceztra'(discontinued personal project).

## Credits

Phuse is developed and maintained by Prima Yoga, a passionate web developer and PHP enthusiast.

## Example Usage

### Overview
In this example, we will create a simple web application that allows users to view a list of items. We will set up a controller to handle requests, a model to interact with the data, and a view to display the information.

### Step 1: Create the Model
First, we will create a model class that represents the items. Create a new file named `Item.php` in the `App/Models` directory.

```php
<?php

namespace App\Models;

class Item
{
    private $items = [
        ['id' => 1, 'name' => 'Item 1'],
        ['id' => 2, 'name' => 'Item 2'],
        ['id' => 3, 'name' => 'Item 3'],
    ];

    public function getAllItems()
    {
        return $this->items;
    }
}
```

### Step 2: Create the Controller
Next, we will create a controller that handles the request and uses the model to fetch data. Create a new file named `ItemController.php` in the `App/Controllers/Web` directory.

```php
<?php

namespace App\Controllers\Web;

use Core\Controller;
use App\Models\Item;

class ItemController extends Controller
{
    public function index()
    {
        $itemModel = new Item();
        $data['items'] = $itemModel->getAllItems();
        $this->render('items/index', $data);
    }
}
```

### Step 3: Create the View
Now, we will create a view to display the list of items. Create a new directory named `items` in the `App/Views` directory, and then create a file named `index.php` inside the `items` directory.

```php
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
```

### Step 4: Define the Route
Open the `routes.php` file in the `Config` directory and add the following route:

```php
$router->add('GET', '/items', 'Web\ItemController', 'index');
```

### Step 5: Access the Application
Now you can access your application by navigating to `http://your-domain/items` in your web browser. You should see a list of items displayed on the page.

Thank you for using Phuse, and happy coding! 😊