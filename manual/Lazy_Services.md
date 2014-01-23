# Lazy services

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