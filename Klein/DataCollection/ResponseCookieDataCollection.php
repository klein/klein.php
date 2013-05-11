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

use Klein\ResponseCookie;

/**
 * ResponseCookieDataCollection
 *
 * A DataCollection for HTTP response cookies
 *
 * @uses        DataCollection
 * @package     Klein\DataCollection
 */
class ResponseCookieDataCollection extends DataCollection
{

    /**
     * Methods
     */

    /**
     * Constructor
     *
     * @override (doesn't call our parent)
     * @param array $cookies The cookies of this collection
     * @access public
     */
    public function __construct(array $cookies = array())
    {
        foreach ($cookies as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Set a cookie
     *
     * {@inheritdoc}
     *
     * A value may either be a string or a ResponseCookie instance
     * String values will be converted into a ResponseCookie with
     * the "name" of the cookie being set from the "key"
     *
     * Obviously, the developer is free to organize this collection
     * however they like, and can be more explicit by passing a more
     * suggested "$key" as the cookie's "domain" and passing in an
     * instance of a ResponseCookie as the "$value"
     *
     * @see DataCollection::set()
     * @param string $key                   The name of the cookie to set
     * @param ResponseCookie|string $value  The value of the cookie to set
     * @access public
     * @return ResponseCookieDataCollection
     */
    public function set($key, $value)
    {
        if (!$value instanceof ResponseCookie) {
            $value = new ResponseCookie($key, $value);
        }

        return parent::set($key, $value);
    }
}
