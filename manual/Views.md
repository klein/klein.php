# Views

You can send properties or helpers to the view by assigning them
to the `$service` object, or by using the second arg of `$service->render()`

```php
$service->escape = function ($str) {
    return htmlentities($str);
};

$service->render('myview.phtml', array('title' => 'My View'));

// Or just: $service->title = 'My View';
```

*myview.phtml*

```html
<title><?php echo $this->escape($this->title) ?></title>
```

Views are compiled and run in the scope of `$service` so all service methods can be accessed with `$this`

```php
$this->render('partial.html')           // Render partials
$this->sharedData()->get('myvar')       // Access stored service variables
echo $this->query(array('page' => 2))   // Modify the current query string
```