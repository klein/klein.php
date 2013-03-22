<?php
/**
 * Klein (klein.php) - A lightning fast router for PHP
 *
 * @author      Chris O'Hara <cohara87@gmail.com>
 * @author      Trevor Suarez (Rican7) (contributor and v2 refactorer)
 * @copyright   (c) Chris O'Hara
 * @link        https://github.com/chriso/klein.php
 * @license     MIT
 */

namespace Klein;


/**
 * Request
 * 
 * @package    Klein
 */
class Request {

    protected $_id = null;

    // HTTP headers helper
    protected $_headers = null;

    protected $_body = null;

    public function	__construct( Headers $headers ) {
        $this->_headers = $headers;
    }

    // Returns all parameters (GET, POST, named) that match the mask
    public function params($mask = null) {
        $params = $_REQUEST;
        if (null !== $mask) {
            if (!is_array($mask)) {
                $mask = func_get_args();
            }
            $params = array_intersect_key($params, array_flip($mask));
            // Make sure each key in $mask has at least a null value
            foreach ($mask as $key) {
                if (!isset($params[$key])) {
                    $params[$key] = null;
                }
            }
        }
        return $params;
    }

    // Return a request parameter, or $default if it doesn't exist
    public function param($key, $default = null) {
        return isset($_REQUEST[$key]) && $_REQUEST[$key] !== '' ? $_REQUEST[$key] : $default;
    }

    public function __isset($param) {
        return isset($_REQUEST[$param]);
    }

    public function __get($param) {
        return isset($_REQUEST[$param]) ? $_REQUEST[$param] : null;
    }

    public function __set($param, $value) {
        $_REQUEST[$param] = $value;
    }

    public function __unset($param) {
        unset($_REQUEST[$param]);
    }

    // Is the request secure? If $required then redirect to the secure version of the URL
    public function isSecure($required = false) {
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'];
        if (!$secure && $required) {
            $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $this->_headers->header('Location: ' . $url);
        }
        return $secure;
    }

    // Gets a request header
    public function header($key, $default = null) {
        $key = 'HTTP_' . strtoupper(str_replace('-','_', $key));
        return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
    }

    // Gets a request cookie
    public function cookie($key, $default = null) {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }

    // Gets the request method, or checks it against $is - e.g. method('post') => true
    public function method($is = null) {
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if (null !== $is) {
            return strcasecmp($method, $is) === 0;
        }
        return $method;
    }

    // Start a validator chain for the specified parameter
    public function validate($param, $err = null) {
        return new _Validator($this->param($param), $err);
    }

    // Gets a unique ID for the request
    public function id() {
        if (null === $this->_id) {
            $this->_id = sha1(mt_rand() . microtime(true) . mt_rand());
        }
        return $this->_id;
    }

    // Gets a session variable associated with the request
    public function session($key, $default = null) {
        startSession();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    // Gets the request IP address
    public function ip() {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }

    // Gets the request user agent
    public function userAgent() {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    }

    // Gets the request URI
    public function uri() {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    }

    // Gets the request body
    public function body() {
        if (null === $this->_body) {
            $this->_body = @file_get_contents('php://input');
        }
        return $this->_body;
    }

} // End class Request
