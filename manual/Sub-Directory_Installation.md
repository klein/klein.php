# Sub-Directory Installation

Klein.php is possible to be deployed in many types of environments that at least meet the PHP 5.3 version minimum requirement. Deploying Klein through a "sub-directory" and allowing Klein to still route in a relative path sense requires some configuration. Here's how.

All examples are based off of the following example paths:

* **Web Root**: "/srv/www/"
* **Klein installed**: "/srv/www/my-site/app/"

## In Server Configuration

### Apache (.htaccess)

For Apache, you'll need the rewrite module to change the `RewriteBase`. So if you're web application is served from a path relative to your webroot, you'll have change your `RewriteBase` to match the relative path from the web root. For example:

.htaccess file (or similar rules could be put in the Apache virtual-host config)
```
<IfModule mod_rewrite.c>
    Options -MultiViews

    RewriteEngine On
    RewriteBase /my-site/app/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### Nginx

For nginx, you'll have to modify your configuration to simply point to the sub-directory path, like so:

```nginx
server {
    location / {
        try_files $uri $uri/ /my-site/app/index.php?$args;
    }
}
```

## In Code

Klein.php has no configuration, so to deploy this url rewrite in a subdirectory you must invoke dispatch with a **custom "Request" object** as shown below:

```php
<?php

define('APP_PATH', '/my-site/app/');

$klein = new \Klein\Klein();
$request = \Klein\Request::createFromGlobals();

// Grab the server-passed "REQUEST_URI"
$uri = $request->server()->get('REQUEST_URI');

// Set the request URI to a modified one (without the "subdirectory") in it
$request->server()->set('REQUEST_URI', substr($uri, strlen(APP_PATH)));

// Pass our request to our dispatch method
$klein->dispatch($request);
```
This might also work for you (doesn't need a custom request object):

```php
<?php

$base  = dirname($_SERVER['PHP_SELF']);

// Update request when we have a subdirectory    
if(ltrim($base, '/')){ 

    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], strlen($base));
}

// Dispatch as always
$klein = new \Klein\Klein();
$klein->dispatch();
```

References:
- [https://github.com/chriso/klein.php/issues/5](https://github.com/chriso/klein.php/issues/5)
- [https://github.com/chriso/klein.php/issues/95](https://github.com/chriso/klein.php/issues/95)