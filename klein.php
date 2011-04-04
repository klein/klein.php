<?php # github.com/chriso/klein.php © (MIT) Chris O'Hara <cohara87@gmail.com>

$__routes = array();
$__params = $_REQUEST;

//REST aliases
function get($route, Closure $callback, $cache = false) {
    return respond('GET', $route, $callback, $cache);
}
function post($route, Closure $callback, $cache = false) {
    return respond('POST', $route, $callback, $cache);
}
function put($route, Closure $callback, $cache = false) {
    return respond('PUT', $route, $callback, $cache);
}
function delete($route, Closure $callback, $cache = false) {
    return respond('DELETE', $route, $callback, $cache);
}
function options($route, Closure $callback, $cache = false) {
    return respond('OPTIONS', $route, $callback, $cache);
}
function request($route, Closure $callback, $cache = false) {
    return respond(null, $route, $callback, $cache); //Match all
}

//Binds a callback to the specified method/route
function respond($method, $route, Closure $callback, $cache = false) {
    global $__routes;
    $negate = false;
    //Routes that start with ! are negative matches
    if ($route[0] === '!') {
        $negate = true;
        $route = substr($route, 1);
    }
    if ($route !== '*' && $route[0] !== '@') {
        //Find the longest substring in the route that doesn't use regex
        for ($i = 0, $l = strlen($route); $i < $l; $i++) {
            if ($route[$i] === '[' || $route[$i] === '(' || $route[$i] === '.') {
                $substr = substr($route, 0, $i);
                break;
            } elseif ($route[$i] === '?' || $route[$i] === '+' || $route[$i] === '*' || $route[$i] === '{') {
                $substr = substr($route, 0, $i - 1);
                break;
            }
        }
        //No regular expression chars found?
        if ($i === $l) {
            $substr = $route;
        }
    }
    //Add the route and return the callback
    $__routes[] = array($method, $route, $negate, $substr, $cache, $callback);
    return $callback;
}

//Use APC internally (if it's available)
if (function_exists('apc_store')) {
    function cache_set($key, $value, $ttl = 0) {
        return apc_store("klein.$key", $value, $ttl);
    }
    function cache_get($key) {
        return apc_fetch("klein.$key");
    }
} else {
    function cache_set() {
        return false;
    }
    function cache_get() {
        return false;
    }
}

//Compiles a route string to a regular expression. This is expensive, so avoid it where possible
function compile_route($route) {
    if (false !== ($regex = cache_get($route))) {
        return $regex;
    }
    $regex = $route;
    if ($route[0] === '@') {
        //The @ operator is used to match any part of the request uri (or use custom regex)
        return "`" . substr($route, 1) . "`";
    }
    if (preg_match_all('`(/?\.?)\[([^:]*+)(?::([^:\]]++))?\](\?)?`', $route, $matches, PREG_SET_ORDER)) {
        $match_types = array(
            'i'  => '[0-9]++',
            'a'  => '[0-9A-Za-z]++',
            'h'  => '[0-9A-Fa-f]++',
            '*'  => '.+?',
            '**' => '.++', //possessive
            ''   => '[^/]++'
        );
        foreach ($matches as $match) {
            list($block, $pre, $type, $param, $optional) = $match;
            if (isset($match_types[$type])) {
                $type = $match_types[$type];
            }
            $pattern = '(?:' . ($pre !== '' && strpos($regex, $block) !== 0 ? $pre : null)
                     . '(' . ($param !== '' ? "?<$param>" : null) . $type . '))'
                     . ($optional !== null ? '?' : null);

            $regex = str_replace($block, $pattern, $regex);
        }
        $regex = "`^/$regex/?$`";
    }
    //Cache the regex string
    cache_set($route, $regex);
    return $regex;
}

