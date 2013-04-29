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

use \Exception;
use \ErrorException;
use \Klein\DataCollection\HeaderDataCollection;

/**
 * Response 
 * 
 * @package     Klein
 */
class Response
{

    /**
     * Class properties
     */

    /**
     * The default response HTTP status code
     *
     * @static
     * @var int
     * @access protected
     */
    protected static $default_status_code = 200;

    /**
     * The HTTP version of the response
     *
     * @var string
     * @access protected
     */
    protected $protocol_version = '1.1';

    /**
     * The response body
     *
     * @var string
     * @access protected
     */
    protected $body;

    /**
     * HTTP response status
     *
     * @var \Klein\HttpStatus
     * @access protected
     */
    protected $status;

    /**
     * HTTP response headers
     *
     * @var \Klein\DataCollection\HeaderDataCollection
     * @access protected
     */
    protected $headers;

    /**
     * Whether or not the response is "locked" from
     * any further modification
     *
     * @var boolean
     * @access protected
     */
    protected $locked = false;

    /**
     * Whether the response has been chunked or not
     *
     * @var boolean
     * @access public
     */
    public $chunked = false;

    /**
     * An array of error callback callables
     *
     * @var array[callable]
     * @access protected
     */
    protected $errorCallbacks = array();


    /**
     * Methods
     */

    /**
     * Constructor
     *
     * Create a new Response object with a dependency injected Headers instance
     *
     * @param string $body          The response body's content
     * @param int $status_code      The status code
     * @param array $headers        The response header "hash"
     * @access public
     */
    public function __construct($body = '', $status_code = null, array $headers = array())
    {
        $status_code   = $status_code ?: static::$default_status_code;

        // Set our body and code using our internal methods
        $this->body($body);
        $this->code($status_code);

        $this->headers = new HeaderDataCollection($headers);
    }

    /**
     * Get (or set) the HTTP protocol version
     *
     * Simply calling this method without any arguments returns the current protocol version.
     * Calling with an integer argument, however, attempts to set the protocol version to what
     * was provided by the argument.
     *
     * @param string $protocol_version
     * @access public
     * @return string|Response
     */
    public function protocolVersion($protocol_version = null)
    {
        if (null !== $protocol_version) {
            if (!$this->isLocked()) {
                $this->protocol_version = (string) $protocol_version;
            }

            return $this;
        }

        return $this->protocol_version;
    }

    /**
     * Get (or set) the response's body content
     *
     * Simply calling this method without any arguments returns the current response body.
     * Calling with an argument, however, sets the response body to what was provided by the argument.
     *
     * @param string $body  The body content string
     * @access public
     * @return string|Response
     */
    public function body($body = null)
    {
        if (null !== $body) {
            if (!$this->isLocked()) {
                $this->body = (string) $body;
            }

            return $this;
        }

        return $this->body;
    }

