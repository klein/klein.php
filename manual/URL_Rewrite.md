# URL Rewrite Config

Why rewrite URLs? Check [Wikipedia](http://en.wikipedia.org/wiki/Rewrite_engine)

## Apache

Make sure [AllowOverride](http://httpd.apache.org/docs/2.0/mod/core.html#allowoverride) is on for your directory, or put in `httpd.conf`

    # Apache (.htaccess or httpd.conf)
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule . /index.php [L] 

## nginx

    # basics
    try_files $uri $uri/ /index.php?$args;

If you're trying to route requests for an app that is *not* in the document root, invoke klein's dispatch line like this:

    <?php
       define('APP_PATH', '/your/webapp');
       $klein->dispatch(substr($_SERVER['REQUEST_URI'], strlen(APP_PATH)));
    ?>

Then in your `nginx.conf` file, use:

    location /your/webapp/ {
       try_files $uri $uri/ /your/webapp/index.php?$args;
    }

**Don't** do this.

    # nginx
    if (!-e $request_filename) {
        rewrite . /index.php last;
    }

See [nginx pitfalls](http://wiki.nginx.org/Pitfalls).

## More Reading
  * http://httpd.apache.org/docs/2.0/mod/mod_rewrite.html
  * http://wiki.nginx.org/HttpRewriteModule
  * http://wiki.nginx.org/Pitfalls
  * [klein.php](https://github.com/chriso/klein.php) - simple, fast router for PHP

Source: https://gist.github.com/jamesvl/910325
