# Flash Messages

Klein provides a Flash Message system, which allows you to store messages for the user in the session array, to be presented to the user at a later time, in a future request.

They usually look something like this: http://getbootstrap.com/components/#alerts

## Create

To store such a message, from inside your route callback, you would call something like:

```php
<?php
$service->flash('Do *NOT* go in there!','warning');
?>
```

The first parameter is a string containing the message you want sent to the user.  You can use basic markdown syntax (basically just links, bold, and italics), which will be converted to HTML during rendering.

The second parameter is a message type.  This is an arbitrary string.  You can make up whatever types make sense for your app.  This parameter is optional.  If you leave it blank, the default value 'info' will be used.

## Retrieve

The flash messages are stored in $_SESSION['__flashes'].  However, you should not access them directly.  To retrive them, you use `Klein\ServiceProvider::flashes()`.  This method retrieves and clears all the flash messages, or all the flash messages of a given type.

If not type parameter is passed to the method, it returns an array of flashes, grouped by type, so you can foreach and echo them. If you're using the Klein templating system, then you can call the ServiceProvider from the template as `$this`.

So in your template, you would have something like:

```php
<? foreach($this->flashes() as $type=>$messages): ?>
	<? foreach($messages as $msg): ?>
		<div class="alert alert-<?= $type ?>"><?= $msg ?>
	<? endforeach; ?>
<? endforeach; ?>
```

Note that we first loop through the types, and then for each type, we loop through the messages.  The code above will format the flash messages appropriately to work with [Bootstrap](http://getbootstrap.com), assuming your types correspond to theirs (success, info, warning, or danger).

## Caution

The two methods involved in handling flash messages are very similar, but not interchangeable.  The singular method `Klein\ServiceProvider::flash()` creates a flash message, while the plural method `Klein\ServiceProvider::flashes()` retrieves them.

## More Info

+ http://chriso.github.io/klein.php/docs/classes/Klein.ServiceProvider.html#method_flashes
+ http://chriso.github.io/klein.php/docs/classes/Klein.ServiceProvider.html#method_flash
+ http://chriso.github.io/klein.php/docs/classes/Klein.ServiceProvider.html#merkdown

Source: http://stackoverflow.com/a/21195011/1004008