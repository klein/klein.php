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

use \Klein\Exceptions\LockedResponseException;
use \Klein\Exceptions\UnhandledException;
use \Klein\Exceptions\ResponseAlreadySentException;

/**
 * Klein
 *
 * Main Klein router class
 * 
 * @package     Klein
 */
class Klein
{

    /**
     * Class properties
     */

    /**
     * Dispatch route output handling
     *
     * Don't capture anything. Behave as normal.
     *
     * @const int
     */
    const DISPATCH_NO_CAPTURE = 0;

    /**
     * Dispatch route output handling
     *
     * Capture all output and return it from dispatch
     *
     * @const int
     */
    const DISPATCH_CAPTURE_AND_RETURN = 1;

    /**
     * Dispatch route output handling
     *
     * Capture all output and replace the response body with it
     *
     * @const int
     */
    const DISPATCH_CAPTURE_AND_REPLACE = 2;

    /**
     * Dispatch route output handling
     *
     * Capture all output and prepend it to the response body
     *
     * @const int
     */
    const DISPATCH_CAPTURE_AND_PREPEND = 3;

    /**
     * Dispatch route output handling
     *
     * Capture all output and append it to the response body
     *
     * @const int
     */
    const DISPATCH_CAPTURE_AND_APPEND = 4;


    /**
     * Class properties
     */

    /**
     * Array of the routes to match on dispatch
     *
     * @var array
     * @access protected
     */
    protected $routes;

    /**
     * The namespace of which to collect the routes in
     * when matching, so you can define routes under a
     * common endpoint
     *
     * @var string
     * @access protected
     */
    protected $namespace;

    /**
     * An array of error callback callables
     *
     * @var array[callable]
     * @access protected
     */
    protected $errorCallbacks = array();


    /**
     * Route objects
     */

    /**
     * The Request object passed to each matched route
     *
     * @var Request
     * @access protected
     */
    protected $request;

    /**
     * The Response object passed to each matched route
     *
     * @var Response
     * @access protected
     */
    protected $response;

    /**
     * The service provider object passed to each matched route
     *
     * @var ServiceProvider
     * @access protected
     */
    protected $service;

    /**
     * A generic variable passed to each matched route
     *
     * @var mixed
     * @access protected
     */
    protected $app;


    /**
     * Methods
     */

    /**
     * Constructor
     *
     * Create a new Klein instance with optionally injected dependencies
     * This DI allows for easy testing, object mocking, or class extension
     *
     * @param ServiceProvider $service  Service provider object responsible for utilitarian behaviors
     * @param mixed $app                    An object passed to each route callback, defaults to a new App instance
     * @access public
     */
    public function __construct(ServiceProvider $service = null, $app = null)
    {
        // Instanciate our routing objects
        $this->service = $service ?: new ServiceProvider();
        $this->app     = $app     ?: new App();
    }

    /**
     * Returns the request object
     *
     * @access public
     * @return Request
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * Returns the response object
     *
     * @access public
     * @return Response
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * Returns the service object
     *
     * @access public
     * @return ServiceProvider
     */
    public function service()
    {
        return $this->service;
    }

    /**
     * Returns the app object
     *
     * @access public
     * @return mixed
     */
    public function app()
    {
        return $this->app;
    }

    /**
     * Add a new route to be matched on dispatch
     *
     * This method takes its arguments in a very loose format
     * The only "required" parameter is the callback (which is very strange considering the argument definition order)
     *
     * <code>
     * $router = new Klein();
     *
     * $router->respond( function() {
     *     echo 'this works';
     * });
     * $router->respond( '/endpoint', function() {
     *     echo 'this also works';
     * });
     * $router->respond( 'POST', '/endpoint', function() {
     *     echo 'this also works!!!!';
     * });
     * </code>
     *
     * @param string | array $method    HTTP Method to match
     * @param string $route             Route URI to match
     * @param callable $callback        Callable callback method to execute on route match
     * @access public
     * @return callable $callback
     */
    public function respond($method, $route = '*', $callback = null)
    {
        $args = func_get_args();
        $callback = array_pop($args);
        $route = array_pop($args);
        $method = array_pop($args);

        if (null === $route) {
            $route = '*';
        }

        // only consider a request to be matched when not using matchall
        $count_match = ($route !== '*');

        if ($this->namespace && $route[0] === '@' || ($route[0] === '!' && $route[1] === '@')) {
            if ($route[0] === '!') {
                $negate = true;
                $route = substr($route, 2);
            } else {
                $negate = false;
                $route = substr($route, 1);
            }

            // regex anchored to front of string
            if ($route[0] === '^') {
                $route = substr($route, 1);
            } else {
                $route = '.*' . $route;
            }

            if ($negate) {
                $route = '@^' . $this->namespace . '(?!' . $route . ')';
            } else {
                $route = '@^' . $this->namespace . $route;
            }

        } elseif ($this->namespace && ('*' === $route)) {
            // empty route with namespace is a match-all
            $route = '@^' . $this->namespace . '(/|$)';
        } else {
            $route = $this->namespace . $route;
        }

        $this->routes[] = array($method, $route, $callback, $count_match);

        return $callback;
    }

