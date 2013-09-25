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
 * RouteFactory
 *
 * The default implementation of the AbstractRouteFactory
 *
 * @uses AbstractRouteFactory
 * @package     Klein
 */
class RouteFactory extends AbstractRouteFactory
{

    /**
     * Pre-process a path string
     *
     * This method wraps the path string in a regular expression syntax baesd
     * on whether the string is a catch-all or custom regular expression.
     * It also adds the namespace in a specific part, based on the style of expression
     *
     * @param string $path
     * @access protected
     * @return string
     */
    protected function preprocessPathString($path)
    {
        // If a custom regular expression (or negated custom regex)
        if ($this->namespace && $path[0] === '@' || ($path[0] === '!' && $path[1] === '@')) {
            // Is it negated?
            if ($path[0] === '!') {
                $negate = true;
                $path = substr($path, 2);
            } else {
                $negate = false;
                $path = substr($path, 1);
            }

            // Regex anchored to front of string
            if ($path[0] === '^') {
                $path = substr($path, 1);
            } else {
                $path = '.*' . $path;
            }

            if ($negate) {
                $path = '@^' . $this->namespace . '(?!' . $path . ')';
            } else {
                $path = '@^' . $this->namespace . $path;
            }

        } elseif ($this->namespace && ('*' === $path)) {
            // Empty route with namespace is a match-all
            $path = '@^' . $this->namespace . '(/|$)';
        } else {
            // Just prepend our namespace
            $path = $this->namespace . $path;
        }

        return $path;
    }

    /**
     * Build a Route instance
     *
     * @param callable $callback    Callable callback method to execute on route match
     * @param string $path          Route URI path to match
     * @param string|array $method  HTTP Method to match
     * @param boolean $count_match  Whether or not to count the route as a match when counting total matches
     * @param string $name          The name of the route
     * @static
     * @access public
     * @return Route
     */
    public function build($callback, $path = '*', $method = null, $count_match = true, $name = null)
    {
        // Only consider a request to be matched when not using matchall
        $count_match = ($path !== '*');

        $path = $this->preprocessPathString($path);

        return new Route($callback, $path, $method, $count_match);
    }
}
