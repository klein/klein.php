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

use \Klein\DataCollection\DataCollection;
use \Klein\DataCollection\ServerDataCollection;
use \Klein\DataCollection\HeaderDataCollection;

/**
 * Request
 * 
 * @package     Klein
 */
class Request
{

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
     * GET (query) parameters
     *
     * @var \Klein\DataCollection\DataCollection
     * @access protected
     */
    protected $params_get;

    /**
     * POST parameters
     *
     * @var \Klein\DataCollection\DataCollection
     * @access protected
     */
    protected $params_post;

    /**
     * Named parameters
     *
     * @var \Klein\DataCollection\DataCollection
     * @access protected
     */
    protected $params_named;

    /**
     * Client cookie data
     *
     * @var \Klein\DataCollection\DataCollection
     * @access protected
     */
    protected $cookies;

    /**
     * Server created attributes
     *
     * @var \Klein\DataCollection\ServerDataCollection
     * @access protected
     */
    protected $server;

    /**
     * HTTP request headers
     *
     * @var \Klein\DataCollection\HeaderDataCollection
     * @access protected
     */
    protected $headers;

    /**
     * Uploaded temporary files
     *
     * @var \Klein\DataCollection\DataCollection
     * @access protected
     */
    protected $files;

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
     * Create a new Request object and define all of its request data
     *
     * @param array  $params_get
     * @param array  $params_post
     * @param array  $cookies
     * @param array  $server
     * @param array  $files
     * @param string $body
     * @access public
     */
    public function __construct(
        array $params_get = array(),
        array $params_post = array(),
        array $cookies = array(),
        array $server = array(),
        array $files = array(),
        $body = null
    ) {
        // Assignment city...
        $this->params_get   = new DataCollection($params_get);
        $this->params_post  = new DataCollection($params_post);
        $this->cookies      = new DataCollection($cookies);
        $this->server       = new ServerDataCollection($server);
        $this->headers      = new HeaderDataCollection($this->server->getHeaders());
        $this->files        = new DataCollection($files);
        $this->body         = $body ? (string) $body : null;

        // Non-injected assignments
        $this->params_named = new DataCollection();
    }

    /**
     * Create a new request object using the built-in "superglobals"
     *
     * @link http://php.net/manual/en/language.variables.superglobals.php
     * @static
     * @access public
     * @return Request
     */
    public static function createFromGlobals()
    {
        // Create and return a new instance of this
        return new static(
            $_GET,
            $_POST,
            $_COOKIE,
            $_SERVER,
            $_FILES,
            null // Let our content getter take care of the "body"
        );
    }

    /**
     * Gets a unique ID for the request
     *
     * Generates one on the first call
     *
     * @param boolean $hash     Whether or not to hash the ID on creation
     * @access public
     * @return string
     */
    public function id($hash = true)
    {
        if (is_null($this->id)) {
            $this->id = uniqid();

            if ($hash) {
                $this->id = sha1($this->id);
            }
        }

        return $this->id;
    }

    /**
     * Returns the GET parameters collection
     *
     * @access public
     * @return \Klein\DataCollection\DataCollection
     */
    public function paramsGet()
    {
        return $this->params_get;
    }

    /**
     * Returns the POST parameters collection
     *
     * @access public
     * @return \Klein\DataCollection\DataCollection
     */
    public function paramsPost()
    {
        return $this->params_post;
    }

    /**
     * Returns the named parameters collection
     *
     * @access public
     * @return \Klein\DataCollection\DataCollection
     */
    public function paramsNamed()
    {
        return $this->params_named;
    }

    /**
     * Returns the cookies collection
     *
     * @access public
     * @return \Klein\DataCollection\DataCollection
     */
    public function cookies()
    {
        return $this->cookies;
    }

    /**
     * Returns the server collection
     *
     * @access public
     * @return \Klein\DataCollection\DataCollection
     */
    public function server()
    {
        return $this->server;
    }

    /**
     * Returns the headers collection
     *
     * @access public
     * @return \Klein\DataCollection\HeaderDataCollection
     */
    public function headers()
    {
        return $this->headers;
    }

    /**
     * Returns the files collection
     *
     * @access public
     * @return \Klein\DataCollection\DataCollection
     */
    public function files()
    {
        return $this->files;
    }

