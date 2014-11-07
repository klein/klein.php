<?php
/**
 * Klein (klein.php) - A fast & flexible router for PHP
 *
 * @author      Chris O'Hara <cohara87@gmail.com>
 * @author      Trevor Suarez (Rican7) (contributor and v2 refactorer)
 * @copyright   (c) Chris O'Hara
 * @link        https://github.com/chriso/klein.php
 * @license     MIT
 */

namespace Klein;

use Klein\DataCollection\DataCollection;

/**
 * ServiceProvider 
 *
 * Service provider class for handling logic extending between
 * a request's data and a response's behavior
 */
class ServiceProvider
{

    /**
     * Class properties
     */

    /**
     * The Request instance containing HTTP request data and behaviors
     *
     * @type Request
     */
    protected $request;

    /**
     * The Response instance containing HTTP response data and behaviors
     *
     * @type AbstractResponse
     */
    protected $response;

    /**
     * The id of the current PHP session
     *
     * @type string|boolean
     */
    protected $session_id;

    /**
     * The view layout
     *
     * @type string
     */
    protected $layout;

    /**
     * The view to render
     *
     * @type string
     */
    protected $view;

    /**
     * Shared data collection
     *
     * @type DataCollection
     */
    protected $shared_data;


    /**
     * Methods
     */

    /**
     * Constructor
     *
     * @param Request $request              Object containing all HTTP request data and behaviors
     * @param AbstractResponse $response    Object containing all HTTP response data and behaviors
     */
    public function __construct(Request $request = null, AbstractResponse $response = null)
    {
        // Bind our objects
        $this->bind($request, $response);

        // Instantiate our shared data collection
        $this->shared_data = new DataCollection();
    }

    /**
     * Bind object instances to this service
     *
     * @param Request $request              Object containing all HTTP request data and behaviors
     * @param AbstractResponse $response    Object containing all HTTP response data and behaviors
     * @return ServiceProvider
     */
    public function bind(Request $request = null, AbstractResponse $response = null)
    {
        // Keep references
        $this->request  = $request  ?: $this->request;
        $this->response = $response ?: $this->response;

        return $this;
    }

    /**
     * Returns the shared data collection object
     *
     * @return \Klein\DataCollection\DataCollection
     */
    public function sharedData()
    {
        return $this->shared_data;
    }

    /**
     * Get the current session's ID
     *
     * This will start a session if the current session id is null
     *
     * @return string|false
     */
    public function startSession()
    {
        if (session_id() === '') {
            // Attempt to start a session
            session_start();

            $this->session_id = session_id() ?: false;
        }

        return $this->session_id;
    }

