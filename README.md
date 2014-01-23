# Klein.php

[![Build Status](https://travis-ci.org/chriso/klein.php.png?branch=master)](https://travis-ci.org/chriso/klein.php)

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

## Quick Start

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

## Manual

+ [Examples](manual/Examples.md)
+ [Route Namespaces](manual/Route_Namespaces.md)
+ [Lazy Services](manual/Routing.md)
+ [Validators](manual/Validators.md)
+ [Routing](manual/Routing.md)
+ [Views](manual/Views.md)
+ [Flash Messages](manual/Views.md)
+ [HTTP Errors](manual/HTTP_Errors.md)
+ [Sub-Directory Installation](manual/Sub-Directory_Installation.md)
+ [URL Rewrite Config](manual/URL_rewrite.md)
+ [API](manual/API.md)
+ [Unit Testing](manual/Unit_Testing.md)
+ [Contributing](CONTRIBUTING.md)

## Contributors

- [Trevor N. Suarez](https://github.com/Rican7)

## License

(MIT License)

Copyright (c) 2010 Chris O'Hara <cohara87@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
