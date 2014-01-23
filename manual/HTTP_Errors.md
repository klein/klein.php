# HTTP Errors

To handle 404 errors, or any HTTP error, you can add a special handler alongside your routes.  You simply pass the handler method a callback, much like you would a route.  The callback receives the following parameters:

* int `$code` The HTTP error code
* Klein `$router` The router instance
* RouteCollection `$matched` The collection of routes that were matched in dispatch
* array `$methods_matched` The HTTP methods that were matched in dispatch
* HttpExceptionInterface `$http_exception` The exception that occurred

Following are a couple examples:

```php
<?php
// Using exact code behaviors via switch/case
$klein->onHttpError(function ($code, $router) {
    switch ($code) {
        case 404:
            $router->response()->body(
                'Y U so lost?!'
            );
            break;
        case 405:
            $router->response()->body(
                'You can\'t do that!'
            );
            break;
        default:
            $router->response()->body(
                'Oh no, a bad error happened that caused a '. $code
            );
    }
});

// Using range behaviors via if/else
$klein->onHttpError(function ($code, $router) {
    if ($code >= 400 && $code < 500) {
        $router->response()->body(
            'Oh no, a bad error happened that caused a '. $code
        );
    } elseif ($code >= 500 && $code <= 599) {
        error_log('uhhh, something bad happened');
    }
});
```

The instructions above represent the current recommended technique for handling HTTP errors.  Below is the older method, which should still work, but **may be deprecated in future.**

Add a route for `404` as your *last* route. If no other routes are matched, the specified callback will be called.

```php
<?php
$klein->respond('404', function ($request) {
    $page = $request->uri();
    echo "Oops, it looks like $page doesn't exist..\n";
});
```

**But I need some other route(s) for setting up layouts, etc.**

If you don't want a certain `respond()` call to be counted as a match, just call it without a route:

```php
<?php
$klein->respond(function ($request, $response, $app) {
    $response->layout('layout.phtml');
    //etc.
});
```