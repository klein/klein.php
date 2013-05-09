---
layout: klein
title: Home
github_name: klein.php
---

**klein.php** is a lightning fast router for PHP 5.3+

* Flexible regular expression routing (inspired by [Sinatra](http://www.sinatrarb.com/))
* A set of boilerplate methods for rapidly building web apps
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
