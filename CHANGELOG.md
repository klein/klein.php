# CHANGELOG

## 2.1.0

### Features

- New exception and helper methods to help control the dispatch flow
- New `abort()` method to allow stopping the routing process and returning a response code
- Routes are now instances of a new `Route` class, instead of just being a set of meta properties and a callback
- Routes are now stored in a `RouteCollection` class, which extends the `DataCollection` class
- New `keys()` and `clear()` methods for the `DataCollection` class
- Added the capability of reverse routing!
- Now allowing for route callbacks to change the response object by returning a new ApiResponse instance
- New "slug" type for route param matching
- New `isEmpty()` and `cloneEmpty()` methods for the `DataCollection` class
- The `$matched` route callback parameter is now an instance of a `RouteCollection`, instead of just an integer
- Route callbacks are now passed the Klein instance for easier closure/class-scope use
- Regular expression routing is now more accurate and will match more special characters in a similar way to Sinatra
- Routes are now built with a dependency injected `AbstractRouteFactory` instance, allowing the building of routes to be customized more easily
- New `options()` and `head()` alias methods for matching OPTIONS and HEAD requests respectively
- The `Response` class has been abstracted into an `AbstractResponse` and a separate `Response` class for cleaner 3rd-party extension
- New "after dispatch" callbacks can be registered for firing a series of callbacks after the dispatch loop has completed
- New `patch()` alias method for matching PATCH requests
- New HTTP error handling via exceptions and callback registration for a more direct (and less magical) API for controlling HTTP errors
- The `escape()` method in the `ServiceProvider` class now allows for the passing of entity escaping flags
- Route regular expressions are now validated and provide helpful errors upon a validation failure
- Routes can now contain an empty string path
- The composer autoloader is now compatible with the PSR-4 standard.
- Regular expression compilation performance has been improved
- 100% Code Coverage

### Bug fixes

- The README document has been updated to fix a few typos and inconsistencies
- Route params are now properly URL decoded
- 404/405 routes now properly set the appropriate status code automatically
- Silencing the locked response exceptions as the behavior is designed to be transparent/automatic
- Allow route callables to be an array suitable for `call_user_func()` callable behavior
- More proper handling for 404's that also call the 404 error handlers
- The `file()` and `json()` methods in the `Response` class no longer override system-configured time processing limits
- Now checking if the output buffer is open before attempting to close it
- The methods matched counter (`$methods_matched`) is now much more accurate, not counting methods that shouldn't have been considered matches
- Various PHPdoc inaccuracies and inconsistencies have been fixed
- Regular expressions are now quoted during compilation in a much safer manner
- The PHPdoc tags have been updated to use the more modern syntax