    /**
     * Gets the request body
     *
     * @access public
     * @return string
     */
    public function body()
    {
        // Only get it once
        if (is_null($this->body)) {
            $this->body = @file_get_contents('php://input');
        }

        return $this->body;
    }

    /**
     * Returns all parameters (GET, POST, named, and cookies) that match the mask
     *
     * Takes an optional mask param that contains the names of any params
     * you'd like this method to exclude in the returned array
     *
     * @see \Klein\DataCollection\DataCollection::all()
     * @param array $mask  The parameter mask array
     * @access public
     * @return array
     */
    public function params($mask = null)
    {
        // Merge our params in the get, post, cookies, named order
        return array_merge(
            $this->params_get->all($mask),
            $this->params_post->all($mask),
            $this->cookies->all($mask),
            $this->params_named->all($mask) // Add our named params last
        );
    }

    /**
     * Return a request parameter, or $default if it doesn't exist
     *
     * @param string $key       The name of the parameter to return
     * @param mixed $default    The default value of the parameter if it contains no value
     * @access public
     * @return string
     */
    public function param($key, $default = null)
    {
        // Get all of our request params
        $params = $this->params();

        return isset($params[$key]) ? $params[$key] : $default;
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
    public function __isset($param)
    {
        // Get all of our request params
        $params = $this->params();

        return isset($params[$param]);
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
    public function __get($param)
    {
        return $this->param($param);
    }

    /**
     * Magic "__set" method
     *
     * Allows the ability to arbitrarily set a parameter from this instance
     * while treating it as an instance property
     *
     * NOTE: This currently sets the "named" parameters, since that's the
     * one collection that we have the most sane control over
     *
     * @param string $param     The name of the parameter
     * @param mixed $value      The value of the parameter
     * @access public
     * @return void
     */
    public function __set($param, $value)
    {
        $this->params_named->set($param, $value);
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
    public function __unset($param)
    {
        $this->params_named->remove($param);
    }

    /**
     * Is the request secure?
     *
     * @access public
     * @return boolean
     */
    public function isSecure()
    {
        return ($this->server->get('HTTPS') == true);
    }

    /**
     * Gets the request IP address
     *
     * @access public
     * @return string
     */
    public function ip()
    {
        return $this->server->get('REMOTE_ADDR');
    }

    /**
     * Gets the request user agent
     *
     * @access public
     * @return string
     */
    public function userAgent()
    {
        return $this->headers->get('USER_AGENT');
    }

    /**
     * Gets the request URI
     *
     * @access public
     * @return string
     */
    public function uri()
    {
        return $this->server->get('REQUEST_URI', '/');
    }

    /**
     * Get the request's pathname
     *
     * @access public
     * @return string
     */
    public function pathname()
    {
        $uri = $this->uri();

        // Strip the query string from the URI
        $uri = strstr($uri, '?', true) ?: $uri;

        return $uri;
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
     * @param string $is				The method to check the current request method against
     * @param boolean $allow_override	Whether or not to allow HTTP method overriding via header or params
     * @access public
     * @return string | boolean
     */
    public function method($is = null, $allow_override = true)
    {
        $method = $this->server->get('REQUEST_METHOD', 'GET');

        // Override
        if ($allow_override && $method === 'POST') {
            // For legacy servers, override the HTTP method with the X-HTTP-Method-Override header or _method parameter
            if ($this->server->exists('X_HTTP_METHOD_OVERRIDE')) {
                $method = $this->server->get('X_HTTP_METHOD_OVERRIDE', $method);
            } else {
                $method = $this->param('_method', $method);
            }

            $method = strtoupper($method);
        }

        // We're doing a check
        if (null !== $is) {
            return strcasecmp($method, $is) === 0;
        }

        return $method;
    }

    /**
     * Adds to or modifies the current query string
     *
     * @param string $key   The name of the query param
     * @param mixed $value  The value of the query param
     * @access public
     * @return string
     */
    public function query($key, $value = null)
    {
        $query = array();

        parse_str(
            $this->server()->get('QUERY_STRING'),
            $query
        );

        if (is_array($key)) {
            $query = array_merge($query, $key);
        } else {
            $query[$key] = $value;
        }

        $request_uri = $this->uri();

        if (strpos($request_uri, '?') !== false) {
            $request_uri = strstr($request_uri, '?', true);
        }

        return $request_uri . (!empty($query) ? '?' . http_build_query($query) : null);
    }
}
