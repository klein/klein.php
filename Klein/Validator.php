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

use \ErrorException;

use \Klein\Exceptions\ValidatorException;


/**
 * Validator 
 * 
 * @package    Klein
 */
class Validator {

    public static $_methods = array();

    protected $str = null;
    protected $err = null;

    // Sets up the validator chain with the string and optional error message
    public function __construct($str, $err = null) {
        $this->str = $str;
        $this->err = $err;
        if (empty(static::$_defaultAdded)) {
            static::addDefault();
        }
    }

    // Adds default validators on first use. See README for usage details
    public static function addDefault() {
        static::$_methods['null'] = function($str) {
            return $str === null || $str === '';
        };
        static::$_methods['len'] = function($str, $min, $max = null) {
            $len = strlen($str);
            return null === $max ? $len === $min : $len >= $min && $len <= $max;
        };
        static::$_methods['int'] = function($str) {
            return (string)$str === ((string)(int)$str);
        };
        static::$_methods['float'] = function($str) {
            return (string)$str === ((string)(float)$str);
        };
        static::$_methods['email'] = function($str) {
            return filter_var($str, FILTER_VALIDATE_EMAIL) !== false;
        };
        static::$_methods['url'] = function($str) {
            return filter_var($str, FILTER_VALIDATE_URL) !== false;
        };
        static::$_methods['ip'] = function($str) {
            return filter_var($str, FILTER_VALIDATE_IP) !== false;
        };
        static::$_methods['alnum'] = function($str) {
            return ctype_alnum($str);
        };
        static::$_methods['alpha'] = function($str) {
            return ctype_alpha($str);
        };
        static::$_methods['contains'] = function($str, $needle) {
            return strpos($str, $needle) !== false;
        };
        static::$_methods['regex'] = function($str, $pattern) {
            return preg_match($pattern, $str);
        };
        static::$_methods['chars'] = function($str, $chars) {
            return preg_match("/^[$chars]++$/i", $str);
        };
    }

    public function __call($method, $args) {
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

        if (!$validator || !isset(static::$_methods[$validator])) {
            throw new ErrorException("Unknown method $method()");
        }
        $validator = static::$_methods[$validator];
        array_unshift($args, $this->str);

        switch (count($args)) {
            case 1:  $result = $validator($args[0]); break;
            case 2:  $result = $validator($args[0], $args[1]); break;
            case 3:  $result = $validator($args[0], $args[1], $args[2]); break;
            case 4:  $result = $validator($args[0], $args[1], $args[2], $args[3]); break;
            default: $result = call_user_func_array($validator, $args); break;
        }

        $result = (bool)($result ^ $reverse);
        if (false === $this->err) {
            return $result;
        } elseif (false === $result) {
            throw new ValidatorException($this->err);
        }
        return $this;
    }

} // End class Validator
