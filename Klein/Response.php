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
     * The view layout
     *
     * @var string
     * @access protected
     */
    protected $layout;

    /**
     * The view to render
     *
     * @var string
     * @access protected
     */
    protected $view;


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

        $this->body    = $this->body($body);
        $this->status  = $this->code($status_code);
        $this->headers = new HeaderDataCollection($headers);
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
     * @return \Klein\DataCollection\HeaderDataCollection
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
     * @access public
     * @return Response
     */
    public function sendHeaders()
    {
        if (headers_sent()) {
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
            $this->headers->header('Transfer-encoding: chunked');
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
     * @param string $value     The value to set the header with
     * @access public
     * @return void
     */
    // public function header($key, $value = null)
    // {
    //     $this->headers->header($key, $value);
    // }

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
     * Stores a flash message of $type
     *
     * @param string $msg       The message to flash
     * @param string $type      The flash message type
     * @param array $params     Optional params to be parsed by markdown
     * @access public
     * @return void
     */
    public function flash($msg, $type = 'info', $params = null)
    {
        startSession();
        if (is_array($type)) {
            $params = $type;
            $type = 'info';
        }
        if (!isset($_SESSION['__flashes'])) {
            $_SESSION['__flashes'] = array($type => array());
        } elseif (!isset($_SESSION['__flashes'][$type])) {
            $_SESSION['__flashes'][$type] = array();
        }
        $_SESSION['__flashes'][$type][] = $this->markdown($msg, $params);
    }

    /**
     * Render a text string as markdown
     *
     * Supports basic markdown syntax
     *
     * @param string $str   The text string to parse
     * @param array $args   Optional arguments to be parsed by markdown
     * @access public
     * @return string
     */
    public function markdown($str, $args = null)
    {
        $args = func_get_args();
        $md = array(
            '/\[([^\]]++)\]\(([^\)]++)\)/' => '<a href="$2">$1</a>',
            '/\*\*([^\*]++)\*\*/'          => '<strong>$1</strong>',
            '/\*([^\*]++)\*/'              => '<em>$1</em>'
        );
        $str = array_shift($args);
        if (is_array($args[0])) {
            $args = $args[0];
        }
        foreach ($args as &$arg) {
            $arg = htmlentities($arg, ENT_QUOTES);
        }
        return vsprintf(preg_replace(array_keys($md), $md, $str), $args);
    }

    /**
     * Tell the browser not to cache the response
     *
     * @access public
     * @return void
     */
    public function noCache()
    {
        $this->header("Pragma: no-cache");
        $this->header('Cache-Control: no-store, no-cache');
    }

    /**
     * Sends a file
     *
     * @param string $path      The path of the file to send
     * @param string $filename  The file's name
     * @param string $mimetype  The MIME type of the file
     * @access public
     * @return void
     */
    public function file($path, $filename = null, $mimetype = null)
    {
        $this->discard();
        $this->noCache();
        set_time_limit(1200);
        if (null === $filename) {
            $filename = basename($path);
        }
        if (null === $mimetype) {
            $mimetype = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
        }
        $this->header('Content-type: ' . $mimetype);
        $this->header('Content-length: ' . filesize($path));
        $this->header('Content-Disposition: attachment; filename="'.$filename.'"');
        readfile($path);
    }

    /**
     * Sends an object as json or jsonp by providing the padding prefix
     *
     * @param mixed $object         The data to encode as JSON
     * @param string $jsonp_prefix  The name of the JSON-P function prefix
     * @access public
     * @return void
     */
    public function json($object, $jsonp_prefix = null)
    {
        $this->discard(true);
        $this->noCache();
        set_time_limit(1200);
        $json = json_encode($object);
        if (null !== $jsonp_prefix) {
            $this->header('Content-Type: text/javascript'); // should ideally be application/json-p once adopted
            echo "$jsonp_prefix($json);";
        } else {
            $this->header('Content-Type: application/json');
            echo $json;
        }
    }

    /**
     * Redirects the request to another URL
     *
     * @param string $url                   The URL to redirect to
     * @param int $code                     The HTTP status code to use for redirection
     * @param boolean $exit_after_redirect  Whether or not to exit after redirection
     * @access public
     * @return void
     */
    public function redirect($url, $code = 302, $exit_after_redirect = true)
    {
        $this->code($code);
        $this->header("Location: $url");
        if ($exit_after_redirect) {
            exit;
        }
    }

    /**
     * Redirects the request to the current URL
     *
     * @access public
     * @return void
     */
    public function refresh()
    {
        $this->redirect(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/');
    }

    /**
     * Redirects the request back to the referrer
     *
     * @access public
     * @return void
     */
    public function back()
    {
        if (isset($_SERVER['HTTP_REFERER'])) {
            $this->redirect($_SERVER['HTTP_REFERER']);
        }
        $this->refresh();
    }

    /**
     * Sets response properties/helpers
     *
     * @param string $key   The name of the response property
     * @param mixed $value  The value of the response property
     * @access public
     * @return void
     */
    public function set($key, $value = null)
    {
        if (!is_array($key)) {
            return $this->$key = $value;
        }
        foreach ($key as $k => $value) {
            $this->$k = $value;
        }
    }

    /**
     * Adds to or modifies the current query string
     *
     * @param string $key   The name of the query param
     * @param mixed $value  The value of the query param
     * @access public
     * @return void
     */
    public function query($key, $value = null)
    {
        $query = array();
        if (isset($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $query);
        }
        if (is_array($key)) {
            $query = array_merge($query, $key);
        } else {
            $query[$key] = $value;
        }

        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        if (strpos($request_uri, '?') !== false) {
            $request_uri = strstr($request_uri, '?', true);
        }
        return $request_uri . (!empty($query) ? '?' . http_build_query($query) : null);
    }

    /**
     * Set the view layout
     *
     * @param string $layout    The layout of the view
     * @access public
     * @return void
     */
    public function layout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * Renders the current view
     *
     * @access public
     * @return void
     */
    public function yield()
    {
        require $this->view;
    }

    /**
     * Renders a view + optional layout
     *
     * @param string $view  The view to render
     * @param array $data   The data to render in the view
     * @access public
     * @return void
     */
    public function render($view, array $data = array())
    {
        $original_view = $this->view;

        if (!empty($data)) {
            $this->set($data);
        }
        $this->view = $view;
        if (null === $this->layout) {
            $this->yield();
        } else {
            require $this->layout;
        }
        if (false !== $this->chunked) {
            $this->chunk();
        }

        // restore state for parent render()
        $this->view = $original_view;
    }

    /**
     * Renders a view without a layout
     *
     * @param string $view  The view to render
     * @param array $data   The data to render in the view
     * @access public
     * @return void
     */
    public function partial($view, array $data = array())
    {
        $layout = $this->layout;
        $this->layout = null;
        $this->render($view, $data);
        $this->layout = $layout;
    }

    /**
     * Sets a session variable
     *
     * @param string $key   The name of the session variable
     * @param mixed $value  The value to set in the session variable
     * @access public
     * @return mixed
     */
    public function session($key, $value = null)
    {
        startSession();
        return $_SESSION[$key] = $value;
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
     * Returns an escaped request paramater
     *
     * @todo .... what is this? Why is this here?
     * @todo REMOVE this and document the API change
     *
     * @param string $param     The name of the parameter
     * @param mixed $default    The default value of the parameter if its not set
     * @access public
     * @return void
     */
    public function param($param, $default = null)
    {
        return isset($_REQUEST[$param]) ?  htmlentities($_REQUEST[$param], ENT_QUOTES) : $default;
    }

    /**
     * Returns and clears all flashes of optional $type
     *
     * @param string $type  The name of the flash message type
     * @access public
     * @return array
     */
    public function flashes($type = null)
    {
        startSession();
        if (!isset($_SESSION['__flashes'])) {
            return array();
        }
        if (null === $type) {
            $flashes = $_SESSION['__flashes'];
            unset($_SESSION['__flashes']);
        } elseif (null !== $type) {
            $flashes = array();
            if (isset($_SESSION['__flashes'][$type])) {
                $flashes = $_SESSION['__flashes'][$type];
                unset($_SESSION['__flashes'][$type]);
            }
        }
        return $flashes;
    }

    /**
     * Escapes a string
     *
     * @todo This is so generic, it might work better in a Utils class...
     *
     * @param string $str   The string to escape
     * @access public
     * @return void
     */
    public function escape($str)
    {
        return htmlentities($str, ENT_QUOTES);
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
