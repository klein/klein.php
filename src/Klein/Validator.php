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

use BadMethodCallException;
use Klein\Exceptions\ValidationException;

/**
 * Validator 
 */
class Validator
{

    /**
     * Class properties
     */

    /**
     * The available validator methods
     *
     * @type array
     */
    public static $methods = array();

    /**
     * The string to validate
     *
     * @type string
     */
    protected $str;

    /**
     * The custom exception message to throw on validation failure
     *
     * @type string
     */
    protected $err;

    /**
     * Flag for whether the default validation methods have been added or not
     *
     * @type boolean
     */
    protected static $defaultAdded = false;


    /**
     * Methods
     */

    /**
     * Sets up the validator chain with the string and optional error message
     *
     * @param string $str   The string to validate
     * @param string $err   The optional custom exception message to throw on validation failure
     */
    public function __construct($str, $err = null)
    {
        $this->str = $str;
        $this->err = $err;

        if (!static::$defaultAdded) {
            static::addDefault();
        }
    }

    /**
     * Adds default validators on first use
     *
     * @return void
     */
    public static function addDefault()
    {
        static::$methods['null'] = function ($str) {
            return $str === null || $str === '';
        };
        static::$methods['len'] = function ($str, $min, $max = null) {
            $len = strlen($str);
            return null === $max ? $len === $min : $len >= $min && $len <= $max;
        };
        static::$methods['int'] = function ($str) {
            return (string)$str === ((string)(int)$str);
        };
        static::$methods['float'] = function ($str) {
            return (string)$str === ((string)(float)$str);
        };
        static::$methods['email'] = function ($str) {
            return filter_var($str, FILTER_VALIDATE_EMAIL) !== false;
        };
        static::$methods['url'] = function ($str) {
            return filter_var($str, FILTER_VALIDATE_URL) !== false;
        };
        static::$methods['ip'] = function ($str) {
            return filter_var($str, FILTER_VALIDATE_IP) !== false;
        };
        static::$methods['remoteip'] = function ($str) {
            return filter_var($str, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
        };
        static::$methods['alnum'] = function ($str) {
            return ctype_alnum($str);
        };
        static::$methods['alpha'] = function ($str) {
            return ctype_alpha($str);
        };
        static::$methods['contains'] = function ($str, $needle) {
            return strpos($str, $needle) !== false;
        };
        static::$methods['regex'] = function ($str, $pattern) {
            return preg_match($pattern, $str);
        };
        static::$methods['chars'] = function ($str, $chars) {
            return preg_match("/^[$chars]++$/i", $str);
        };

        static::$defaultAdded = true;
    }

    /**
     * Add a custom validator to our list of validation methods
     *
     * @param string $method        The name of the validator method
     * @param callable $callback    The callback to perform on validation
     * @return void
     */
    public static function addValidator($method, $callback)
    {
        static::$methods[strtolower($method)] = $callback;
    }

    /**
     * Magic "__call" method
     *
     * Allows the ability to arbitrarily call a validator with an optional prefix
     * of "is" or "not" by simply calling an instance property like a callback
     *
     * @param string $method            The callable method to execute
     * @param array $args               The argument array to pass to our callback
     * @throws BadMethodCallException   If an attempt was made to call a validator modifier that doesn't exist
     * @throws ValidationException      If the validation check returns false
     * @return Validator|boolean
     */
    public function __call($method, $args)
    {
        $reverse = false;
        $validator = $method;
        $method_substr = substr($method, 0, 2);

        if ($method_substr === 'is') {       // is<$validator>()
            $validator = substr($method, 2);
        } elseif ($method_substr === 'no') { // not<$validator>()
            $validator = substr($method, 3);
            $reverse = true;
        }

        $validator = strtolower($validator);

        if (!$validator || !isset(static::$methods[$validator])) {
            throw new BadMethodCallException('Unknown method '. $method .'()');
        }

        $validator = static::$methods[$validator];
        array_unshift($args, $this->str);

        switch (count($args)) {
            case 1:
                $result = $validator($args[0]);
                break;
            case 2:
                $result = $validator($args[0], $args[1]);
                break;
            case 3:
                $result = $validator($args[0], $args[1], $args[2]);
                break;
            case 4:
                $result = $validator($args[0], $args[1], $args[2], $args[3]);
                break;
            default:
                $result = call_user_func_array($validator, $args);
                break;
        }

        $result = (bool)($result ^ $reverse);

        if (false === $this->err) {
            return $result;
        } elseif (false === $result) {
            throw new ValidationException($this->err);
        }

        return $this;
    }
}
