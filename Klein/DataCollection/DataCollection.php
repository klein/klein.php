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

namespace Klein\DataCollection;

use \IteratorAggregate;
use \ArrayAccess;
use \Countable;
use \ArrayIterator;

/**
 * DataCollection
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
 * @package     Klein\DataCollection
 */
class DataCollection implements IteratorAggregate, ArrayAccess, Countable
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
    protected $attributes = array();


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
     * @return DataCollection
     */
    public function set($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Replace the collection's attributes
     *
     * @param array $attributes The attributes to replace the collection's with
     * @access public
     * @return DataCollection
     */
    public function replace(array $attributes = array())
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Merge attributes with the collection's attributes
     *
     * Optionally allows a second boolean parameter to merge the attributes
     * into the collection in a "hard" manner, using the "array_replace"
     * method instead of the usual "array_merge" method
     *
     * @param array $attributes The attributes to merge into the collection
     * @param boolean $hard     Whether or not to make the merge "hard"
     * @access public
     * @return DataCollection
     */
    public function merge(array $attributes = array(), $hard = false)
    {
        // Don't waste our time with an "array_merge" call if the array is empty
        if (!empty($attributes)) {
            // Hard merge?
            if ($hard) {
                $this->attributes = array_replace(
                    $this->attributes,
                    $attributes
                );
            } else {
                $this->attributes = array_merge(
                    $this->attributes,
                    $attributes
                );
            }
        }

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
     * Allows the ability to arbitrarily request an attribute from
     * this instance while treating it as an instance property
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
     * Allows the ability to arbitrarily set an attribute from
     * this instance while treating it as an instance property
     *
     * @see set()
     * @param string $key   The name of the parameter to set
     * @param mixed  $value The value of the parameter to set
     * @access public
     * @return void
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Magic "__isset" method
     *
     * Allows the ability to arbitrarily check the existence of an attribute
     * from this instance while treating it as an instance property
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
     * Allows the ability to arbitrarily remove an attribute from
     * this instance while treating it as an instance property
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
     * Get an attribute via array syntax
     *
     * Allows the access of attributes of this instance while treating it like an array
     *
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
     * Set an attribute via array syntax
     *
     * Allows the access of attributes of this instance while treating it like an array
     *
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
     * Check existence an attribute via array syntax
     *
     * Allows the access of attributes of this instance while treating it like an array
     *
     * @see \ArrayAccess::offsetExists()
     * @see exists()
     * @param string $key   The name of the parameter
     * @access public
     * @return boolean
     */
    public function offsetExists($key)
    {
        return $this->exists($key);
    }

    /**
     * Remove an attribute via array syntax
     *
     * Allows the access of attributes of this instance while treating it like an array
     *
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
     * Count the attributes via a simple "count" call
     *
     * Allows the use of the "count" function (or any internal counters)
     * to simply count the number of attributes in the collection.
     *
     * @see \Countable::count()
     * @access public
     * @return int
     */
    public function count()
    {
        return count($this->attributes);
    }
}
