---
layout: klein
title: Home
github_name: klein.php
---

**klein.php** is a lightning fast router for PHP 5.3+

* Flexible regular expression routing (inspired by [Sinatra](http://www.sinatrarb.com/))
* A set of boilerplate methods for rapidly building web apps
* Almost no overhead => [2500+ requests/second](https://gist.github.com/878833)

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
