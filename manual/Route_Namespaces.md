# Route Namespaces

```php
$klein->with('/users', function () use ($klein) {

    $klein->respond('GET', '/?', function ($request, $response) {
        // Show all users
    });

    $klein->respond('GET', '/[:id]', function ($request, $response) {
        // Show a single user
    });

});

foreach(array('projects', 'posts') as $controller) {
    // Include all routes defined in a file under a given namespace
    $klein->with("/$controller", "controllers/$controller.php");
}
```

Included files are run in the scope of Klein (`$klein`) so all Klein
methods/properties can be accessed with `$this`

_Example file for: "controllers/projects.php"_
```php
// Routes to "/projects/?"
$this->respond('GET', '/?', function ($request, $response) {
    // Show all projects
});
```