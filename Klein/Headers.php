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
 * Headers 
 * 
 * @package    Klein
 */
class Headers {

    public function header($key, $value = null) {
        header($this->header($key, $value));
    }

    /**
     * Output an HTTP header. If $value is null, $key is
     * assume to be the HTTP response code, and the ":"
     * separator will be omitted.
     */
    public function _header($key, $value = null) {
        if (null === $value) {
            return $key;
        }

        $key = str_replace(' ', '-', ucwords(str_replace('-', ' ', $key)));
        return "$key: $value";
    }

} // End class Headers
