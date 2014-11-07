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

/**
 * ResponseCookie
 *
 * Class to represent an HTTP response cookie
 */
class ResponseCookie
{

    /**
     * Class properties
     */

    /**
     * The name of the cookie
     *
     * @type string
     */
    protected $name;

    /**
     * The string "value" of the cookie
     *
     * @type string
     */
    protected $value;

    /**
     * The date/time that the cookie should expire
     *
     * Represented by a Unix "Timestamp"
     *
     * @type int
     */
    protected $expire;

    /**
     * The path on the server that the cookie will
     * be available on
     *
     * @type string
     */
    protected $path;

    /**
     * The domain that the cookie is available to
     *
     * @type string
     */
    protected $domain;

    /**
     * Whether the cookie should only be transferred
     * over an HTTPS connection or not
     *
     * @type boolean
     */
    protected $secure;

    /**
     * Whether the cookie will be available through HTTP
     * only (not available to be accessed through
     * client-side scripting languages like JavaScript)
     *
     * @type boolean
     */
    protected $http_only;


    /**
     * Methods
     */

    /**
     * Constructor
     *
     * @param string  $name         The name of the cookie
     * @param string  $value        The value to set the cookie with
     * @param int     $expire       The time that the cookie should expire
     * @param string  $path         The path of which to restrict the cookie
     * @param string  $domain       The domain of which to restrict the cookie
     * @param boolean $secure       Flag of whether the cookie should only be sent over a HTTPS connection
     * @param boolean $http_only    Flag of whether the cookie should only be accessible over the HTTP protocol
     */
    public function __construct(
        $name,
        $value = null,
        $expire = null,
        $path = null,
        $domain = null,
        $secure = false,
        $http_only = false
    ) {
        // Initialize our properties
        $this->setName($name);
        $this->setValue($value);
        $this->setExpire($expire);
        $this->setPath($path);
        $this->setDomain($domain);
        $this->setSecure($secure);
        $this->setHttpOnly($http_only);
    }

    /**
     * Gets the cookie's name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Sets the cookie's name
     *
     * @param string $name
     * @return ResponseCookie
     */
    public function setName($name)
    {
        $this->name = (string) $name;

        return $this;
    }

    /**
     * Gets the cookie's value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * Sets the cookie's value
     *
     * @param string $value
     * @return ResponseCookie
     */
    public function setValue($value)
    {
        if (null !== $value) {
            $this->value = (string) $value;
        } else {
            $this->value = $value;
        }

        return $this;
    }

    /**
     * Gets the cookie's expire time
     *
     * @return int
     */
    public function getExpire()
    {
        return $this->expire;
    }
    
    /**
     * Sets the cookie's expire time
     *
     * The time should be an integer
     * representing a Unix timestamp
     *
     * @param int $expire
     * @return ResponseCookie
     */
    public function setExpire($expire)
    {
        if (null !== $expire) {
            $this->expire = (int) $expire;
        } else {
            $this->expire = $expire;
        }

        return $this;
    }

    /**
     * Gets the cookie's path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * Sets the cookie's path
     *
     * @param string $path
     * @return ResponseCookie
     */
    public function setPath($path)
    {
        if (null !== $path) {
            $this->path = (string) $path;
        } else {
            $this->path = $path;
        }

        return $this;
    }

    /**
     * Gets the cookie's domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }
    
    /**
     * Sets the cookie's domain
     *
     * @param string $domain
     * @return ResponseCookie
     */
    public function setDomain($domain)
    {
        if (null !== $domain) {
            $this->domain = (string) $domain;
        } else {
            $this->domain = $domain;
        }

        return $this;
    }

    /**
     * Gets the cookie's secure only flag
     *
     * @return boolean
     */
    public function getSecure()
    {
        return $this->secure;
    }
    
    /**
     * Sets the cookie's secure only flag
     *
     * @param boolean $secure
     * @return ResponseCookie
     */
    public function setSecure($secure)
    {
        $this->secure = (boolean) $secure;

        return $this;
    }

    /**
     * Gets the cookie's HTTP only flag
     *
     * @return boolean
     */
    public function getHttpOnly()
    {
        return $this->http_only;
    }
    
    /**
     * Sets the cookie's HTTP only flag
     *
     * @param boolean $http_only
     * @return ResponseCookie
     */
    public function setHttpOnly($http_only)
    {
        $this->http_only = (boolean) $http_only;

        return $this;
    }
}
