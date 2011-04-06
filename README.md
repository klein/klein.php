**klein.php** is a full-featured micro router for PHP 5.3+. In < 600 lines you get

* Sinatra-like routing and a small but powerful set of of methods for rapidly building web apps
* *Very* low overhead => as much speed as you can possibly squeeze from PHP ([2000 requests/second+](https://gist.github.com/878833) on a 2yr old laptop).

## Getting started

1. PHP 5.3+ is required.
2. Setup some form of [front controller URL rewriting](https://gist.github.com/874000)
3. Add `<?php require 'klein.php';` as your first line and `dispatch();` as your last
4. (Optional) Throw in some [APC](http://pecl.php.net/package/APC) for good measure

## Example

*Example 1*

    get('*', function ($request, $response) {
        echo 'Hello World!';
    });

*Example 2* - Named parameters

    get('/[:name]?', function ($request, $response) {
        $name = $request->param('name', 'world');
        echo "Hello $name!";
    });

*Example 3* - [So RESTful](http://bit.ly/g93B1s)

    get('/posts', $callback);
    post('/post/create', $callback);
    put('/post/[i:id]', $callback);
    delete('/post/[i:id]', $callback);

    //..or to match all requests
    request('*', $callback);

*Example 4* - All together

    request('*', function ($reguest, $response, $app) {
        //By default, on error/exception, flash the message and redirect to the referrer
        $response->onError(function ($response, $err_msg) {
            $response->flash($err_msg);
            $response->back();
        });

        //The third parameter can be used to share scope
        $app->db = new PDO(/* .. */);
    });

    post('/users/[i:id]/edit', function ($request, $response) {
        //Quickly validate input parameters
        $request->validate('username', 'Please enter a valid username')->isLen(5, 64)->isChars('a-zA-Z0-9-');
        $request->validate('password')->notNull();

        $app->db->query(/*..*/);

        //Add view properties and helpers
        $response->title = 'foo';
        $response->escape = function ($str) {
            return htmlentities($str); //Assign view helpers
        };

        $response->render('myview.phtml');
    });

    //myview.phtml:
    <title><?php echo $this->escape($this->title) ?></title>

## API

    $request->
        header($key)                    //Gets a request header
        cookie($key)                    //Gets a cookie from the request
        session($key)                   //Gets a session variable
        param($key, $default = null)    //Gets a request parameter (get, post, named)
        params()                        //Return all parameters
        params($mask = null)            //Return all parameters that match the mask array
        validate($param, $err = null)   //Starts a validator chain
        method()                        //Gets the request method
        method($method)                 //Checks if the request method is $method, i.e. method('post') => true
        secure()                        //https?
        secure(true)                    //Redirect non-secure requests to the secure version
        id()                            //Gets a unique ID for the request
        ip()
        userAgent()

    $response->
        header($key, $value = null)                                 //Sets a response header
        cookie($key, $value = null, $expiry = null, $path = '/')    //Sets a cookie
        cookie($key, '')                                            //Removes a cookie
        flash($msg, $type = 'error')                                //Sets a flash message
        send($object, $type = 'json', $filename = null)             //$type can be 'csv', 'json', or 'file'
        code($code)                                                 //Sends an HTTP response code
        redirect($url, $code = 302)                                 //Redirect to the specified URL
        refresh()                                                   //Redirect to the current URL
        back()                                                      //Redirect to the referer
        render($view, $data = array(), $compile = false)            //Renders a view, set $compile to use micro tags
        onError($callback)                                          //$callback takes ($response, $msg, $err_type = null)
        set($key, $value = null)                                    //Set a view property or helper
        set($arr)
        escape($str)                                                //Escapes a string
        query($key, $value = null)                                  //Modify the current query string
        query($arr)
        param($param, $default = null)                              //Gets an escaped request parameter
        getFlashes($type = 'error')                                 //Retrieves and clears all flashes of $type
        flush()                                                     //Flush all open output buffers
        discard()                                                   //Discard all open output buffers
        <callback>($arg1, ...)                                      //Calls a user-defined helper
        <property>                                                  //Gets a user-defined property

    $validator->
        isLen($length)                      //The string must be the exact length
        isLen($min, $max)                   //The string must be between $min and $max length (inclusive)
        notNull()                           //The string must not be null
        isInt()                             //Checks for a valid integer
        isFloat()                           //Checks for a valid float/decimal
        isEmail()                           //Checks for a valid email
        isUrl()                             //Checks for a valid URL
        isIp()                              //Checks for a valid IP
        isAlpha()                           //Checks for a-z
        isAlnum()                           //Checks for alphanumeric characters
        contains($needle)                   //Checks if the string contains $needle
        isChars($chars)                     //Validates against a character list
        isRegex($pattern, $modifiers = '')  //Validates against a regular expression
        notRegex($pattern, $modifiers ='')
        is<Validator>()                     //Validate against a custom validator
        not<Validator>()                    //The validator can't match
        <Validator>()                       //Alias for is<Validator>()

    Misc:
        request($route, $callback)  //Respond to any request method
            get($route, $callback)  //Respond to GET requests
            put($route, $callback)  //Respond to PUT requests
           post($route, $callback)  //Respond to POST requests
         delete($route, $callback)  //Response to DELETE requests
        options($route, $callback)  //Respond to OPTIONS requests
        request('*',    $callback)  //Respond to *all* requests

## Views

You can send properties or helper methods to the view with the second param of `$response->render()` call, or just by assigning a property to the $response object

    $response->escape = function ($str) {
        return htmlentities($str);
    };

    $response->render('myview.phtml', array('title' => 'My View'));

*myview.phtml*

    <title><?php echo $this->escape($this->title) ?></title>

Views are compiled and run in the scope of `$response`

    $this->render('partial.html')  //Render partials
    $this->param('myvar')          //Access request parameters

Set the third param of `render()` to `true` to use the optional template tags

    $people = array('Chris','Jeff','Carla');
    $response->render('myview.tpl', array('people' => $people), true);

*myview.tpl*

    <ul id="people">
    {{foreach($people as $person):}}
        <li>Hi, my name is {{=$person->name}}</li>
    {{endforeach}}
    </ul>

## Validators

To add a custom validator use `addValidator($method, $callback)`

    addValidator('hex', function ($str) {
        return preg_match('/^[0-9a-f]++$/i', $str);
    });

Then you can validate parameters using `is<$method>()` or `not<$method>()`, e.g.

    $request->validate('key')->isHex();

Validation methods are chainable, and a custom exception message can be specified for if validation fails

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

    get('*', function ($request, $response) { $response->render('header.phtml'; });
    //other routes
    get('*', function ($request, $response) { $response->render('footer.phtml'; });

Routes automatically match the entire request URI. If you need to match
only a part of the request URI or use a custom regular expression, use the `@` operator. If you need to
negate a route, use the `!` operator

    //Match all requests containing 'json' or 'csv'
    get('@json|csv', ...

    //Match all requests that _don't_ start with /admin
    get('!@^/admin/', ...

## License

(MIT License)

Copyright (c) 2010 Chris O'Hara <cohara87@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
