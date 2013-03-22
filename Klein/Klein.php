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

use \_Request;
use \_Response;
use \_App;

/**
 * Klein 
 *
 * Main Klein router class
 * 
 * @package		Klein
 */
class Klein {

    /**
     * Class properties
     */
    protected $_routes;
    protected $_namespace;

    public function	__construct() {
    }

    function respond($method, $route = '*', $callback = null) {
        $args = func_get_args();
        $callback = array_pop($args);
        $route = array_pop($args);
        $method = array_pop($args);

        if (null === $route) {
            $route = '*';
        }

        // only consider a request to be matched when not using matchall
        $count_match = ($route !== '*');

        if ($this->_namespace && $route[0] === '@' || ($route[0] === '!' && $route[1] === '@')) {
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
                $route = '@^' . $this->_namespace . '(?!' . $route . ')';
            } else {
                $route = '@^' . $this->_namespace . $route;
            }
        }
        // empty route with namespace is a match-all
        elseif ($this->_namespace && ('*' === $route)) {
            $route = '@^' . $this->_namespace . '(/|$)';
        } else {
            $route = $this->_namespace . $route;
        }

        $this->_routes[] = array($method, $route, $callback, $count_match);

        return $callback;
    }

    function with($namespace, $routes) {
        $previous = $this->_namespace;
        $this->_namespace .= $namespace;

        if (is_callable($routes)) {
            $routes();
        } else {
            require $routes;
        }

        $this->_namespace = $previous;
    }

    function startSession() {
        if (session_id() === '') {
            session_start();
        }
    }

    // Dispatch the request to the approriate route(s)
    function dispatch($uri = null, $req_method = null, array $params = null, $capture = false) {
        // Pass $request, $response, and a blank object for sharing scope through each callback
        $request  = new _Request;
        $response = new _Response;
        $app      = new _App;

        // Get/parse the request URI and method
        if (null === $uri) {
            $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        }
        if (false !== strpos($uri, '?')) {
            $uri = strstr($uri, '?', true);
        }
        if (null === $req_method) {
            $req_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

            // For legacy servers, override the HTTP method with the X-HTTP-Method-Override
            // header or _method parameter
            if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
                $req_method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
            } else if (isset($_REQUEST['_method'])) {
                $req_method = $_REQUEST['_method'];
            }
        }

        // Force request_order to be GP
        // http://www.mail-archive.com/internals@lists.php.net/msg33119.html
        $_REQUEST = array_merge($_GET, $_POST);
        if (null !== $params) {
            $_REQUEST = array_merge($_REQUEST, $params);
        }

        $matched = 0;
        $methods_matched = array();
        $apc = function_exists('apc_fetch');

        ob_start();

        foreach ($this->_routes as $handler) {
            list($method, $_route, $callback, $count_match) = $handler;

            $method_match = null;
            // Was a method specified? If so, check it against the current request method
            if (is_array($method)) {
                foreach ($method as $test) {
                    if (strcasecmp($req_method, $test) === 0) {
                        $method_match = true;
                    }
                }
                if (null === $method_match) {
                  $method_match = false;
                }
            } elseif (null !== $method && strcasecmp($req_method, $method) !== 0) {
               $method_match = false;
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

            // Easily handle 404's
            } elseif ($_route === '404' && !$matched && count($methods_matched) <= 0) {
                try {
                    call_user_func($callback, $request, $response, $app, $matched, $methods_matched);
                } catch (Exception $e) {
                    $response->error($e);
                }

                ++$matched;
                continue;

            // Easily handle 405's
            } elseif ($_route === '405' && !$matched && count($methods_matched) > 0) {
                try {
                    call_user_func($callback, $request, $response, $app, $matched, $methods_matched);
                } catch (Exception $e) {
                    $response->error($e);
                }

                ++$matched;
                continue;

            // @ is used to specify custom regex
            } elseif (isset($_route[$i]) && $_route[$i] === '@') {
                $match = preg_match('`' . substr($_route, $i + 1) . '`', $uri, $params);

            // Compiling and matching regular expressions is relatively
            // expensive, so try and match by a substring first
            } else {
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
                        $regex = compile_route($route);
                        apc_store("route:$route", $regex);
                    }
                } else {
                    $regex = compile_route($route);
                }

                $match = preg_match($regex, $uri, $params);
            }

            if (isset($match) && $match ^ $negate) {
                 // Keep track of possibly matched methods
                 $methods_matched = array_merge($methods_matched, (array) $method);
                 $methods_matched = array_filter($methods_matched);
                 $methods_matched = array_unique($methods_matched);

                 if ($possible_match) {
                      if (null !== $params) {
                           $_REQUEST = array_merge($_REQUEST, $params);
                      }
                      try {
                           call_user_func($callback, $request, $response, $app, $matched, $methods_matched);
                      } catch (Exception $e) {
                           $response->error($e);
                      }
                      if ($_route !== '*') {
                           $count_match && ++$matched;
                      }
                 }
            }
        }

        if (!$matched && count($methods_matched) > 0) {
            $response->code(405);
            $response->header('Allow', implode(', ', $methods_matched));
        } elseif (!$matched) {
            $response->code(404);
        }

        if ($capture) {
            return ob_get_clean();
        } elseif ($response->chunked) {
            $response->chunk();
        } else {
            ob_end_flush();
        }
    }

    // Compiles a route string to a regular expression
    function compile_route($route) {
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

} // End class Klein