    /**
     * Collect a set of routes under a common namespace
     *
     * The routes may be passed in as either a callable (which holds the route definitions),
     * or as a string of a filename, of which to "include" under the Klein router scope
     *
     * <code>
     * $router = new Klein();
     *
     * $router->with('/users', function() use ( $router) {
     *     $router->respond( '/', function() {
     *         // do something interesting
     *     });
     *     $router->respond( '/[i:id]', function() {
     *         // do something different
     *     });
     * });
     *
     * $router->with('/cars', __DIR__ . '/routes/cars.php');
     * </code>
     *
     * @param string $namespace                     The namespace under which to collect the routes
     * @param callable | string[filename] $routes   The defined routes to collect under the namespace
     * @access public
     * @return void
     */
    public function with($namespace, $routes)
    {
        $previous = $this->namespace;
        $this->namespace .= $namespace;

        if (is_callable($routes)) {
            $routes();
        } else {
            require $routes;
        }

        $this->namespace = $previous;
    }

    /**
     * Dispatch the request to the approriate route(s)
     *
     * Dispatch with optionally injected dependencies
     * This DI allows for easy testing, object mocking, or class extension
     *
     * @param Request $request          The request object to give to each callback
     * @param Response $response        The response object to give to each callback
     * @param boolean $send_response    Whether or not to "send" the response after the last route has been matched
     * @param int $capture              Specify a DISPATCH_* constant to change the output capturing behavior
     * @access public
     * @return void|string
     */
    public function dispatch(
        Request $request = null,
        Response $response = null,
        $send_response = true,
        $capture = self::DISPATCH_NO_CAPTURE
    ) {
        // Set/Initialize our objects to be sent in each callback
        $this->request = $request ?: Request::createFromGlobals();
        $this->response = $response ?: new Response();

        // Bind our objects to our service
        $this->service->bind($this->request, $this->response);


        // Grab some data from the request
        $uri = $this->request->uri(true); // Strip the query string
        $req_method = $this->request->method();

        // Set up some variables for matching
        $matched = 0;
        $methods_matched = array();
        $params = array();
        $apc = function_exists('apc_fetch');

        ob_start();

        foreach ($this->routes as $handler) {
            list($method, $_route, $callback, $count_match) = $handler;

            // Keep track of whether this specific request method was matched
            $method_match = null;

            // Was a method specified? If so, check it against the current request method
            if (is_array($method)) {
                foreach ($method as $test) {
                    if (strcasecmp($req_method, $test) === 0) {
                        $method_match = true;
                    } elseif (strcasecmp($req_method, 'HEAD') === 0
                          && (strcasecmp($test, 'HEAD') === 0 || strcasecmp($test, 'GET') === 0)) {

                        // Test for HEAD request (like GET)
                        $method_match = true;
                    }
                }

                if (null === $method_match) {
                    $method_match = false;
                }
            } elseif (null !== $method && strcasecmp($req_method, $method) !== 0) {
                $method_match = false;

                // Test for HEAD request (like GET)
                if (strcasecmp($req_method, 'HEAD') === 0
                    && (strcasecmp($method, 'HEAD') === 0 || strcasecmp($method, 'GET') === 0 )) {

                    $method_match = true;
                }
            } elseif (null !== $method && strcasecmp($req_method, $method) === 0) {
                $method_match = true;
            }

            // If the method was matched or if it wasn't even passed (in the route callback)
            $possible_match = is_null($method_match) || $method_match;

            // ! is used to negate a match
            if (isset($_route[0]) && $_route[0] === '!') {
                $negate = true;
                $i = 1;
            } else {
                $negate = false;
                $i = 0;
            }

            // Check for a wildcard (match all)
            if ($_route === '*') {
                $match = true;

            } elseif ($_route === '404' && !$matched && count($methods_matched) <= 0) {
                // Easily handle 404's

                try {
                    $this->response->append(
                        call_user_func(
                            $callback,
                            $this->request,
                            $this->response,
                            $this->service,
                            $this->app,
                            $matched,
                            $methods_matched
                        )
                    );
                } catch (LockedResponseException $e) {
                    // Do nothing, since this is an automated behavior
                } catch (Exception $e) {
                    $this->error($e);
                }

                ++$matched;
                continue;

            } elseif ($_route === '405' && !$matched && count($methods_matched) > 0) {
                // Easily handle 405's

                try {
                    $this->response->append(
                        call_user_func(
                            $callback,
                            $this->request,
                            $this->response,
                            $this->service,
                            $this->app,
                            $matched,
                            $methods_matched
                        )
                    );
                } catch (LockedResponseException $e) {
                    // Do nothing, since this is an automated behavior
                } catch (Exception $e) {
                    $this->error($e);
                }

                ++$matched;
                continue;

            } elseif (isset($_route[$i]) && $_route[$i] === '@') {
                // @ is used to specify custom regex

                $match = preg_match('`' . substr($_route, $i + 1) . '`', $uri, $params);

            } else {
                // Compiling and matching regular expressions is relatively
                // expensive, so try and match by a substring first

                $route = null;
                $regex = false;
                $j = 0;
                $n = isset($_route[$i]) ? $_route[$i] : null;

                // Find the longest non-regex substring and match it against the URI
                while (true) {
                    if (!isset($_route[$i])) {
                        break;
                    } elseif (false === $regex) {
                        $c = $n;
                        $regex = $c === '[' || $c === '(' || $c === '.';
                        if (false === $regex && false !== isset($_route[$i+1])) {
                            $n = $_route[$i + 1];
                            $regex = $n === '?' || $n === '+' || $n === '*' || $n === '{';
                        }
                        if (false === $regex && $c !== '/' && (!isset($uri[$j]) || $c !== $uri[$j])) {
                            continue 2;
                        }
                        $j++;
                    }
                    $route .= $_route[$i++];
                }

                // Check if there's a cached regex string
                if (false !== $apc) {
                    $regex = apc_fetch("route:$route");
                    if (false === $regex) {
                        $regex = $this->compileRoute($route);
                        apc_store("route:$route", $regex);
                    }
                } else {
                    $regex = $this->compileRoute($route);
                }

                $match = preg_match($regex, $uri, $params);
            }

            if (isset($match) && $match ^ $negate) {
                // Keep track of possibly matched methods
                $methods_matched = array_merge($methods_matched, (array) $method);
                $methods_matched = array_filter($methods_matched);
                $methods_matched = array_unique($methods_matched);

                if ($possible_match) {
                    if (!empty($params)) {
                        $this->request->paramsNamed()->merge($params);
                    }

                    // Try and call our route's callback
                    try {
                        $this->response->append(
                            call_user_func(
                                $callback,
                                $this->request,
                                $this->response,
                                $this->service,
                                $this->app,
                                $matched,
                                $methods_matched
                            )
                        );
                    } catch (LockedResponseException $e) {
                        // Do nothing, since this is an automated behavior
                    } catch (Exception $e) {
                        $this->error($e);
                    }

                    if ($_route !== '*') {
                        $count_match && ++$matched;
                    }
                }
            }
        }

        try {
            if (!$matched && count($methods_matched) > 0) {
                if (strcasecmp($req_method, 'OPTIONS') !== 0) {
                    $this->response->code(405);
                }

                $this->response->header('Allow', implode(', ', $methods_matched));
            } elseif (!$matched) {
                $this->response->code(404);
            }

            if ($this->response->chunked) {
                $this->response->chunk();

            } else {
                // Output capturing behavior
                switch($capture) {
                    case self::DISPATCH_CAPTURE_AND_RETURN:
                        return ob_get_clean();
                        break;
                    case self::DISPATCH_CAPTURE_AND_REPLACE:
                        $this->response->body(ob_get_clean());
                        break;
                    case self::DISPATCH_CAPTURE_AND_PREPEND:
                        $this->response->prepend(ob_get_clean());
                        break;
                    case self::DISPATCH_CAPTURE_AND_APPEND:
                        $this->response->append(ob_get_clean());
                        break;
                    case self::DISPATCH_NO_CAPTURE:
                    default:
                        ob_end_flush();
                        break;
                }
            }

            // Test for HEAD request (like GET)
            if (strcasecmp($req_method, 'HEAD') === 0) {
                // HEAD requests shouldn't return a body
                $this->response->body('');
                ob_clean();
            }
        } catch (LockedResponseException $e) {
            // Do nothing, since this is an automated behavior
        }

        if ($send_response && !$this->response->isSent()) {
            $this->response->send();
        }
    }

