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

use \BadMethodCallException;

use \Klein\Exceptions\UnknownServiceException;
use \Klein\Exceptions\DuplicateServiceException;

/**
 * App 
 * 
 * @package    Klein
 */
class App
{

    /**
     * Class properties
     */

    /**
     * The array of app services
     *
     * @var array
     * @access protected
     */
    protected $services = array();

    /**
     * Magic "__get" method
     *
     * Allows the ability to arbitrarily request a service from this instance
     * while treating it as an instance property
     *
     * This checks the lazy service register and automatically calls the registered
     * service method
     *
     * @param string $name  The name of the service
     * @access public
     * @return mixed
     */
    public function __get($name)
    {
        if (!isset($this->services[$name])) {
            throw new UnknownServiceException('Unknown service '. $name);
        }
        $service = $this->services[$name];

        return $service();
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
            throw new BadMethodCallException('Unknown method '. $method .'()');
        }

        return call_user_func_array($this->$method, $args);
    }

    /**
     * Register a lazy service
     *
     * @param string $name          The name of the service
     * @param callable $closure     The callable function to execute when requesting our service
     * @access public
     * @return mixed
     */
    public function register($name, $closure)
    {
        if (isset($this->services[$name])) {
            throw new DuplicateServiceException('A service is already registered under '. $name);
        }

        $this->services[$name] = function () use ($closure) {
            static $instance;
            if (null === $instance) {
                $instance = $closure();
            }

            return $instance;
        };
    }
}
