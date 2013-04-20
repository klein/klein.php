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

use \IteratorAggregate;
use \ArrayAccess;
use \Countable;
use \ArrayIterator;

/**
 * HttpDataCollection
 *
 * A generic collection class to contain array-like data, specifically
 * designed to work with HTTP data (request params, session data, etc)
 *
 * Inspired by @fabpot's Symfony 2's HttpFoundation
 * @link https://github.com/symfony/HttpFoundation/blob/master/ParameterBag.php
 *
 * @uses        IteratorAggregate
 * @uses        ArrayAccess
 * @uses        Countable
 * @package     Klein
 */
class HttpDataCollection implements IteratorAggregate, ArrayAccess, Countable
{

    /**
     * Class properties
     */

    /**
     * Collection of data attributes
     *
     * @var array
     * @access protected
     */
    protected $attributes;


    /**
     * Methods
     */

    /**
     * Constructor
     *
     * @param array $attributes The data attributes of this collection
     * @access public
     */
    public function __construct(array $attributes = array())
    {
        $this->attributes = $attributes;
    }

    /**
     * Returns all of the attributes in the collection
     *
     * If an optional mask array is passed, this only
     * returns the keys that match the mask
     *
     * @param array $mask  The parameter mask array
     * @access public
     * @return array
     */
    public function all($mask = null)
    {
        if (!is_null($mask)) {
            // Support a more "magical" call
            if (!is_array($mask)) {
                $mask = func_get_args();
            }

            /*
             * Remove all of the keys from the attributes
             * that aren't in the passed mask
             */
            $attributes = array_intersect_key(
                $this->attributes,
                array_flip($mask)
            );

            /*
             * Make sure that each key in the mask has at least a
             * null value, since the user will expect the key to exist
             */
            foreach ($mask as $key) {
                if (!isset($attributes[$key])) {
                    $attributes[$key] = null;
                }
            }

            return $attributes;
        }

        return $this->attributes;
    }

    /**
     * Return an attribute of the collection
     *
     * Return a default value if the key doesn't exist
     *
     * @param string $key           The name of the parameter to return
     * @param mixed  $default_val   The default value of the parameter if it contains no value
     * @access public
     * @return mixed
     */
    public function get($key, $default_val = null)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        return $default_val;
    }

    /**
     * Set an attribute of the collection
     *
     * @param string $key   The name of the parameter to set
     * @param mixed  $value The value of the parameter to set
     * @access public
     * @return HttpDataCollection
     */
    public function set($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * See if an attribute exists in the collection
     *
     * @param string $key   The name of the parameter
     * @access public
     * @return boolean
     */
    public function exists($key)
    {
        // Don't use "isset", since it returns false for null values
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Remove an attribute from the collection
     *
     * @param string $key   The name of the parameter
     * @access public
     * @return void
     */
    public function remove($key)
    {
        unset($this->attributes[$key]);
    }


    /*
     * Magic method implementations
     */

    /**
     * Magic "__get" method
     *
     * @see get()
     * @param string $key   The name of the parameter to return
     * @access public
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Magic "__set" method
     *
     * @see set()
     * @param string $key   The name of the parameter to set
     * @param mixed  $value The value of the parameter to set
     * @access public
     * @return HttpDataCollection
     */
    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * Magic "__isset" method
     *
     * @see exists()
     * @param string $key   The name of the parameter
     * @access public
     * @return boolean
     */
    public function __isset($key)
    {
        return $this->exists($key);
    }

    /**
     * Magic "__unset" method
     *
     * @see remove()
     * @param string $key   The name of the parameter
     * @access public
     * @return void
     */
    public function __unset($key)
    {
        $this->remove($key);
    }


    /*
     * Interface required method implementations
     */

    /**
     * Get the aggregate iterator
     *
     * IteratorAggregate interface required method
     *
     * @see \IteratorAggregate::getIterator()
     * @access public
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->attributes);
    }

    /**
     * @see \ArrayAccess::offsetGet()
     * @see get()
     * @param string $key   The name of the parameter to return
     * @access public
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * @see \ArrayAccess::offsetSet()
     * @see set()
     * @param string $key   The name of the parameter to set
     * @param mixed  $value The value of the parameter to set
     * @access public
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * @see \ArrayAccess::offsetIsset()
     * @see exists()
     * @param string $key   The name of the parameter
     * @access public
     * @return boolean
     */
    public function offsetIsset($key)
    {
        return $this->exists($key);
    }

    /**
     * @see \ArrayAccess::offsetUnset()
     * @see remove()
     * @param string $key   The name of the parameter
     * @access public
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    /**
     * @see \Countable::count()
     * @access public
     * @return int
     */
    public function count()
    {
        return count($this->attributes);
    }
}