    /**
     * Returns the status object
     *
     * @access public
     * @return \Klein\HttpStatus
     */
    public function status()
    {
        return $this->status;
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
     * Get (or set) the HTTP response code
     *
     * Simply calling this method without any arguments returns the current response code.
     * Calling with an integer argument, however, attempts to set the response code to what
     * was provided by the argument.
     *
     * @param int $code     The HTTP status code to send
     * @access public
     * @return int|Response
     */
    public function code($code = null)
    {
        if (null !== $code) {
            if (!$this->isLocked()) {
                $this->status = new HttpStatus($code);
            }

            return $this;
        }

        return $this->status->getCode();
    }

    /**
     * Prepend a string to the response's content body
     *
     * @param string $content   The string to prepend
     * @access public
     * @return Response
     */
    public function prepend($content)
    {
        if (!$this->isLocked()) {
            $this->body = $content . $this->body;
        }

        return $this;
    }

    /**
     * Append a string to the response's content body
     *
     * @param string $content   The string to append
     * @access public
     * @return Response
     */
    public function append($content)
    {
        if (!$this->isLocked()) {
            $this->body .= $content;
        }

        return $this;
    }

    /**
     * Check if the response is locked
     *
     * @access public
     * @return boolean
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * Lock the response from further modification
     *
     * @access public
     * @return Response
     */
    public function lock()
    {
        $this->locked = true;

        return $this;
    }

    /**
     * Unlock the response from further modification
     *
     * @access public
     * @return Response
     */
    public function unlock()
    {
        $this->locked = false;

        return $this;
    }

    /**
     * Generates an HTTP compatible status header line string
     *
     * Creates the string based off of the response's properties
     *
     * @access protected
     * @return string
     */
    protected function httpStatusLine()
    {
        return sprintf('HTTP/%s %s', $this->protocol_version, $this->status);
    }

    /**
     * Send our HTTP headers
     *
     * @param mixed $override   Whether or not to override the check if headers have already been sent
     * @access public
     * @return Response
     */
    public function sendHeaders($override = false)
    {
        if (headers_sent() && !$override) {
            return $this;
        }

        // Send our HTTP status line
        header($this->httpStatusLine());

        // Iterate through our Headers data collection and send each header
        foreach ($this->headers as $key => $value) {
            header($key .': '. $value, false);
        }

        return $this;
    }

    /**
     * Send our body's contents
     *
     * @access public
     * @return Response
     */
    public function sendBody()
    {
        echo $this->body;

        return $this;
    }

    /**
     * Send the response and lock it
     *
     * @access public
     * @return Response
     */
    public function send()
    {
        // Send our response data
        $this->sendHeaders();
        $this->sendBody();

        // Lock the response from further modification
        $this->lock();

        // If there running FPM, tell the process manager to finish the server request/response handling
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        return $this;
    }

    /**
     * Enable response chunking
     *
     * @link https://github.com/chriso/klein.php/wiki/Response-Chunking
     * @link http://bit.ly/hg3gHb
     * @param string $str   An optional string to send as a response "chunk"
     * @access public
     * @return void
     */
    public function chunk($str = null)
    {
        if (false === $this->chunked) {
            $this->chunked = true;
            $this->headers->header('Transfer-encoding', 'chunked');
            flush();
        }
        if (null !== $str) {
            printf("%x\r\n", strlen($str));
            echo "$str\r\n";
            flush();
        } elseif (($ob_length = ob_get_length()) > 0) {
            printf("%x\r\n", $ob_length);
            ob_flush();
            echo "\r\n";
            flush();
        }
    }

    /**
     * Sets a response header
     *
     * @param string $key       The name of the HTTP response header
     * @param mixed $value      The value to set the header with
     * @access public
     * @return void
     */
    public function header($key, $value)
    {
        $this->headers->set($key, $value);
    }

    /**
     * Sets a response cookie
     *
     * @param string $key           The name of the cookie
     * @param string $value         The value to set the cookie with
     * @param int $expiry           The time that the cookie should expire
     * @param string $path          The path of which to restrict the cookie
     * @param string $domain        The domain of which to restrict the cookie
     * @param boolean $secure       Flag of whether the cookie should only be sent over a HTTPS connection
     * @param boolean $httponly     Flag of whether the cookie should only be accessible over the HTTP protocol
     * @access public
     * @return boolean
     */
    public function cookie(
        $key,
        $value = '',
        $expiry = null,
        $path = '/',
        $domain = null,
        $secure = false,
        $httponly = false
    ) {
        if (null === $expiry) {
            $expiry = time() + (3600 * 24 * 30);
        }

        return setcookie($key, $value, $expiry, $path, $domain, $secure, $httponly);
    }

    /**
     * Tell the browser not to cache the response
     *
     * @access public
     * @return Response
     */
    public function noCache()
    {
        $this->header('Pragma', 'no-cache');
        $this->header('Cache-Control', 'no-store, no-cache');

        return $this;
    }

    /**
     * Redirects the request to another URL
     *
     * @param string $url                   The URL to redirect to
     * @param int $code                     The HTTP status code to use for redirection
     * @access public
     * @return Response
     */
    public function redirect($url, $code = 302)
    {
        $this->code($code);
        $this->header('Location', $url);
        $this->lock();

        return $this;
    }

    /**
     * Adds an error callback to the stack of error handlers
     *
     * @param callable $callback            The callable function to execute in the error handling chain
     * @param boolean $allow_duplicates     Whether or not to allow duplicate callbacks to exist in the
     *  error handling chain
     * @access public
     * @return boolean | void
     */
    public function onError($callback, $allow_duplicates = true)
    {
        if (!$allow_duplicates && in_array($callback, $this->errorCallbacks)) {
            return false;
        }

        $this->errorCallbacks[] = $callback;
    }

    /**
     * Routes an exception through the error callbacks
     *
     * @param Exception $err    The exception that occurred
     * @access public
     * @return void
     */
    public function error(Exception $err)
    {
        $type = get_class($err);
        $msg = $err->getMessage();

        if (count($this->errorCallbacks) > 0) {
            foreach (array_reverse($this->errorCallbacks) as $callback) {
                if (is_callable($callback)) {
                    if ($callback($this, $msg, $type, $err)) {
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

    /**
     * Discards the current output buffer and restarts it if passed a true boolean
     *
     * @param boolean $restart_buffer   Whether or not to restart the output buffer after discarding it
     * @access public
     * @return void
     */
    public function discard($restart_buffer = false)
    {
        $cleaned = ob_end_clean();

        if ($restart_buffer) {
            ob_start();
        }

        return $cleaned;
    }

    /**
     * Flushes the current output buffer
     *
     * @access public
     * @return void
     */
    public function flush()
    {
        ob_end_flush();
    }

    /**
     * Return the current output buffer as a string
     *
     * @access public
     * @return string
     */
    public function buffer()
    {
        return ob_get_contents();
    }

    /**
     * Dump a variable
     *
     * @param mixed $obj    The variable to dump
     * @access public
     * @return void
     */
    public function dump($obj)
    {
        if (is_array($obj) || is_object($obj)) {
            $obj = print_r($obj, true);
        }

        echo '<pre>' .  htmlentities($obj, ENT_QUOTES) . "</pre><br />\n";
    }

    /**
     * Magic "__call" method
     *
     * Allows the ability to arbitrarily call a property as a callable method
     * Allow callbacks to be assigned as properties and called like normal methods
     *
     * @param callable $method  The callable method to execute
     * @param array $args       The argument array to pass to our callback
     * @access public
     * @return void
     */
    public function __call($method, $args)
    {
        if (!isset($this->$method) || !is_callable($this->$method)) {
            throw new ErrorException("Unknown method $method()");
        }

        $callback = $this->$method;

        switch (count($args)) {
            case 1:
                return $callback($args[0]);
            case 2:
                return $callback($args[0], $args[1]);
            case 3:
                return $callback($args[0], $args[1], $args[2]);
            case 4:
                return $callback($args[0], $args[1], $args[2], $args[3]);
            default:
                return call_user_func_array($callback, $args);
        }
    }
}
