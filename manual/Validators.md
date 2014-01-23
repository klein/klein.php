# Validators

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