**klein.php** is a lightning fast router for PHP 5.3+

* Flexible regular expression routing (inspired by [Sinatra](http://www.sinatrarb.com/))
* A set of [boilerplate methods](https://github.com/chriso/klein.php/wiki/API) for rapidly building web apps
* Almost no overhead => [2500+ requests/second](https://gist.github.com/878833)

## Getting started

1. PHP 5.3.x is required
2. Setup [URL rewriting](https://gist.github.com/874000) so that all requests are handled by **index.php**
3. Add `<?php require 'klein.php';` as your first line and `dispatch();` as your last
4. (Optional) Throw in some [APC](http://pecl.php.net/package/APC) for good measure

## Example

*Example 1* - Respond to all requests

```php
<?php
respond(function () {
    echo 'Hello World!';
});
```

*Example 2* - Named parameters

```php
<?php
respond('/[:name]', function ($request) {
    echo 'Hello ' . $request->name;
});
```

*Example 3* - [So RESTful](http://bit.ly/g93B1s)

```php
<?php
respond('GET', '/posts', $callback);
respond('POST', '/posts/create', $callback);
respond('PUT', '/posts/[i:id]', $callback);
respond('DELETE', '/posts/[i:id]', $callback);

// To match multiple request methods:
respond(array('POST','GET'), $route, $callback);

// Or you might want to handle the requests in the same place
respond('/posts/[create|edit:action]?/[i:id]?', function ($request, $response) {
    switch ($request->action) {
        //
    }
});
```

*Example 4* - Sending objects / files

```php
<?php
respond(function ($request, $response) {
    $response->xml = function ($object) {
        // Custom xml output function
    }
    $response->csv = function ($object) {
        // Custom csv output function
    }
});

respond('/report.[xml|csv|json:format]?', function ($reqest, $response) {
    // Get the format or fallback to JSON as the default
    $send = $request->param('format', 'json');
    $response->$send($report);
});

respond('/report/latest', function ($request, $response) {
    $response->file('/tmp/cached_report.zip');
});
```

*Example 5* - All together

```php
<?php
respond(function ($request, $response, $app) {
    // Handle exceptions => flash the message and redirect to the referrer
    $response->onError(function ($response, $err_msg) {
        $response->flash($err_msg);
        $response->back();
    });

    // The third parameter can be used to share scope and global objects
    $app->db = new PDO(...);
    // $app also can store lazy services, e.g. if you don't want to
    // instantiate a database connection on every response
    $app->register('db', function() {
        return new PDO(...);
    });
});

respond('POST', '/users/[i:id]/edit', function ($request, $response) {
    // Quickly validate input parameters
    $request->validate('username', 'Please enter a valid username')->isLen(5, 64)->isChars('a-zA-Z0-9-');
    $request->validate('password')->notNull();

    $app->db->query(...); // etc.

    // Add view properties and helper methods
    $response->title = 'foo';
    $response->escape = function ($str) {
        return htmlentities($str); // Assign view helpers
    };

    $response->render('myview.phtml');
});

// myview.phtml:
<title><?php echo $this->escape($this->title) ?></title>
```

## Route namespaces

```php
<?php
with('/users', function () {

    respond('GET', '/?', function ($request, $response) {
        // Show all users
    });

    respond('GET', '/[:id]', function ($request, $response) {
        // Show a single user
    });

});

foreach(array('projects', 'posts') as $controller) {
    with("/$controller", "controllers/$controller.php");
}
```

## Lazy services

Services can be stored **lazily**, meaning that they are only instantiated on
first use.

``` php
<?php
respond(function ($request, $response, $app) {
    $app->register('lazyDb', function() {
        $db = new stdClass();
        $db->name = 'foo';
        return $db;
    });
});

//Later

respond('GET', '/posts', function ($request, $response, $app) {
    // $db is initialised on first request
    // all subsequent calls will use the same instance
    echo $app->lazyDb->name;
});
```

## Validators

To add a custom validator use `addValidator($method, $callback)`

```php
<?php
addValidator('hex', function ($str) {
    return preg_match('/^[0-9a-f]++$/i', $str);
});
```

You can validate parameters using `is<$method>()` or `not<$method>()`, e.g.

```php
$request->validate('key')->isHex();
```

Validation methods are chainable, and a custom exception message can be specified for if/when validation fails

```php
$request->validate('key', 'The key was invalid')->isHex()->isLen(32);
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
respond('*', function ($request, $response) { $response->render('header.phtml'; });
//other routes
respond('*', function ($request, $response) { $response->render('footer.phtml'; });
```

Routes automatically match the entire request URI. If you need to match
only a part of the request URI or use a custom regular expression, use the `@` operator. If you need to
negate a route, use the `!` operator

```php
// Match all requests that end with '.json' or '.csv'
respond('@\.(json|csv)$', ...

// Match all requests that _don't_ start with /admin
respond('!@^/admin/', ...
```

## Views

You can send properties or helpers to the view by assigning them
to the `$response` object, or by using the second arg of `$response->render()`

```php
<?php
$response->escape = function ($str) {
    return htmlentities($str);
};

$response->render('myview.phtml', array('title' => 'My View'));

// Or just: $response->title = 'My View';
```

*myview.phtml*

```html
<title><?php echo $this->escape($this->title) ?></title>
```

Views are compiled and run in the scope of `$response` so all response methods can be accessed with `$this`

```php
<?php
$this->render('partial.html')           // Render partials
$this->param('myvar')                   // Access request parameters
echo $this->query(array('page' => 2))   // Modify the current query string
```

## API

```php
<?php
$request->
    header($key, $default = null)       // Get a request header
    cookie($key, $default = null)       // Get a cookie from the request
    session($key, $default = null)      // Get a session variable
    param($key, $default = null)        // Get a request parameter (get, post, named)
    params()                            // Return all parameters
    params($mask = null)                // Return all parameters that match the mask array - extract() friendly
    validate($param, $err_msg = null)   // Start a validator chain
    method()                            // Get the request method
    method($method)                     // Check if the request method is $method, i.e. method('post') => true
    isSecure($required = false)         // https? Redirect if $required is true and the request is not secure
    id()                                // Get a unique ID for the request
    ip()                                // Get the request IP
    userAgent()                         // Get the request user agent
    uri()                               // Get the request URI
    <param>                             // Get / Set (if assigned a value)a request parameter

$response->
    header($key, $value = null)                     // Set a response header
    cookie($key, $value = null, $expiry = null)     // Set a cookie
    cookie($key, null)                              // Remove a cookie
    session($key, $value = null)                    // Sets a session variable
    flash($msg, $type = 'info', $params = array()   // Set a flash message
    file($path, $filename = null)                   // Send a file
    noCache()                                       // Tell the browser not to cache the response
    json($object, $jsonp_prefix = null)             // Send an object as JSON or JSONP by providing padding prefix
    markdown($str, $args, ...)                      // Return a string formatted with markdown
    code($code = null)                              // Return the HTTP response code, or send a new code
    redirect($url, $code = 302)                     // Redirect to the specified URL
    refresh()                                       // Redirect to the current URL
    back()                                          // Redirect to the referer
    render($view, $data = array())                  // Render a view or partial (in the scope of $response)
    partial($view, $data = array())                 // Render a partial without a layout (in the scope of $response)
    layout($layout)                                 // Set the view layout
    yield()                                         // Call inside the layout to render the view content
    error(Exception $err)                           // Routes an exception through the error callbacks
    onError($callback)                              // $callback takes ($response, $msg, $err_type = null)
    set($key, $value = null)                        // Set a view property or helper
    set($arr)
    escape($str)                                    // Escape a string
    query($key, $value = null)                      // Modify the current query string
    query($arr)
    param($param, $default = null)                  // Get an escaped request parameter
    flashes($type = null)                           // Retrieve and clears all flashes of $type
    flush()                                         // Flush all open output buffers
    discard($restart_buffer = false)                // Discard all open output buffers and optionally restart it
    buffer()                                        // Return the contents of the output buffer as a string
    chunk($str = null)                              // Enable response chunking (see the wiki)
    dump($obj)                                      // Dump an object
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
