<?php
# © Chris O'Hara <cohara87@gmail.com> (MIT License)
# http://github.com/chriso/klein.php

$__routes = array();
$__params = $_REQUEST;

//Define a route handler
function respond($methods, $route, Closure $callback = null) {
    global $__routes;
    foreach ((array)$methods as $method) { //Allow an array of methods
        $__routes[] = array($method, $route, $callback);
    }
    return $callback;
}

//Dispatch the request to the approriate route(s)
function dispatch($request_uri = null, $request_method = null, array $params = null, $capture = false) {
    global $__routes;
    global $__params;

    //Pass three parameters to each callback, $request, $response, and a blank object for sharing scope
    $request  = new _Request;
    $response = new _Response;
    $app      = new StdClass;

    //Get the request method and URI
    if (null === $request_uri) {
        if (isset($_SERVER['REQUEST_URI'])) {
            $request_uri = $_SERVER['REQUEST_URI'];
            if (strpos($request_uri, '?') !== false) {
                $request_uri = strstr($request_uri, '?', true);
            }
        } else {
            $request_uri = '/';
        }
    }
    if (null === $request_method) {
        $request_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
    }
    if (null !== $params) {
        $__params = array_merge($__params, $params);
    }

    $matched = false;
    $apc = function_exists('apc_fetch');

    ob_start();

    foreach ($__routes as $handler) {
        list($method, $_route, $callback) = $handler;

        //Was a method specified? If so, check it against the current request method
        if (is_callable($_route)) {
            $callback = $_route;
            $_route = $method;
        } elseif (strcasecmp($request_method, $method) !== 0) {
            continue;
        }

        //! is a negative match
        if ($_route[0] === '!') {
            $negate = true;
            $i = 1;
        } else {
            $i = 0;
        }

        if ($_route === $request_uri || $_route === '*') {
            $match = true;
        } else {

            //@ is used for custom regex
            if ($_route[0] !== '@') {

                //Compiling and matching regular expressions is (relatively)
                //expensive, so try and match by a substring first
                $route = $substr = null;
                while (true) {
                    if ($_route[$i] === '') {
                        break;
                    } elseif (null === $substr) {
                        $c = $_route[$i];
                        $n = $_route[$i + 1];
                        if ($c === '[' || $c === '(' || $c === '.' || $n === '?' || $n === '+' || $n === '*' || $n === '{') {
                            $substr = $route;
                        }
                    }
                    $route .= $_route[$i++];
                }
                if (null === $substr || strpos($request_uri, $substr) !== 0) {
                    continue;
                }

            } else {
                $route = $_route;
            }

            //Check if there's a cached regex string
            if (false !== $apc) {
                $regex = apc_fetch("route:$route");
                if (false === $regex) {
                    $regex = compile_route($route);
                    apc_store("route:$route", $regex);
                }
            } else {
                $regex = compile_route($route);
            }
            $match = preg_match($regex, $request_uri, $params);
        }

        //Handle a match
        if ($match ^ $negate) {
            $matched = true;
            //Merge named parameters
            if (null !== $params) {
                $__params = array_merge($__params, $params);
            }
            try {
                //Ignore callbacks that return false
                if (false === $callback($request, $response, $app)) {
                    $matched = false;
                }
            } catch (Exception $e) {
                $response->error($e);
            }
        }
    }
    if (false === $matched) {
        $response->code(404);
    }
    return false !== $capture ? ob_get_contents() : ob_end_flush();
}