//Dispatch the request to the approriate routes (and auto call on shutdown)
function dispatch($request_uri = null) {
    global $__routes;
    global $__params;

    if (empty($__routes)) return;

    //Get the request method and URI
    if (null === $request_uri) {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    }
    $request_method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';

    //Pass three parameters to each callback, $request, $response, and an
    //instance of StdClass for sharing scope
    $request = new _Request;
    $response = new _Response;
    $holder = new StdClass;
    $matched = false;

    //Start the global output buffer
    ob_start();

    foreach ($__routes as $route => $handler) {
        list($method, $route, $negate, $substr, $cache_ttl, $callback) = $handler;

        //Check the method and then the non-regex substr against the request uri
        if (null !== $method && $request_method !== $method) continue;
        if ($substr && strpos($request_uri, $substr) !== 0) continue;

        //Match the route to the request uri
        $match = $route === '*' || $substr === $request_uri || preg_match(compile_route($route), $request_uri, $params);

        if ($match ^ $negate) {
            $matched = true;
            if ($params) {
                //Merge named parameters
                $__params = array_merge($__params, $params);
            }
            $cache_key = "$route _output";
            if ($cache_ttl && false !== ($output = cache_get($cache_key))) {
                //Output if we have a cache hit
                echo $output;
            } else {
                ob_start();
                try {
                    $callback($request, $response, $holder);
                } catch (Exception $e) {
                    //Catch exceptions and send them to the response error handler
                    $response->error($e);
                }
                //Cache the callback output?
                if ($cache_ttl) {
                    cache_set($cache_key, ob_get_flush(), $cache_ttl === true ? 0 : $cache_ttl);
                } else {
                    @ ob_end_flush();
                }
            }
        }
    }
    if (!$matched) {
        $response->code(404);
    }
    $response->flush();
    $__routes = array(); //Only dispatch once
}
register_shutdown_function('dispatch');

class _Request {

    //Returns all parameters (GET, POST, named) that match the mask array
    public function params($mask = null) {
        global $__params;
        if (null !== $mask) {
            if (!is_array($mask)) {
                $mask = func_get_args();
            }
            $mask = array_flip($mask);
            $params = array_intersect_key($__params, $mask);
            $params = array_merge($mask, $params); //Ensure each key is present
        }
        return $params;
    }

    //Return a request parameter, or $default if it doesn't exist
    public function param($param, $default = null) {
        global $__params;
        return isset($__params[$param]) ? trim($__params[$param]) : $default;
    }

    //Is the request secure? If $required then redirect to the secure version of the URL
    public function secure($required = false) {
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'];
        if (!$secure && $required) {
            $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('Location: ' . $url);
        }
        return $secure;
    }

    //Gets a request header
    public function header($key) {
        $key = 'HTTP_' . strtoupper(str_replace('-','_', $key));
        return isset($_SERVER[$key]) ? $_SERVER[$key] : null;
    }

    //Gets a request cookie
    public function cookie($cookie) {
        return isset($_COOKIE[$cookie]) ? $_COOKIE[$cookie] : null;
    }

    //Gets the request method, or checks it against $is - e.g. method('post') => true
    public function method($is = null) {
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if (null !== $is) {
            return strcasecmp($method, $is) === 0;
        }
        return $method;
    }

    //Start a validator chain for the specified parameter
    public function validate($param, $err = null) {
        return new _Validator($this->param($param), $err);
    }

    //Gets a unique ID for the request
    public function id($entropy = null) {
        return sha1(mt_rand() . microtime(true) . mt_rand() . $entropy);
    }

    //Gets a session variable associated with the request
    public function session($key, $default = null) {
        @ session_start();
        return isset($_SESSION[$key]) ? $key : $default;
    }

    //Gets the request IP address
    public function ip() {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
    }

    //Gets the request user agent
    public function userAgent() {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : false;
    }
}

class _Response {

    protected $_errorCallbacks = array();
    protected $_data = array();

    //Sets a response header
    public function header($header, $key = '') {
        $key = str_replace(' ', '-', ucwords(str_replace('-', ' ', $key)));
        header("$key: $value");
    }

    //Sets a response cookie
    public function cookie($key, $value = '', $expiry = null, $path = '/', $domain = null, $secure = false, $httponly = false) {
        if (null === $expiry) {
            $expiry = time() + (3600 * 24 * 30);
        }
        return setcookie($key, $value, $expiry, $path, $domain, $secure, $httponly);
    }

    //Stores a flash message of $type
    public function flash($msg, $type = 'error') {
        @ session_start();
        if (!isset($_SESSION['__flash_' . $type])) {
            $_SESSION['__flash_' . $type] = array();
        }
        $_SESSION['__flash_' . $type][] = $msg;
    }