    /**
     * Stores a flash message of $type
     *
     * @param string $msg       The message to flash
     * @param string $type      The flash message type
     * @param array $params     Optional params to be parsed by markdown
     * @return void
     */
    public function flash($msg, $type = 'info', $params = null)
    {
        $this->startSession();
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
     * Returns and clears all flashes of optional $type
     *
     * @param string $type  The name of the flash message type
     * @return array
     */
    public function flashes($type = null)
    {
        $this->startSession();

        if (!isset($_SESSION['__flashes'])) {
            return array();
        }

        if (null === $type) {
            $flashes = $_SESSION['__flashes'];
            unset($_SESSION['__flashes']);
        } else {
            $flashes = array();
            if (isset($_SESSION['__flashes'][$type])) {
                $flashes = $_SESSION['__flashes'][$type];
                unset($_SESSION['__flashes'][$type]);
            }
        }

        return $flashes;
    }

    /**
     * Render a text string as markdown
     *
     * Supports basic markdown syntax
     *
     * Also, this method takes in EITHER an array of optional arguments (as the second parameter)
     * ... OR this method will simply take a variable number of arguments (after the initial str arg)
     *
     * @param string $str   The text string to parse
     * @param array $args   Optional arguments to be parsed by markdown
     * @return string
     */
    public static function markdown($str, $args = null)
    {
        // Create our markdown parse/conversion regex's
        $md = array(
            '/\[([^\]]++)\]\(([^\)]++)\)/' => '<a href="$2">$1</a>',
            '/\*\*([^\*]++)\*\*/'          => '<strong>$1</strong>',
            '/\*([^\*]++)\*/'              => '<em>$1</em>'
        );

        // Let's make our arguments more "magical"
        $args = func_get_args(); // Grab all of our passed args
        $str = array_shift($args); // Remove the initial arg from the array (and set the $str to it)
        if (isset($args[0]) && is_array($args[0])) {
            /**
             * If our "second" argument (now the first array item is an array)
             * just use the array as the arguments and forget the rest
             */
            $args = $args[0];
        }

        // Encode our args so we can insert them into an HTML string
        foreach ($args as &$arg) {
            $arg = htmlentities($arg, ENT_QUOTES, 'UTF-8');
        }

        // Actually do our markdown conversion
        return vsprintf(preg_replace(array_keys($md), $md, $str), $args);
    }

    /**
     * Escapes a string for UTF-8 HTML displaying
     *
     * This is a quick macro for escaping strings designed
     * to be shown in a UTF-8 HTML environment. Its options
     * are otherwise limited by design
     *
     * @param string $str   The string to escape
     * @param int $flags    A bitmask of `htmlentities()` compatible flags
     * @return string
     */
    public static function escape($str, $flags = ENT_QUOTES)
    {
        return htmlentities($str, $flags, 'UTF-8');
    }

    /**
     * Redirects the request to the current URL
     *
     * @return ServiceProvider
     */
    public function refresh()
    {
        $this->response->redirect(
            $this->request->uri()
        );

        return $this;
    }

    /**
     * Redirects the request back to the referrer
     *
     * @return ServiceProvider
     */
    public function back()
    {
        $referer = $this->request->server()->get('HTTP_REFERER');

        if (null !== $referer) {
            $this->response->redirect($referer);
        } else {
            $this->refresh();
        }

        return $this;
    }

    /**
     * Get (or set) the view's layout
     *
     * Simply calling this method without any arguments returns the current layout.
     * Calling with an argument, however, sets the layout to what was provided by the argument.
     *
     * @param string $layout    The layout of the view
     * @return string|ServiceProvider
     */
    public function layout($layout = null)
    {
        if (null !== $layout) {
            $this->layout = $layout;

            return $this;
        }

        return $this->layout;
    }

    /**
     * Renders the current view
     *
     * @return void
     */
    public function yieldView()
    {
        require $this->view;
    }

    /**
     * Renders a view + optional layout
     *
     * @param string $view  The view to render
     * @param array $data   The data to render in the view
     * @return void
     */
    public function render($view, array $data = array())
    {
        $original_view = $this->view;

        if (!empty($data)) {
            $this->shared_data->merge($data);
        }

        $this->view = $view;

        if (null === $this->layout) {
            $this->yieldView();
        } else {
            require $this->layout;
        }

        if (false !== $this->response->chunked) {
            $this->response->chunk();
        }

        // restore state for parent render()
        $this->view = $original_view;
    }

    /**
     * Renders a view without a layout
     *
     * @param string $view  The view to render
     * @param array $data   The data to render in the view
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
     * Add a custom validator for our validation method
     *
     * @param string $method        The name of the validator method
     * @param callable $callback    The callback to perform on validation
     * @return void
     */
    public function addValidator($method, $callback)
    {
        Validator::addValidator($method, $callback);
    }

    /**
     * Start a validator chain for the specified string
     *
     * @param string $string    The string to validate
     * @param string $err       The custom exception message to throw
     * @return Validator
     */
    public function validate($string, $err = null)
    {
        return new Validator($string, $err);
    }

    /**
     * Start a validator chain for the specified parameter
     *
     * @param string $param     The name of the parameter to validate
     * @param string $err       The custom exception message to throw
     * @return Validator
     */
    public function validateParam($param, $err = null)
    {
        return $this->validate($this->request->param($param), $err);
    }


    /**
     * Magic "__isset" method
     *
     * Allows the ability to arbitrarily check the existence of shared data
     * from this instance while treating it as an instance property
     *
     * @param string $key     The name of the shared data
     * @return boolean
     */
    public function __isset($key)
    {
        return $this->shared_data->exists($key);
    }

    /**
     * Magic "__get" method
     *
     * Allows the ability to arbitrarily request shared data from this instance
     * while treating it as an instance property
     *
     * @param string $key     The name of the shared data
     * @return string
     */
    public function __get($key)
    {
        return $this->shared_data->get($key);
    }

    /**
     * Magic "__set" method
     *
     * Allows the ability to arbitrarily set shared data from this instance
     * while treating it as an instance property
     *
     * @param string $key     The name of the shared data
     * @param mixed $value      The value of the shared data
     * @return void
     */
    public function __set($key, $value)
    {
        $this->shared_data->set($key, $value);
    }

    /**
     * Magic "__unset" method
     *
     * Allows the ability to arbitrarily remove shared data from this instance
     * while treating it as an instance property
     *
     * @param string $key     The name of the shared data
     * @return void
     */
    public function __unset($key)
    {
        $this->shared_data->remove($key);
    }
}