//Compiles a route string to a regular expression
function compile_route($route) {
    if ($route[0] === '@') {
        return '`' . substr($route, 1) . '`';
    }
    $regex = $route;
    if (preg_match_all('`(/?\.?)\[([^:]*+)(?::([^:\]]++))?\](\?)?`', $route, $matches, PREG_SET_ORDER)) {
        $match_types = array(
            'i'  => '[0-9]++',
            'a'  => '[0-9A-Za-z]++',
            'h'  => '[0-9A-Fa-f]++',
            '*'  => '.+?',
            '**' => '.++',
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
        $regex = "/$regex/?";
    }
    return "`^$regex$`";
}

class _Request {

    //Returns all parameters (GET, POST, named) that match the mask
    public function params($mask = null) {
        global $__params;
        $params = $__params;
        if (null !== $mask) {
            if (!is_array($mask)) {
                $mask = func_get_args();
            }
            $params = array_intersect_key($params, array_flip($mask));
            foreach ($mask as $key) {
                if (!isset($params[$key])) $params[$key] = null;
            }
        }
        return $params;
    }

    //Return a request parameter, or $default if it doesn't exist
    public function param($param, $default = null) {
        global $__params;
        return isset($__params[$param]) ? $__params[$param] : $default;
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
    public function id() {
        return sha1(mt_rand() . microtime(true) . mt_rand());
    }

    //Gets a session variable associated with the request
    public function session($key, $default = null) {
        @ session_start();
        return isset($_SESSION[$key]) ? $key : $default;
    }

    //Gets the request IP address
    public function ip() {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }

    //Gets the request user agent
    public function userAgent() {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    }

    //Gets the request URI
    public function uri() {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        if (strpos($request_uri, '?') !== false) {
            $request_uri = strstr($request_uri, '?', true);
        }
        return $request_uri;
    }
}

class _Response extends StdClass {

    protected $_errorCallbacks = array();

    //Sets a response header
    public function header($key, $value = '') {
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
        set_time_limit(1200);
        header("Pragma: no-cache");
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
            header('Content-Disposition: attachment; filename="'.$filename.'"');
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
            header('Content-Disposition: attachment; filename="'.$filename.'"');
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

    //Sets response properties/helpers
    public function set($key, $value = null) {
        if (!is_array($key)) {
            return $this->$key = $value;
        }
        foreach ($key as $k => $value) {
            $this->$k = $value;
        }
    }

    //Adds to / modifies the current query string
    public function query($new, $value = null) {
        $query = array();
        if (isset($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $query);
        }
        if (is_array($new)) {
            $query = array_merge($query, $new);
        } else {
            $query[$new] = $value;
        }
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        if (strpos($request_uri, '?') !== false) {
            $request_uri = strstr($request_uri, '?', true);
        }
        return $request_uri . (!empty($query) ? '?' . http_build_query($query) : null);
    }

    //Renders a view
    public function render($view, array $data = array()) {
        if (!file_exists($view) || !is_readable($view)) {
            throw new ErrorException("Cannot render $view");
        }
        if (!empty($data)) {
            $this->set($data);
        }
        require $view;
    }

    //Sets a session variable
    public function session($key, $value = null) {
        @ session_start();
        return $_SESSION[$key] = $value;
    }

    //Adds an error callback to the stack of error handlers
    public function onError(Closure $callback) {
        $this->_errorCallbacks[] = $callback;
    }

    //Routes an exception through the error callbacks
    public function error(Exception $err) {
        $type = get_class($err);
        $msg = $err->getMessage();

        if (count($this->_errorCallbacks) > 0) {
            foreach (array_reverse($this->_errorCallbacks) as $callback) {
                if (is_callable($callback)) {
                    if($callback($this, $msg, $type)) {
                        return;
                    }
                } else {
                    $this->flash($err);
                    $this->redirect($callback);
                }
            }
        } else {
            $this->code(500);
            throw new ErrorException($err);
        }
    }

    //Returns an escaped request paramater
    public function param($param, $default = null) {
        global $__params;
        if (!isset($__params[$param])) return null;
        return htmlentities($__params[$param], ENT_QUOTES, 'UTF-8');
    }

    //Returns (and clears) all flashes of $type
    public function getFlashes($type = 'error') {
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

    //Escapes a string
    public function escape($str) {
        return htmlentities($str, ENT_QUOTES, 'UTF-8');
    }

    //Discards the current output buffer(s)
    public function discard() {
        while (@ ob_end_clean());
    }

    //Flushes the current output buffer(s)
    public function flush() {
        while (@ ob_end_flush());
    }

    //Allow callbacks to be assigned as properties and called like normal methods
    public function __call($method, $args) {
        if (!isset($this->$method) || !is_callable($this->$method)) {
            throw new ErrorException("Unknown method $method()");
        }
        $callback = $this->$method;
        switch (count($args)) {
            case 1:  return $callback($args[0]);
            case 2:  return $callback($args[0], $args[1]);
            case 3:  return $callback($args[0], $args[1], $args[2]);
            case 4:  return $callback($args[0], $args[1], $args[2], $args[3]);
            default: return call_user_func_array($callback, $args);
        }
    }
}

function addValidator($method, Closure $callback) {
    _Validator::$_methods[strtolower($method)] = $callback;
}

class _Validator {

    protected static $_defaultAdded = false;
    public static $_methods = array();

    protected $_str = null;
    protected $_err = null;

    //Sets up the validator chain with the string and optional error message
    public function __construct($str, $err = null) {
        $this->_str = $str;
        $this->_err = $err;
        if (false === static::$_defaultAdded) {
            static::addDefault();
            static::$_defaultAdded = true;
        }
    }

    //Adds default validators on first use. See README for usage details
    public static function addDefault() {
        static::$_methods['null'] = function($str) {
            return $str === null || $str === '';
        };
        static::$_methods['len'] = function($str, $min, $max = null) {
            $len = strlen($str);
            return null === $max ? $len === $min : $len >= $min && $len <= $max;
        };
        static::$_methods['int'] = function($str) {
            return (string)$str === ((string)(int)$str);
        };
        static::$_methods['float'] = function($str) {
            return (string)$str === ((string)(float)$str);
        };
        static::$_methods['email'] = function($str) {
            return filter_var($str, FILTER_VALIDATE_EMAIL);
        };
        static::$_methods['url'] = function($str) {
            return filter_var($str, FILTER_VALIDATE_URL);
        };
        static::$_methods['ip'] = function($str) {
            return filter_var($str, FILTER_VALIDATE_IP);
        };
        static::$_methods['alnum'] = function($str) {
            return ctype_alnum($str);
        };
        static::$_methods['alpha'] = function($str) {
            return ctype_alpha($str);
        };
        static::$_methods['contains'] = function($str, $needle) {
            return strpos($str, $needle) !== false;
        };
        static::$_methods['regex'] = function($str, $pattern) {
            return preg_match($pattern, $str);
        };
        static::$_methods['chars'] = function($str, $chars) {
            return preg_match("`^[$chars]++$`i", $str);
        };
    }

    public function __call($method, $args) {
        switch (substr($method, 0, 2)) {
        case 'is': //is<$validator>()
            $validator = substr($method, 2);
            break;
        case 'no': //not<$validator>()
            $reverse = true;
            $validator = substr($method, 3);
            break;
        default:   //<$validator>()
            $validator = $method;
            break;
        }
        $validator = strtolower($validator);

        if (!$validator || !isset(static::$_methods[$validator])) {
            throw new ErrorException("Unknown method $method()");
        }
        $validator = static::$_methods[$validator];
        array_unshift($args, $this->_str);

        switch (count($args)) {
            case 1:  $result = $validator($args[0]); break;
            case 2:  $result = $validator($args[0], $args[1]); break;
            case 3:  $result = $validator($args[0], $args[1], $args[2]); break;
            case 4:  $result = $validator($args[0], $args[1], $args[2], $args[3]); break;
            default: $result = call_user_func_array($validator, $args); break;
        }

        //Throw an exception on failed validation
        if (false === (bool)$result ^ (bool)$reverse) {
            throw new Exception($this->_err);
        }
        return $this;
    }
}
