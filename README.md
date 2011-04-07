**klein.php** is a lightning fast router for PHP 5.3+. In < 600 lines you get

* Sinatra-like routing
* Almost no overhead = as much speed as you can possibly squeeze from PHP ([2200 requests/second+](https://gist.github.com/878833))
* A small but powerful set of of methods for rapidly building web apps

## Getting started

1. PHP 5.3+ is required
2. Setup some form of [front controller URL rewriting](https://gist.github.com/874000)
3. Add `<?php require 'klein.php';` as your first line and `dispatch();` as your last
4. (Optional) Throw in some [APC](http://pecl.php.net/package/APC) for good measure

## Example

*Example 1* - Respond to all requests

    respond('*', function ($request, $response) {
        echo 'Hello World!';
    });

*Example 2* - Named parameters

    respond('GET', '/[:name]?', function ($request, $response) {
        $name = $request->param('name', 'world');
        echo "Hello $name!";
    });

*Example 3* - [So RESTful](http://bit.ly/g93B1s)

    respond('GET',    '/posts',       $callback);
    respond('POST',   '/post/create', $callback);
    respond('PUT',    '/post/[i:id]', $callback);
    respond('DELETE', '/post/[i:id]', $callback);

    //To match multiple request methods:
    respond(array('POST','GET'), $route, $callback);

*Example 4* - All together

    respond('*', function ($reguest, $response, $app) {
        //Handle exceptions => flash the message and redirect to the referrer
        $response->onError(function ($response, $err_msg) {
            $response->flash($err_msg);
            $response->back();
        });

        //The third parameter can be used to share scope and global objects
        $app->db = new PDO(...);
    });

    respond('POST', '/users/[i:id]/edit', function ($request, $response) {
        //Quickly validate input parameters
        $request->validate('username', 'Please enter a valid username')->isLen(5, 64)->isChars('a-zA-Z0-9-');
        $request->validate('password')->notNull();

        $app->db->query(...); //etc.

        //Add view properties and helper methods
        $response->title = 'foo';
        $response->escape = function ($str) {
            return htmlentities($str); //Assign view helpers
        };

        $response->render('myview.phtml');
    });

    //myview.phtml:
    <title><?php echo $this->escape($this->title) ?></title>

## Validators

To add a custom validator use `addValidator($method, $callback)`

    addValidator('hex', function ($str) {
        return preg_match('/^[0-9a-f]++$/i', $str);
    });

You can validate parameters using `is<$method>()` or `not<$method>()`, e.g.

    $request->validate('key')->isHex();

Validation methods are chainable, and a custom exception message can be specified for if/when validation fails

    $request->validate('key', 'The key was invalid')->isHex()->isLen(32);

## Routing

**[** *match_type* **:** *param_name* **]**

Some examples

    *                    //Match all request URIs
    [i]                  //Match an integer
    [i:id]               //Match an integer as 'id'
    [a:action]           //Match alphanumeric characters as 'action'
    [h:key]              //Match hexadecimal characters as 'key'
    [:action]            //Match anything up to the next / or end of the URI as 'action'
    [create|edit:action] //Match either 'create' or 'edit' as 'action'
    [*]                  //Catch all (lazy)
    [*:trailing]         //Catch all as 'trailing' (lazy)
    [**:trailing]        //Catch all (possessive - will match the rest of the URI)
    .[:format]?          //Matches an optional parameter 'format'. A / or . character before the block is also optional

Some more complicated examples

    /posts/[*:title][i:id]    //Matches "/posts/this-is-a-title-123"
    /output.[xml|json:format]? //Matches "/output", "output.xml", "output.json"
    /[:controller]?/[:action]? //Matches the typical /controller/action format

**Note** - *all* routes that match the request URI are called - this
allows you to incorporate complex conditional logic such as user
authentication or view layouts. e.g. as a basic example, the following
code will wrap other routes with a header and footer

    respond('*', function ($request, $response) { $response->render('header.phtml'; });
    //other routes
    respond('*', function ($request, $response) { $response->render('footer.phtml'; });

Routes automatically match the entire request URI. If you need to match
only a part of the request URI or use a custom regular expression, use the `@` operator. If you need to
negate a route, use the `!` operator

    //Match all requests that end with '.json' or '.csv'
    respond('@\.(json|csv)$', ...

    //Match all requests that _don't_ start with /admin
    respond('!@^/admin/', ...

## Views

You can send properties or helpers to the view by assigning them
to the `$response` object, or by using the second arg of `$response->render()`

    $response->escape = function ($str) {
        return htmlentities($str);
    };

    $response->render('myview.phtml', array('title' => 'My View'));

*myview.phtml*

    <title><?php echo $this->escape($this->title) ?></title>

Views are compiled and run in the scope of `$response` so all response methods can be accessed with `$this`

    $this->render('partial.html')           //Render partials
    $this->param('myvar')                   //Access request parameters
    echo $this->query(array('page' => 2))   //Modify the current query string

## API

    $request->
        header($key)                        //Gets a request header
        cookie($key)                        //Gets a cookie from the request
        session($key)                       //Gets a session variable
        param($key, $default = null)        //Gets a request parameter (get, post, named)
        params()                            //Return all parameters
        params($mask = null)                //Return all parameters that match the mask array - extract() friendly
        validate($param, $err_msg = null)   //Starts a validator chain
        method()                            //Gets the request method
        method($method)                     //Checks if the request method is $method, i.e. method('post') => true
        secure()                            //https?
        secure(true)                        //Redirect non-secure requests to the secure version
        id()                                //Gets a unique ID for the request
        ip()                                //Get the request IP
        userAgent()                         //Get the request user agent
        uri()                               //Get the request URI

    $response->
        header($key, $value = null)                     //Sets a response header
        cookie($key, $value = null, $expiry = null)     //Sets a cookie
        cookie($key, null)                              //Removes a cookie
        flash($msg, $type = 'error')                    //Sets a flash message
        send($object, $type = 'json', $filename = null) //$type can be 'csv', 'json', or 'file'
        code($code)                                     //Sends an HTTP response code
        redirect($url, $code = 302)                     //Redirect to the specified URL
        refresh()                                       //Redirect to the current URL
        back()                                          //Redirect to the referer
        render($view, $data = array())                  //Renders a view or partial
        onError($callback)                              //$callback takes ($response, $msg, $err_type = null)
        set($key, $value = null)                        //Set a view property or helper
        set($arr)
        escape($str)                                    //Escapes a string
        query($key, $value = null)                      //Modify the current query string
        query($arr)
        param($param, $default = null)                  //Gets an escaped request parameter
        getFlashes($type = 'error')                     //Retrieves and clears all flashes of $type
        flush()                                         //Flush all open output buffers
        discard()                                       //Discard all open output buffers
        <callback>($arg1, ...)                          //Calls a user-defined helper
        <property>                                      //Gets a user-defined property

    $validator->
        notNull()                           //The string must not be null
        isLen($length)                      //The string must be the exact length
        isLen($min, $max)                   //The string must be between $min and $max length (inclusive)
        isInt()                             //Checks for a valid integer
        isFloat()                           //Checks for a valid float/decimal
        isEmail()                           //Checks for a valid email
        isUrl()                             //Checks for a valid URL
        isIp()                              //Checks for a valid IP
        isAlpha()                           //Checks for a-z (case insensitive)
        isAlnum()                           //Checks for alphanumeric characters
        contains($needle)                   //Checks if the string contains $needle
        isChars($chars)                     //Validates against a character list
        isRegex($pattern, $modifiers = '')  //Validates against a regular expression
        notRegex($pattern, $modifiers ='')
        is<Validator>()                     //Validate against a custom validator
        not<Validator>()                    //The validator can't match
        <Validator>()                       //Alias for is<Validator>()

## License

(MIT License)

Copyright (c) 2010 Chris O'Hara <cohara87@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
