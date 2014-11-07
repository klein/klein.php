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

use InvalidArgumentException;

/**
 * Route
 *
 * Class to represent a route definition
 */
class Route
{

    /**
     * Properties
     */

    /**
     * The callback method to execute when the route is matched
     *
     * Any valid "callable" type is allowed
     *
     * @link http://php.net/manual/en/language.types.callable.php
     * @type callable
     */
    protected $callback;

    /**
     * The URL path to match
     *
     * Allows for regular expression matching and/or basic string matching
     *
     * Examples:
     * - '/posts'
     * - '/posts/[:post_slug]'
     * - '/posts/[i:id]'
     *
     * @type string
     */
    protected $path;

    /**
     * The HTTP method to match
     *
     * May either be represented as a string or an array containing multiple methods to match
     *
     * Examples:
     * - 'POST'
     * - array('GET', 'POST')
     *
     * @type string|array
     */
    protected $method;

    /**
     * Whether or not to count this route as a match when counting total matches
     *
     * @type boolean
     */
    protected $count_match;

    /**
     * The name of the route
     *
     * Mostly used for reverse routing
     *
     * @type string
     */
    protected $name;


    /**
     * Methods
     */

    /**
     * Constructor
     *
     * @param callable $callback
     * @param string $path
     * @param string|array $method
     * @param boolean $count_match
     */
    public function __construct($callback, $path = null, $method = null, $count_match = true, $name = null)
    {
        // Initialize some properties (use our setters so we can validate param types)
        $this->setCallback($callback);
        $this->setPath($path);
        $this->setMethod($method);
        $this->setCountMatch($count_match);
        $this->setName($name);
    }

    /**
     * Get the callback
     *
     * @return callable
     */
    public function getCallback()
    {
        return $this->callback;
    }
    
    /**
     * Set the callback
     *
     * @param callable $callback
     * @throws InvalidArgumentException If the callback isn't a callable
     * @return Route
     */
    public function setCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('Expected a callable. Got an uncallable '. gettype($callback));
        }

        $this->callback = $callback;

        return $this;
    }

    /**
     * Get the path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * Set the path
     *
     * @param string $path
     * @return Route
     */
    public function setPath($path)
    {
        $this->path = (string) $path;

        return $this;
    }

    /**
     * Get the method
     *
     * @return string|array
     */
    public function getMethod()
    {
        return $this->method;
    }
    
    /**
     * Set the method
     *
     * @param string|array|null $method
     * @throws InvalidArgumentException If a non-string or non-array type is passed
     * @return Route
     */
    public function setMethod($method)
    {
        // Allow null, otherwise expect an array or a string
        if (null !== $method && !is_array($method) && !is_string($method)) {
            throw new InvalidArgumentException('Expected an array or string. Got a '. gettype($method));
        }

        $this->method = $method;

        return $this;
    }

    /**
     * Get the count_match
     *
     * @return boolean
     */
    public function getCountMatch()
    {
        return $this->count_match;
    }
    
    /**
     * Set the count_match
     *
     * @param boolean $count_match
     * @return Route
     */
    public function setCountMatch($count_match)
    {
        $this->count_match = (boolean) $count_match;

        return $this;
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set the name
     *
     * @param string $name
     * @return Route
     */
    public function setName($name)
    {
        if (null !== $name) {
            $this->name = (string) $name;
        } else {
            $this->name = $name;
        }

        return $this;
    }


    /**
     * Magic "__invoke" method
     *
     * Allows the ability to arbitrarily call this instance like a function
     *
     * @param mixed $args Generic arguments, magically accepted
     * @return mixed
     */
    public function __invoke($args = null)
    {
        $args = func_get_args();

        return call_user_func_array(
            $this->callback,
            $args
        );
    }
}
