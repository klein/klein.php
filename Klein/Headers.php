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

    /**
	 * Set an HTTP header by first creating
	 * a header string from our passed values
     * 
     * @param string $key
     * @param string $value
     * @access public
     * @return void
     */
    public function header( $key, $value = null ) {
		header(
			$this->create_header_string( $key, $value )
		);
    }

    /**
     * Output an HTTP header. If $value is null, $key is
     * assume to be the HTTP response code, and the ":"
     * separator will be omitted.
     * 
     * @param string $key
     * @param string $value
     * @access public
     * @return string
     */
    public function create_header_string( $key, $value = null ) {
        if ( null === $value ) {
            return $key;
        }

        $key = str_replace( ' ', '-', ucwords( str_replace( '-', ' ', $key ) ) );
        return $key . ': ' . $value;
    }

} // End class Headers
