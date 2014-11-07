# Klein Upgrade Guide

## 2.0.x to 2.1.0

### Deprecations

- Handling 404 and 405 errors with a specially registered route callback is now deprecated. It's now suggested to use Klein's new `onHttpError()` method instead.
- Autoloading the library with Composer no longer utilizes the PSR-0 spec. The composer autoloader now uses PSR-4.

### Interface Changes

- Some of the route callback params have changed. This will effect any route definitions with callbacks using the more advanced parameters.
    - The old params were (in order):
        - `Request $request`
        - `Response $response`
        - `Service $service`
        - `App $app`
        - `int $matched`
        - `array $methods_matched`
    - The new params are (in order):
        - `Request $request`
        - `Response $response`
        - `Service $service`
        - `App $app`
        - `Klein $klein`
        - `RouteCollection $matched`
        - `array $methods_matched`
- Non-match routes (routes that are wildcard and shouldn't consider as "matches") will no longer be considered as part of the "methods matched" array, since they aren't supposed to be matches in the first place
    - This may have implications for users that have created "match-all" OPTIONS method routes, as the OPTIONS method will no longer be considered a match.
    - If you'd like to conserve the old match behavior, you can simply mark the route as one that should be counted as a match with `$route->setCountMatch(true)`
