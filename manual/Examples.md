# Examples

*Hello World* - Obligatory hello world example

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$klein = new \Klein\Klein();

$klein->respond('GET', '/hello-world', function () {
    return 'Hello World!';
});

$klein->dispatch();
```

*Example 1* - Respond to all requests

```php
$klein->respond(function () {
    return 'All the things';
});
```

*Example 2* - Named parameters

```php
$klein->respond('/[:name]', function ($request) {
    return 'Hello ' . $request->name;
});
```

*Example 3* - [So RESTful](http://bit.ly/g93B1s)

```php
$klein->respond('GET', '/posts', $callback);
$klein->respond('POST', '/posts', $callback);
$klein->respond('PUT', '/posts/[i:id]', $callback);
$klein->respond('DELETE', '/posts/[i:id]', $callback);
$klein->respond('OPTIONS', null, $callback);

// To match multiple request methods:
$klein->respond(array('POST','GET'), $route, $callback);

// Or you might want to handle the requests in the same place
$klein->respond('/posts/[create|edit:action]?/[i:id]?', function ($request, $response) {
    switch ($request->action) {
        //
    }
});
```

*Example 4* - Sending objects / files

```php
$klein->respond(function ($request, $response, $service) {
    $service->xml = function ($object) {
        // Custom xml output function
    }
    $service->csv = function ($object) {
        // Custom csv output function
    }
});

$klein->respond('/report.[xml|csv|json:format]?', function ($request, $response, $service) {
    // Get the format or fallback to JSON as the default
    $send = $request->param('format', 'json');
    $service->$send($report);
});

$klein->respond('/report/latest', function ($request, $response, $service) {
    $service->file('/tmp/cached_report.zip');
});
```

*Example 5* - All together

```php
$klein->respond(function ($request, $response, $service, $app) use ($klein) {
    // Handle exceptions => flash the message and redirect to the referrer
    $klein->onError(function ($klein, $err_msg) {
        $klein->service()->flash($err_msg);
        $klein->service()->back();
    });

    // The third parameter can be used to share scope and global objects
    $app->db = new PDO(...);

    // $app also can store lazy services, e.g. if you don't want to
    // instantiate a database connection on every response
    $app->register('db', function() {
        return new PDO(...);
    });
});

$klein->respond('POST', '/users/[i:id]/edit', function ($request, $response, $service, $app) {
    // Quickly validate input parameters
    $service->validateParam('username', 'Please enter a valid username')->isLen(5, 64)->isChars('a-zA-Z0-9-');
    $service->validateParam('password')->notNull();

    $app->db->query(...); // etc.

    // Add view properties and helper methods
    $service->title = 'foo';
    $service->escape = function ($str) {
        return htmlentities($str); // Assign view helpers
    };

    $service->render('myview.phtml');
});

// myview.phtml:
<title><?php echo $this->escape($this->title) ?></title>
```