    /**
     * Compiles a route string to a regular expression
     *
     * @param string $route     The route string to compile
     * @access protected
     * @return void
     */
    protected function compileRoute($route)
    {
        if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER)) {
            $match_types = array(
                'i'  => '[0-9]++',
                'a'  => '[0-9A-Za-z]++',
                'h'  => '[0-9A-Fa-f]++',
                '*'  => '.+?',
                '**' => '.++',
                ''   => '[^/]+?'
            );
            foreach ($matches as $match) {
                list($block, $pre, $type, $param, $optional) = $match;

                if (isset($match_types[$type])) {
                    $type = $match_types[$type];
                }
                if ($pre === '.') {
                    $pre = '\.';
                }
                // Older versions of PCRE require the 'P' in (?P<named>)
                $pattern = '(?:'
                         . ($pre !== '' ? $pre : null)
                         . '('
                         . ($param !== '' ? "?P<$param>" : null)
                         . $type
                         . '))'
                         . ($optional !== '' ? '?' : null);

                $route = str_replace($block, $pattern, $route);
            }
        }
        return "`^$route$`";
    }

    /**
     * Adds an error callback to the stack of error handlers
     *
     * @param callable $callback            The callable function to execute in the error handling chain
     * @access public
     * @return boolean|void
     */
    public function onError($callback)
    {
        $this->errorCallbacks[] = $callback;
    }

    /**
     * Routes an exception through the error callbacks
     *
     * @param Exception $err    The exception that occurred
     * @access protected
     * @return void
     */
    protected function error(Exception $err)
    {
        $type = get_class($err);
        $msg = $err->getMessage();

        if (count($this->errorCallbacks) > 0) {
            foreach (array_reverse($this->errorCallbacks) as $callback) {
                if (is_callable($callback)) {
                    if (is_string($callback)) {
                        if ($callback($this, $msg, $type, $err)) {
                            return;
                        }
                    } else {
                        if (call_user_func($callback, $this, $msg, $type, $err)) {
                            return;
                        }
                    }
                } else {
                    if (null !== $this->service && null !== $this->response) {
                        $this->service->flash($err);
                        $this->response->redirect($callback);
                    }
                }
            }
        } else {
            $this->response->code(500);
            throw new UnhandledException($err);
        }
    }


    /**
     * Method aliases
     */

    /**
     * GET alias for "respond()"
     *
     * @param string $route
     * @param callable $callback
     * @access public
     * @return callable
     */
    public function get($route = '*', $callback = null)
    {
        $args = func_get_args();
        $callback = array_pop($args);
        $route = array_pop($args);

        return $this->respond('GET', $route, $callback);
    }

    /**
     * POST alias for "respond()"
     *
     * @param string $route
     * @param callable $callback
     * @access public
     * @return callable
     */
    public function post($route = '*', $callback = null)
    {
        $args = func_get_args();
        $callback = array_pop($args);
        $route = array_pop($args);

        return $this->respond('POST', $route, $callback);
    }

    /**
     * PUT alias for "respond()"
     *
     * @param string $route
     * @param callable $callback
     * @access public
     * @return callable
     */
    public function put($route = '*', $callback = null)
    {
        $args = func_get_args();
        $callback = array_pop($args);
        $route = array_pop($args);

        return $this->respond('PUT', $route, $callback);
    }

    /**
     * DELETE alias for "respond()"
     *
     * @param string $route
     * @param callable $callback
     * @access public
     * @return callable
     */
    public function delete($route = '*', $callback = null)
    {
        $args = func_get_args();
        $callback = array_pop($args);
        $route = array_pop($args);

        return $this->respond('DELETE', $route, $callback);
    }
}
