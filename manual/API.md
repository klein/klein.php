# API

Below is a list of the public methods in the common classes you will most likely use. For a more formal source
of class/method documentation, please see the [PHPdoc generated documentation](http://chriso.github.io/klein.php/docs/).

```php
$request->
    id($hash = true)                    // Get a unique ID for the request
    paramsGet()                         // Return the GET parameter collection
    paramsPost()                        // Return the POST parameter collection
    paramsNamed()                       // Return the named parameter collection
    cookies()                           // Return the cookies collection
    server()                            // Return the server collection
    headers()                           // Return the headers collection
    files()                             // Return the files collection
    body()                              // Get the request body
    params()                            // Return all parameters
    params($mask = null)                // Return all parameters that match the mask array - extract() friendly
    param($key, $default = null)        // Get a request parameter (get, post, named)
    isSecure()                          // Was the request sent via HTTPS?
    ip()                                // Get the request IP
    userAgent()                         // Get the request user agent
    uri()                               // Get the request URI
    pathname()                          // Get the request pathname
    method()                            // Get the request method
    method($method)                     // Check if the request method is $method, i.e. method('post') => true
    query($key, $value = null)          // Get, add to, or modify the current query string
    <param>                             // Get / Set (if assigned a value) a request parameter

$response->
    protocolVersion($protocol_version = null)       // Get the protocol version, or set it to the passed value
    body($body = null)                              // Get the response body's content, or set it to the passed value
    status()                                        // Get the response's status object
    headers()                                       // Return the headers collection
    cookies()                                       // Return the cookies collection
    code($code = null)                              // Return the HTTP response code, or set it to the passed value
    prepend($content)                               // Prepend a string to the response body
    append($content)                                // Append a string to the response body
    isLocked()                                      // Check if the response is locked
    requireUnlocked()                               // Require that a response is unlocked
    lock()                                          // Lock the response from further modification
    unlock()                                        // Unlock the response
    sendHeaders($override = false)                  // Send the HTTP response headers
    sendCookies($override = false)                  // Send the HTTP response cookies
    sendBody()                                      // Send the response body's content
    send()                                          // Send the response and lock it
    isSent()                                        // Check if the response has been sent
    chunk($str = null)                              // Enable response chunking (see the wiki)
    header($key, $value = null)                     // Set a response header
    cookie($key, $value = null, $expiry = null)     // Set a cookie
    cookie($key, null)                              // Remove a cookie
    noCache()                                       // Tell the browser not to cache the response
    redirect($url, $code = 302)                     // Redirect to the specified URL
    dump($obj)                                      // Dump an object
    file($path, $filename = null)                   // Send a file
    json($object, $jsonp_prefix = null)             // Send an object as JSON or JSONP by providing padding prefix

$service->
    sharedData()                                    // Return the shared data collection
    startSession()                                  // Start a session and return its ID
    flash($msg, $type = 'info', $params = array()   // Set a flash message
    flashes($type = null)                           // Retrieve and clears all flashes of $type
    markdown($str, $args, ...)                      // Return a string formatted with markdown
    escape($str)                                    // Escape a string
    refresh()                                       // Redirect to the current URL
    back()                                          // Redirect to the referer
    query($key, $value = null)                      // Modify the current query string
    query($arr)
    layout($layout)                                 // Set the view layout
    yieldView()                                     // Call inside the layout to render the view content
    render($view, $data = array())                  // Render a view or partial (in the scope of $response)
    partial($view, $data = array())                 // Render a partial without a layout (in the scope of $response)
    addValidator($method, $callback)                // Add a custom validator method
    validate($string, $err = null)                  // Validate a string (with a custom error message)
    validateParam($param, $err = null)                  // Validate a param
    <callback>($arg1, ...)                          // Call a user-defined helper
    <property>                                      // Get a user-defined property

$app->
    <callback>($arg1, ...)                          //Call a user-defined helper

$validator->
    notNull()                           // The string must not be null
    isLen($length)                      // The string must be the exact length
    isLen($min, $max)                   // The string must be between $min and $max length (inclusive)
    isInt()                             // Check for a valid integer
    isFloat()                           // Check for a valid float/decimal
    isEmail()                           // Check for a valid email
    isUrl()                             // Check for a valid URL
    isIp()                              // Check for a valid IP
    isAlpha()                           // Check for a-z (case insensitive)
    isAlnum()                           // Check for alphanumeric characters
    contains($needle)                   // Check if the string contains $needle
    isChars($chars)                     // Validate against a character list
    isRegex($pattern, $modifiers = '')  // Validate against a regular expression
    notRegex($pattern, $modifiers ='')
    is<Validator>()                     // Validate against a custom validator
    not<Validator>()                    // The validator can't match
    <Validator>()                       // Alias for is<Validator>()
```