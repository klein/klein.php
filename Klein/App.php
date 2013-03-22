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
 * App 
 * 
 * @package    Klein
 */
class App {

    protected $services = array();

    // Check for a lazy service
    public function __get($name) {
        if (!isset($this->services[$name])) {
            throw new InvalidArgumentException("Unknown service $name");
        }
        $service = $this->services[$name];
        return $service();
    }

    // Call a class property like a method
    public function __call($method, $args) {
        if (!isset($this->$method) || !is_callable($this->$method)) {
            throw new ErrorException("Unknown method $method()");
        }
        return call_user_func_array($this->$method, $args);
    }

    // Register a lazy service
    public function register($name, $closure) {
        if (isset($this->services[$name])) {
            throw new Exception("A service is already registered under $name");
        }
        $this->services[$name] = function() use ($closure) {
            static $instance;
            if (null === $instance) {
                $instance = $closure();
            }
            return $instance;
        };
    }

} // End class App
