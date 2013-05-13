# Klein.php

**klein.php** is a lightning fast router for PHP 5.3+

* Flexible regular expression routing (inspired by [Sinatra](http://www.sinatrarb.com/))
* A set of [boilerplate methods](#api) for rapidly building web apps
* Almost no overhead => [2500+ requests/second](https://gist.github.com/878833)

## Getting started

1. PHP 5.3.x is required
2. Install Klein using [Composer](#composer-installation) (recommended) or manually
3. Setup [URL rewriting](https://gist.github.com/874000) so that all requests are handled by **index.php**
4. (Optional) Throw in some [APC](http://pecl.php.net/package/APC) for good measure

## Composer Installation

1. Get [Composer](http://getcomposer.org/)
2. Require Klein with `php composer.phar require klein/klein v2.0.x`
3. Install dependencies with `php composer.phar install`

## Example

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
$klein->respond('POST', '/posts/create', $callback);
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

$klein->respond('/report.[xml|csv|json:format]?', function ($reqest, $response, $service) {
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
$klein->respond(function ($request, $response, $service, $app) {
    // Handle exceptions => flash the message and redirect to the referrer
    $response->onError(function ($klein, $err_msg) {
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

## Route namespaces

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

## Lazy services

Services can be stored **lazily**, meaning that they are only instantiated on
first use.

``` php
<?php
$klein->respond(function ($request, $response, $service, $app) {
    $app->register('lazyDb', function() {
        $db = new stdClass();
        $db->name = 'foo';
        return $db;
    });
});

//Later

$klein->respond('GET', '/posts', function ($request, $response, $service, $app) {
    // $db is initialised on first request
    // all subsequent calls will use the same instance
    return $app->lazyDb->name;
});
```

## Validators

To add a custom validator use `addValidator($method, $callback)`

```php
$service->addValidator('hex', function ($str) {
    return preg_match('/^[0-9a-f]++$/i', $str);
});
```

You can validate parameters using `is<$method>()` or `not<$method>()`, e.g.

```php
$service->validateParam('key')->isHex();
```

Or you can validate any string using the same flow..

```php
$service->validate($username)->isLen(4,16);
```

Validation methods are chainable, and a custom exception message can be specified for if/when validation fails

```php
$service->validateParam('key', 'The key was invalid')->isHex()->isLen(32);
```

## Routing

**[** *match_type* **:** *param_name* **]**

Some examples

    *                    // Match all request URIs
    [i]                  // Match an integer
    [i:id]               // Match an integer as 'id'
    [a:action]           // Match alphanumeric characters as 'action'
    [h:key]              // Match hexadecimal characters as 'key'
    [:action]            // Match anything up to the next / or end of the URI as 'action'
    [create|edit:action] // Match either 'create' or 'edit' as 'action'
    [*]                  // Catch all (lazy)
    [*:trailing]         // Catch all as 'trailing' (lazy)
    [**:trailing]        // Catch all (possessive - will match the rest of the URI)
    .[:format]?          // Match an optional parameter 'format' - a / or . before the block is also optional

Some more complicated examples

    /posts/[*:title][i:id]     // Matches "/posts/this-is-a-title-123"
    /output.[xml|json:format]? // Matches "/output", "output.xml", "output.json"
    /[:controller]?/[:action]? // Matches the typical /controller/action format

**Note** - *all* routes that match the request URI are called - this
allows you to incorporate complex conditional logic such as user
authentication or view layouts. e.g. as a basic example, the following
code will wrap other routes with a header and footer

```php
$klein->respond('*', function ($request, $response, $service) { $service->render('header.phtml'; });
//other routes
$klein->respond('*', function ($request, $response, $service) { $service->render('footer.phtml'; });
```

Routes automatically match the entire request URI. If you need to match
only a part of the request URI or use a custom regular expression, use the `@` operator. If you need to
negate a route, use the `!` operator

```php
// Match all requests that end with '.json' or '.csv'
$klein->respond('@\.(json|csv)$', ...

// Match all requests that _don't_ start with /admin
$klein->respond('!@^/admin/', ...
```

## Views

You can send properties or helpers to the view by assigning them
to the `$service` object, or by using the second arg of `$service->render()`

```php
$service->escape = function ($str) {
    return htmlentities($str);
};

$service->render('myview.phtml', array('title' => 'My View'));

// Or just: $service->title = 'My View';
```

*myview.phtml*

```html
<title><?php echo $this->escape($this->title) ?></title>
```

Views are compiled and run in the scope of `$service` so all service methods can be accessed with `$this`

```php
$this->render('partial.html')           // Render partials
$this->sharedData()->get('myvar')       // Access stored service variables
echo $this->query(array('page' => 2))   // Modify the current query string
```

## API

```php
$request->
    id($hash = true)                    // Get a unique ID for the request
    paramsGet()                         // Return the GET parameter collection
    paramsPost()                        // Return the POST parameter collection
    paramsNamed()                       // Return the named parameter collection
    cookies()                           // Return the cookies collection
    server()                            // Return the server collection
    headers()                           // Return the headers collection
    files()                             // Return the files collection
    body()                              // Get the request body
    params()                            // Return all parameters
    params($mask = null)                // Return all parameters that match the mask array - extract() friendly
    param($key, $default = null)        // Get a request parameter (get, post, named)
    isSecure()                          // Was the request sent via HTTPS?
    ip()                                // Get the request IP
    userAgent()                         // Get the request user agent
    uri()                               // Get the request URI
    method()                            // Get the request method
    method($method)                     // Check if the request method is $method, i.e. method('post') => true
    <param>                             // Get / Set (if assigned a value) a request parameter

$response->
    protocolVersion($protocol_version = null)       // Get the protocol version, or set it to the passed value
    body($body = null)                              // Get the response body's content, or set it to the passed value
    status()                                        // Get the response's status object
    headers()                                       // Return the headers collection
    cookies()                                       // Return the cookies collection
    code($code = null)                              // Return the HTTP response code, or set it to the passed value
    prepend($content)                               // Prepend a string to the response body
    append($content)                                // Append a string to the response body
    isLocked()                                      // Check if the response is locked
    lock()                                          // Lock the response from further modification
    unlock()                                        // Unlock the response
    sendHeaders($override = false)                  // Send the HTTP response headers
    sendBody()                                      // Send the response body's content
    send()                                          // Send the response and lock it
    chunk($str = null)                              // Enable response chunking (see the wiki)
    header($key, $value = null)                     // Set a response header
    cookie($key, $value = null, $expiry = null)     // Set a cookie
    cookie($key, null)                              // Remove a cookie
    noCache()                                       // Tell the browser not to cache the response
    redirect($url, $code = 302)                     // Redirect to the specified URL
    dump($obj)                                      // Dump an object

$service->
    sharedData()                                    // Return the shared data collection
    startSession()                                  // Start a session and return its ID
    flash($msg, $type = 'info', $params = array()   // Set a flash message
    flashes($type = null)                           // Retrieve and clears all flashes of $type
    markdown($str, $args, ...)                      // Return a string formatted with markdown
    escape($str)                                    // Escape a string
    file($path, $filename = null)                   // Send a file
    json($object, $jsonp_prefix = null)             // Send an object as JSON or JSONP by providing padding prefix
    refresh()                                       // Redirect to the current URL
    back()                                          // Redirect to the referer
    query($key, $value = null)                      // Modify the current query string
    query($arr)
    layout($layout)                                 // Set the view layout
    yieldView()                                     // Call inside the layout to render the view content
    render($view, $data = array())                  // Render a view or partial (in the scope of $response)
    partial($view, $data = array())                 // Render a partial without a layout (in the scope of $response)
    addValidator($method, $callback)                // Add a custom validator method
    validate($string, $err = null)                  // Validate a string (with a custom error message)
    validateParam($param, $err = null)                  // Validate a param
    <callback>($arg1, ...)                          // Call a user-defined helper
    <property>                                      // Get a user-defined property

$app->
    <callback>($arg1, ...)                          //Call a user-defined helper

$validator->
    notNull()                           // The string must not be null
    isLen($length)                      // The string must be the exact length
    isLen($min, $max)                   // The string must be between $min and $max length (inclusive)
    isInt()                             // Check for a valid integer
    isFloat()                           // Check for a valid float/decimal
    isEmail()                           // Check for a valid email
    isUrl()                             // Check for a valid URL
    isIp()                              // Check for a valid IP
    isAlpha()                           // Check for a-z (case insensitive)
    isAlnum()                           // Check for alphanumeric characters
    contains($needle)                   // Check if the string contains $needle
    isChars($chars)                     // Validate against a character list
    isRegex($pattern, $modifiers = '')  // Validate against a regular expression
    notRegex($pattern, $modifiers ='')
    is<Validator>()                     // Validate against a custom validator
    not<Validator>()                    // The validator can't match
    <Validator>()                       // Alias for is<Validator>()
```

## Unit Testing

Unit tests are a crucial part of developing a routing engine such as Klein.
Added features or bug-fixes can have adverse effects that are hard to find
without a lot of testing, hence the importance of unit testing.

This project uses [PHPUnit](https://github.com/sebastianbergmann/phpunit/) as
its unit testing framework.

The tests all live in `/tests` and each test extends an abstract class
`AbstractKleinTest`

To test the project, simply run `php composer.phar install --dev` to download
a common version of PHPUnit with composer and run the tests from the main
directory with `./vendor/bin/phpunit`

## Contributing

Contributing is absolutely encouraged, but a few things should be taken into
account:

- Always test any bug-fixes or changes with [unit testing](#unit-testing)
- When adding or changing a feature, make sure to write a **new** [unit test](#unit-testing)
- Please try to adhere to the standards made obvious in the class source files
   - This project uses ["soft tabs"](http://vim.wikia.com/wiki/Converting_tabs_to_spaces), 
   please don't use any hard tabbing
   - Make sure to document your code with the 
   [PHPDoc syntax](http://www.phpdoc.org/docs/latest/for-users/phpdoc-reference.html)
- When creating pull requests, make sure to have checked your code for styling
  and create useful/verbose PR messages

## More information

See the [wiki](https://github.com/chriso/klein.php/wiki) for more information

## Contributors

- [Trevor N. Suarez](https://github.com/Rican7)

## License

(MIT License)

Copyright (c) 2010 Chris O'Hara <cohara87@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
