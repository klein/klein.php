**klein.php** is a full-featured micro router for PHP5.3+ 

In ~600 lines you get:

* Sinatra-like routing w/ auto dispatch
* Views, view helpers, caching and partials + optional template tags
* Helpers for working with sessions, cookies, redirects, response codes, etc. etc.
* As much speed as you can possibly squeeze from PHP ([2000 requests/second +](https://gist.github.com/878833))

## Example

Setup some form of front controller [URL rewriting](https://gist.github.com/874000), add `<?php require 'klein.php';` and you're ready.

*Example 1* - That's it?

    get('/', function () {
        echo 'Hello World!';
    });

*Example 2* - Named parameters

    get('/[:name]?', function ($request, $response) {
        $name = $request->param('name', 'world');
        echo "Hello $name!";
    });

*Example 3* - [So RESTful](http://bit.ly/g93B1s)

       get('/posts',       function ($request, $response) {/* ... */});
      post('/post/create', function ($request, $response) {/* ... */});
       put('/post/[i:id]', function ($request, $response) {/* ... */});
    delete('/post/[i:id]', function ($request, $response) {/* ... */});

*Example 4* - Parameter validation

    put('/users/[i:id]/edit', function ($request, $response) {
        $request->onError(function ($msg) use ($response) {
            $response->flash($err);
            $response->back(); //Redir to the referer
        });
        $request->validate('username', 'Please enter a valid username')->isLen(5, 64)->isChars('a-zA-Z0-9-');
    });

## API

    $request->
        header($key)                    //Gets a request header
        cookie($key)                    //Gets a cookie from the request
        session($key)                   //Gets a session variable
        param($key)                     //Gets a request parameter (get, post, named)
        params()                        //Return all parameters
        params($mask = null)            //Return all parameters that match the mask array
        validate($param, $err = null)   //Starts a validator chain
        method()                        //Gets the request method
        method($method)                 //Checks if the request method is $method, i.e. method('post') => true
        secure()                        //https?
        secure(true)                    //Redirect non-secure requests to the secure version
        id($entropy = null)             //Gets a unique ID for the request
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
        onError($callback)                                          //$callback takes ($err_msg, $exception_type = null)
        data($key, $value = null)                                   //Add data to the view
        data($arr)                                                  //Add an array of data to the view
        flush()                                                     //Flush all open output buffers
        discard()                                                   //Discard all open output buffers

    $validator->
        isLen($length)
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
        is<Validator>()                     //Validate against a custom validator
        not<Validator>()                    //The validator can't match
        <Validator>()                       //Alias for is<Validator>()

    $view->
        partial($view, $data = array(), $compile = false)   //Render a partial view
        flash($type = 'error')                              //Retrieves and clears all flash messages of $type
        param($param, $default = null)                      //Gets an escaped request parameter
        query($key, $value = null)                          //Modify the current query string
        query($arr)
        <helper>($args, ...)                                //Calls the specified view helper
        <property>                                          //Gets a variable from the response data

    Misc:
        request($route, $cache = null, $callback = null)    //Match all request methods
        get($route, $cache = null, $callback = null)
        put($route, $cache = null, $callback = null)
        post($route, $cache = null, $callback = null)
        delete($route, $cache = null, $callback = null)
        options($route, $cache = null, $callback = null)

## Validators

To add a custom validator use `addValidator($method, $callback)`

    addValidator('hex', function ($str) {
        return preg_match('/^[0-9a-f]++$/i', $str);
    });

Then you can validate parameters using `is<$method>()` or `not<$method>()`, e.g.

    $request->validate('key')->isHex();

Validation methods are chainable, and a custom exception message can be specified for if validation fails

    $request->validate('key', 'The key was invalid')->isHex()->isLen(32);

## Views

You can send data or helper methods to the view using `$response->data()` or the second param of `$response->render()` call

    $response->data('escape', function ($str) {
        return htmlentities($str);
    });

    $response->render('myview.phtml', array('title' => 'My View'));

*myview.phtml*

    <title><?php echo $this->escape($this->title) ?></title>

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

    /posts/[*?:title][i:id]    //Matches "/posts/this-is-a-title-123"
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
only a part of the request URI, use the `@` operator. If you need to
negate a route, use the `!` operator

    //Match all requests containing 'json' or 'csv'
    get('@json|csv', ...

    //Match all requests that _don't_ start with /admin
    get('!/admin/[*]', ...

    //Match all requests that do not end in .xml
    get('!@\.xml$', ...

## Sharing scope between routes

Each callback receives a third parameter - an instance of `StdClass` -
that can be used to share scope

    get('*', function ($request, $response, $app) {
        $app->db = new Pdo(/* pdo config */);
    });

    get('/login', function ($request, $response, $app) {
        $app->db->query("SELECT * FROM .."); //etc.
    }

## Micro-templates

Set the third param of `render()` or `partial()` to true to use the optional template tags

    $people = array('Chris','Jeff','Carla');
    $response->render('myview.tpl', array('people'=>$people), true);

*myview.tpl*

    <ul id="people">
    {{foreach($people as $person):}}
        <li>Hi, my name is {{=$person->name}}</li>
    {{endforeach}}
    </ul>

## License

(MIT License)

Copyright (c) 2010 Chris O'Hara <cohara87@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