    //Sends an object or file
    public function send($object, $type = 'json', $filename = null) {
        $this->discard();
        set_time_limit(600);
        header('Cache-Control: no-store, no-cache');
        switch ($type) {
        case 'json':
            $json = json_encode($object);
            header('Content-Type: text/javascript; charset=utf8');
            echo $json;
            exit;
        case 'csv':
            header('Content-type: text/csv; charset=utf8');
            if (null === $filename) {
                $filename = 'output.csv';
            }
            header("Content-Disposition: attachment; filename='$filename'");
            $columns = false;
            $escape = function ($value) { return str_replace('"', '\"', $value); };
            foreach ($object as $row) {
                $row = (array)$row;
                if (!$columns && !isset($row[0])) {
                    echo '"' . implode('","', array_keys($row)) . '"' . "\n";
                    $columns = true;
                }
                echo '"' . implode('","', array_map($escape, array_values($row))) . '"' . "\n";
            }
            exit;
        case 'file':
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            header('Content-type: ' . finfo_file($finfo, $object));
            header('Content-length: ' . filesize($object));
            if (null === $filename) {
                $filename = basename($object);
            }
            header("Content-Disposition: attachment; filename='$filename'");
            fpassthru($object);
            finfo_close($finfo);
            exit;
        }
    }

    //Sends a HTTP response code
    public function code($code) {
        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
        header("$protocol $code");
    }

    //Redirects the request to another URL
    public function redirect($url, $code = 302) {
        $this->code($code);
        header("Location: $url");
        exit;
    }

    //Redirects the request to the current URL
    public function refresh() {
        $redirect = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']) {
            $redirect .= '?' . $_SERVER['QUERY_STRING'];
        }
        $this->redirect($redirect);
    }

    //Redirects the request back to the referrer
    public function back() {
        if (isset($_SERVER['HTTP_REFERER'])) {
            $this->redirect($_SERVER['HTTP_REFERER']);
        }
        $this->refresh();
    }

    //Sets view data
    public function data($data, $value = null) {
        if (!is_array($data)) {
            return $this->_data[$data] = $value;
        }
        return $this->_data += $data;
    }

    //Adds to / modifies the current query string
    public function query($new, $value = null) {
        $query = isset($_SERVER['QUERY_STRING']) ? parse_str($_SERVER['QUERY_STRING']) : array();
        if (is_array($new)) {
            $query += $new;
        } else {
            $query[$new] = $value;
        }
        $url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        if (empty($query)) return $url;
        return $url . '?' . http_build_query($query);
    }

    //Renders a view
    public function render($view, $data = array(), $compile = false) {
        $view = new _View($view, $this->_data + $data, $compile);
    }

    //Sets a session variable
    public function session($key, $value = null) {
        @ session_start();
        return $_SESSION[$key] = $value;
    }

    //Adds an error callback to the stack of error handlers
    public function onError($callback) {
        $this->_errorCallbacks[] = $callback;
    }

    //Routes an exception through the error callbacks
    public function error(Exception $err) {
        $type = get_class($err);
        $msg = $err->getMessage();

        if (count($this->_errorCallbacks) > 0) {
            foreach (array_reverse($this->_errorCallbacks) as $callback) {
                if (is_callable($callback)) {
                    if($callback($msg, $type)) {
                        return;
                    }
                } else {
                    $this->flash($err);
                    $this->redirect($callback);
                }
            }
        } else {
            $this->code(500);
            trigger_error($err, E_USER_ERROR);
        }
    }

    //Discards the current output buffer(s)
    public function discard() {
        while(@ ob_end_clean());
    }

    //Flushes the current output buffer(s)
    public function flush() {
        while(@ ob_end_flush());
    }
}

class ViewException extends Exception {}

class _View {

    protected $_compiled = false;
    protected $_data = array();

    //Renders a view
    public function __construct($view, $data = array(), $compile = false) {
        if (!file_exists($view) || !is_readable($view)) {
            throw new ViewException("Cannot render $view");
        }
        if ($this->_compiled = $compile) {
            //$compile enables basic micro-templating tags
            $contents = file_get_contents($view);
            $tags = array(
                '{{=' => '<?php echo ',
                '{{'  => '<?php ',
                '}}'  => '?'.'>'
            );
            $compiled = str_replace(array_keys($tags), array_values($tags), $contents, $replaced);
            if ($replaced) {
                $view = tempnam(sys_get_temp_dir(), mt_rand());
                file_put_contents($view, $compiled);
            }
        }
        $this->_data = $data;
        require $view;
    }

    //Renders a partial view
    public function partial($view, $data = array(), $compile = false) {
        $partial = new self($view, (array)$this->_data + $data, $this->_compiled || $compile);
    }

    //Returns an escaped request paramater
    public function param($param, $default = null) {
        global $__params;
        if (!isset($__params[$param])) {
            return null;
        }
        return htmlentities($__params[$param], ENT_QUOTES, 'UTF-8');
    }

