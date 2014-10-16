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

use DateTime;

/**
 * ResponseCookie
 *
 * Class to represent an HTTP response cookie
 *
 * @package     Klein
 */
class ResponseCookie
{
    /**
     * Class properties
     */

    /**
     * The name of the cookie
     *
     * @var string
     * @access protected
     */
    protected $name;

    /**
     * The string "value" of the cookie
     *
     * @var string
     * @access protected
     */
    protected $value;

    /**
     * The date/time that the cookie should expire
     *
     * @var DateTime
     */
    protected $expiration;

    /**
     * The path on the server that the cookie will
     * be available on
     *
     * @var string
     * @access protected
     */
    protected $path;

    /**
     * The domain that the cookie is available to
     *
     * @var string
     * @access protected
     */
    protected $domain;

    /**
     * Whether the cookie should only be transferred
     * over an HTTPS connection or not
     *
     * @var boolean
     * @access protected
     */
    protected $secure;

    /**
     * Whether the cookie will be available through HTTP
     * only (not available to be accessed through
     * client-side scripting languages like JavaScript)
     *
     * @var boolean
     * @access protected
     */
    protected $http_only;

    /**
     * Methods
     */

    /**
     * @param string $name The name of the cookie
     * @param string $value The value to set the cookie with
     * @param DateTime $expiration The date/time that the cookie should expire
     * @param string $path The path of which to restrict the cookie
     * @param string $domain The domain of which to restrict the cookie
     * @param boolean $secure Flag of whether the cookie should only be sent over a HTTPS connection
     * @param boolean $http_only Flag of whether the cookie should only be accessible over the HTTP protocol
     * @todo once the setExpire|getExpire methods are removed, we should type-hint the expiration
     */
    public function __construct(
        $name,
        $value = null,
        $expiration = null,
        $path = null,
        $domain = null,
        $secure = false,
        $http_only = false
    ) {
        $this->setName($name);
        $this->setValue($value);
        if ($expiration instanceof DateTime) {
            $this->setExpiration($expiration);
        } elseif (null !== $expiration) {
            $this->setExpire($expiration);
        }
        $this->setPath($path);
        $this->setDomain($domain);
        $this->setSecure($secure);
        $this->setHttpOnly($http_only);
    }

    /**
     * Gets the cookie's name
     *
     * @access public
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
     * @access public
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
     * @access public
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
     * @access public
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
     * @return int
     * @deprecated
     */
    public function getExpire()
    {
        // Warn user of deprecation
        trigger_error(
            'Use of ResponseCookie::getExpire() and ResponseCookie::setExpire() is deprecated. ' .
            'Use ResponseCookie::getExpiration() and ResponseCookie::setExpiration() instead.',
            E_USER_DEPRECATED
        );
        return $this->expiration->getTimestamp();
    }

    /**
     * Gets the cookie's expire time
     *
     * @return DateTime
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @param int $expire
     * @return $this
     * @deprecated
     */
    public function setExpire($expire)
    {
        // Warn user of deprecation
        trigger_error(
            'Use of ResponseCookie::getExpire() and ResponseCookie::setExpire() is deprecated. ' .
            'Use ResponseCookie::getExpiration() and ResponseCookie::setExpiration() instead.',
            E_USER_DEPRECATED
        );

        if (null !== $expire) {
            $this->expiration = new DateTime((int)$expire);
        } else {
            $this->expiration = null;
        }

        return $this;
    }

    /**
     * Sets the cookie's expiration date/time
     *
     * @param DateTime $expiration
     * @return $this
     */
    public function setExpiration(DateTime $expiration)
    {
        $this->expiration = $expiration;
        return $this;
    }

    /**
     * Removes the cookie's expiration date/time
     *
     * @return $this
     */
    public function removeExpiration()
    {
        $this->expiration = null;
        return $this;
    }

    /**
     * Gets the cookie's path
     *
     * @access public
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
     * @access public
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
     * @access public
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
     * @access public
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
     * @access public
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
     * @access public
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
     * @access public
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
     * @access public
     * @return ResponseCookie
     */
    public function setHttpOnly($http_only)
    {
        $this->http_only = (boolean) $http_only;

        return $this;
    }
}
