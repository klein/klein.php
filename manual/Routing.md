# Routing

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

## Matching Multiple Routes

*All* routes that match the request URI are called - this
allows you to incorporate complex conditional logic such as user
authentication or view layouts. e.g. as a basic example, the following
code will wrap other routes with a header and footer

```php
$klein->respond('*', function ($request, $response, $service) { $service->render('header.phtml'); });
//other routes
$klein->respond('*', function ($request, $response, $service) { $service->render('footer.phtml'); });
```

In some cases, you may need to override this matching behavior.  For instance, if you have several specific routes, followed by a much broader route.

```php
// Backend (Admin panel)

$klein->respond('/admin/?', function ($request, $response, $service) {
   if(isAdmin()) {
      $service->render('admin.phtml');
   } else {
      permissionDenied();
   }
});

// Main Views

$klein->respond('/[:page]/?', function ($request, $response, $service) {
    $service->render($request->page.'.phtml');
});
```

The code above will match both routes, for `/admin` and render both views, making a big mess.  You can either call `$klein->skipRemaining()` to skip the other routes, or you could call `$response->send()`.  It looks like either would accomplish the goal, but I'm not sure what the difference in effect would be, or the scenarios where one is more appropriate than the other.

## Matching Partial URIs

Routes automatically match the entire request URI. If you need to match
only a part of the request URI or use a custom regular expression, use the `@` operator. If you need to
negate a route, use the `!` operator

```php
// Match all requests that end with '.json' or '.csv'
$klein->respond('@\.(json|csv)$', ...

// Match all requests that _don't_ start with /admin
$klein->respond('!@^/admin/', ...
```