    //Returns (and clears) all flashes of $type
    public function flash($type = 'error') {
        @ session_start();
        if (isset($_SESSION['__flash_' . $type])) {
            $flashes = $_SESSION['__flash_' . $type];
            foreach ($flashes as $k => $flash) {
                $flashes[$k] = htmlentities($flash, ENT_QUOTES, 'UTF-8');
            }
            unset($_SESSION['__flash_' . $type]);
            return $flashes;
        }
        return array();
    }

    //Gets a view property
    public function __get($property) {
        if (!isset($this->_data[$property])) {
            return null;
        }
        return htmlentities($this->_data[$property], ENT_QUOTES, 'UTF-8');
    }

    //Calls a view helper
    public function __call($method, $args) {
        if (!isset($this->_data[$method]) || !is_callable($this->_data[$method])) {
            throw new ViewException("Call to undefined view helper $method()");
        }
        $helper = $this->_data[$method];
        switch (count($args)) {
            case 0: return $helper();
            case 1: return $helper($args[0]);
            case 2: return $helper($args[0], $args[1]);
            case 3: return $helper($args[0], $args[1], $args[2]);
            default: return call_user_func_array($helper, $args);
        }
    }
}

class ValidatorException extends Exception {}

class _Validator {

    public static $methods = array();

    protected $_str = null;
    protected $_err = null;

    //Sets up the validator chain with the string and optional error message
    public function __construct($str, $err = null) {
        $this->_str = $str;
        $this->_err = $err;
    }

    //Calls a validator, is<Validator>() or not<Validator>()
    public function __call($method, $args) {
        switch (substr($method, 0, 2)) {
        case 'is':
            $validator = substr($method, 2);
            break;
        case 'no': //not
            $reverse = true;
            $validator = substr($method, 3);
            break;
        default:
            $validator = $method;
            break;
        }
        $validator = strtolower($validator);

        if (!$validator || !isset(static::$methods[$validator])) {
            throw new ValidatorException("Unknown method $method()");
        }
        $validator = static::$methods[$validator];
        array_unshift($args, $this->_str);

        //Try and avoid using the slow call_user_func_array
        switch (count($args)) {
            case 1:  $result = $validator($args[0]); break;
            case 2:  $result = $validator($args[0], $args[1]); break;
            case 3:  $result = $validator($args[0], $args[1], $args[2]); break;
            case 4:  $result = $validator($args[0], $args[1], $args[2], $args[3]); break;
            default: $result = call_user_func_array($validator, $args); break;
        }

        //Throw an exception on failed validation
        if (false === (bool)$result ^ (bool)$reverse) {
            throw new ValidatorException($this->_err);
        }
        return $this;
    }
}

//Add a new validator
function addValidator($method, Closure $callback) {
    _Validator::$methods[strtolower($method)] = $callback;
}

//$str must be not null
addValidator('null', function ($str) {
    return $str === null || $str === '';
});

//$str must be either equal to $min, or between $min and $max
addValidator('len', function ($str, $min, $max = null) {
    $len = strlen($str);
    return null === $max ? $len === $min : $len >= $min && $len <= $max;
});

//$str must be an integer
addValidator('int', function ($str) {
    return (string)$str === ((string)(int)$str);
});

//$str must be a float
addValidator('float', function ($str) {
    return (string)$str === ((string)(float)$str);
});

//$str must be a valid email
addValidator('email', function ($str) {
    return filter_var($str, FILTER_VALIDATE_EMAIL);
});

//$str must be a valid URL
addValidator('url', function ($str) {
    return filter_var($str, FILTER_VALIDATE_URL);
});

//$str must be a valid IP
addValidator('ip', function ($str) {
    return filter_var($str, FILTER_VALIDATE_IP);
});

//$str must be alphanumeric
addValidator('alnum', function ($str) {
    return ctype_alnum($str);
});

//$str must be a-z
addValidator('alpha', function ($str) {
    return ctype_alpha($str);
});

//$str must contain $needle
addValidator('contains', function ($str, $needle) {
    return strpos($str, $needle) !== false;
});

//$str must match $pattern
addValidator('regex', function ($str, $pattern) {
    return preg_match($pattern, $str);
});

//$str must be made up of $chars
addValidator('chars', function ($str, $chars) {
    return preg_match("`^[$chars]++$`i", $str);
});
