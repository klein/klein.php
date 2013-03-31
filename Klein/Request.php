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
 * @package     Klein
 */
class Request {

    /**
     * Class properties
     */

    /**
     * Unique identifier for the request
     *
     * @var string
     * @access protected
     */
    protected $id;

    /**
     * HTTP Headers helper
     *
     * @var Headers
     * @access protected
     */
    protected $headers;

    /**
     * The request body
     *
     * @var string
     * @access protected
     */
    protected $body;


    /**
     * Methods
     */

    /**
     * Constructor
     *
     * Create a new Request object with a dependency injected Headers instance
     *
     * @param Headers $headers  Headers class to handle writing HTTP headers
     * @access public
     */
    public function __construct( Headers $headers ) {
        $this->headers = $headers;
    }

    /**
     * Returns all parameters (GET, POST, named) that match the mask
     *
     * Takes an optional mask param that contains the names of any params
     * you'd like this method to exclude in the returned array
     *
     * @param array $mask  The parameter mask array
     * @access public
     * @return array
     */
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

    /**
     * Return a request parameter, or $default if it doesn't exist
     *
     * @param string $key       The name of the parameter to return
     * @param mixed $default    The default value of the parameter if it contains no value
     * @access public
     * @return string
     */
    public function param($key, $default = null) {
        return isset($_REQUEST[$key]) && $_REQUEST[$key] !== '' ? $_REQUEST[$key] : $default;
    }

    /**
     * Magic "__isset" method
     *
     * Allows the ability to arbitrarily check the existence of a parameter
     * from this instance while treating it as an instance property
     *
     * @param string $param     The name of the parameter
     * @access public
     * @return boolean
     */
    public function __isset($param) {
        return isset($_REQUEST[$param]);
    }

    /**
     * Magic "__get" method
     *
     * Allows the ability to arbitrarily request a parameter from this instance
     * while treating it as an instance property
     *
     * @param string $param     The name of the parameter
     * @access public
     * @return string
     */
    public function __get($param) {
        return isset($_REQUEST[$param]) ? $_REQUEST[$param] : null;
    }

    /**
     * Magic "__set" method
     *
     * Allows the ability to arbitrarily set a parameter from this instance
     * while treating it as an instance property
     *
     * @param string $param     The name of the parameter
     * @param mixed $value      The value of the parameter
     * @access public
     * @return void
     */
    public function __set($param, $value) {
        $_REQUEST[$param] = $value;
    }

    /**
     * Magic "__unset" method
     *
     * Allows the ability to arbitrarily remove a parameter from this instance
     * while treating it as an instance property
     *
     * @param string $param     The name of the parameter
     * @access public
     * @return void
     */
    public function __unset($param) {
        unset($_REQUEST[$param]);
    }

    /**
     * Is the request secure?
     *
     * If $required then redirect to the secure version of the URL
     *
     * @param boolean $required     Whether or not the request is required to be secure
     * @access public
     * @return boolean
     */
    public function isSecure($required = false) {
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'];
        if (!$secure && $required) {
            $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $this->headers->header('Location: ' . $url);
        }
        return $secure;
    }

    /**
     * Gets a request header
     *
     * @param string $key       The name of the HTTP request header
     * @param mixed $default    The default value of the header if its not set
     * @access public
     * @return string
     */
    public function header($key, $default = null) {
        $key = 'HTTP_' . strtoupper(str_replace('-','_', $key));
        return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
    }

    /**
     * Gets a request cookie
     *
     * @param string $key       The name of the cookie
     * @param mixed $default    The default value of the cookie if its not set
     * @access public
     * @return string
     */
    public function cookie($key, $default = null) {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }

    /**
     * Gets a session variable associated with the request
     * 
     * @param string $key       The name of the session variable
     * @param mixed $default    The default value of the session variable if its not set
     * @access public
     * @return mixed
     */
    public function session($key, $default = null) {
        startSession();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    /**
     * Gets the request IP address
     *
     * @access public
     * @return string
     */
    public function ip() {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }

    /**
     * Gets the request user agent
     *
     * @access public
     * @return string
     */
    public function userAgent() {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    }

    /**
     * Gets the request URI
     *
     * @access public
     * @return string
     */
    public function uri() {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    }

    /**
     * Gets the request body
     *
     * @access public
     * @return string
     */
    public function body() {
        if (null === $this->body) {
            $this->body = @file_get_contents('php://input');
        }
        return $this->body;
    }

    /**
     * Gets the request method, or checks it against $is
     *
     * <code>
     * // POST request example
     * $request->method() // returns 'POST'
     * $request->method('post') // returns true
     * $request->method('get') // returns false
     * </code>
     * 
     * @param string $is    The method to check the current request method against
     * @access public
     * @return string | boolean
     */
    public function method($is = null) {
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if (null !== $is) {
            return strcasecmp($method, $is) === 0;
        }
        return $method;
    }

    /**
     * Start a validator chain for the specified parameter
     *
     * @param string $param     The name of the parameter to validate
     * @param string $err       The custom exception message to throw
     * @access public
     * @return Validator
     */
    public function validate($param, $err = null) {
        return new Validator($this->param($param), $err);
    }

    /**
     * Gets a unique ID for the request
     *
     * Generates one on the first call
     *
     * @access public
     * @return string
     */
    public function id() {
        if (null === $this->id) {
            $this->id = sha1(mt_rand() . microtime(true) . mt_rand());
        }
        return $this->id;
    }

} // End class Request
