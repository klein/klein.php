# Response Chunking

Read [this article](http://weblog.rubyonrails.org/2011/4/18/why-http-streaming) to understand how response chunking works and how it might benefit your app.

To send a string as a chunk

```php
$response->chunk($str);
```

To flush the contents of the output buffer as a response chunk

```php
$response->chunk();
```

After calling `chunk()`, views will be chunked too

```php
$response->render('mytemplate.phtml');
```

*Note: calling `$response->chunk()` for the first time sets the appropriate header (`Transfer-Encoding: chunked`).*