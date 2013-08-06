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
 * HttpResponseCache
 *
 * HTTP cache headers manager
 *
 * @package     Klein
 */
class HttpResponseCache
{

    /**
     * HTTP Cache-Control public indicator
     *
     * @var bool
     */
    protected $public = false;

    /**
     * HTTP Cache-Control private indicator
     *
     * @var bool
     */
    protected $private = false;

    /**
     * HTTP Cache-Control no-cache indicator
     *
     * @var bool
     */
    protected $no_cache = false;

    /**
     * HTTP Cache-Control no-store indicator
     *
     * @var bool
     */
    protected $no_store = false;

    /**
     * HTTP Cache-Control no-transform indicator
     *
     * @var bool
     */
    protected $no_transform = false;

    /**
     * HTTP Cache-Control must-revalidate indicator
     *
     * @var bool
     */
    protected $must_revalidate = false;

    /**
     * HTTP Cache-Control proxy-revalidate indicator
     *
     * @var bool
     */
    protected $proxy_revalidate = false;

    /**
     * HTTP Cache-Control max-age value
     *
     * @var int
     */
    protected $max_age = 0;

    /**
     * HTTP Cache-Control s-maxage value
     *
     * @var int
     */
    protected $s_maxage = 0;

    /**
     * HTTP Cache-Control cache-extension
     *
     * @var array
     */
    protected $extensions = array();


    /**
     * Set response public
     *
     * @param bool $public
     * @return HttpResponseCache
     */
    public function setPublic($public = true)
    {
        $this->public = (bool)$public;
        $this->private = false;
        $this->no_cache = false;

        return $this;
    }

    /**
     * Indicates whether is response public
     *
     * @return bool
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set response private
     *
     * @param bool $private
     * @return HttpResponseCache
     */
    public function setPrivate($private = true)
    {
        $this->private = (bool)$private;
        $this->public = false;
        $this->no_cache = false;

        return $this;
    }

    /**
     * Indicates whether is response private
     *
     * @return bool
     */
    public function getPrivate()
    {
        return $this->private;
    }

    /**
     * Set response non cacheable
     *
     * @param bool $no_cache
     * @return HttpResponseCache
     */
    public function setNoCache($no_cache = true)
    {
        $this->no_cache = (bool)$no_cache;
        $this->public = false;
        $this->private = false;

        return true;
    }

    /**
     * Indicates whether is response cacheable
     *
     * @return bool
     */
    public function getNoCache()
    {
        return $this->no_cache;
    }

    /**
     * Set response not storable
     *
     * @param bool $no_store
     * @return HttpResponseCache
     */
    public function setNoStore($no_store = true)
    {
        $this->no_store = (bool)$no_store;

        return $this;
    }

    /**
     * Indicates whether is response storable
     *
     * @return bool
     */
    public function getNoStore()
    {
        return $this->no_store;
    }

    /**
     * Set response cannot transform
     *
     * @param bool $no_transform
     * @return HttpResponseCache
     */
    public function setNoTransform($no_transform = true)
    {
        $this->no_transform = (bool)$no_transform;

        return $this;
    }

    /**
     * Indicates whether response can transform
     *
     * @return bool
     */
    public function getNoTransform()
    {
        return $this->no_transform;
    }

    /**
     * Set cache must revalidate stored response
     *
     * @param $must_revalidate
     * @return HttpResponseCache
     */
    public function setMustRevalidate($must_revalidate = true)
    {
        $this->must_revalidate = (bool)$must_revalidate;

        return $this;
    }

    /**
     * Indicates whether cache must revalidate stored response
     *
     * @return boolean
     */
    public function getMustRevalidate()
    {
        return $this->must_revalidate;
    }

    /**
     * Set shared cache must revalidate stored response
     *
     * @param $proxy_revalidate
     * @return HttpResponseCache
     */
    public function setProxyRevalidate($proxy_revalidate = true)
    {
        $this->proxy_revalidate = (bool)$proxy_revalidate;

        return $this;
    }

    /**
     * Indicates whether shared cache must revalidate stored response
     *
     * @return boolean
     */
    public function getProxyRevalidate()
    {
        return $this->proxy_revalidate;
    }

    /**
     * Set cache lifetime
     *
     * @param $max_age
     * @return HttpResponseCache
     */
    public function setMaxAge($max_age)
    {
        $this->max_age = (int)$max_age;

        return $this;
    }

    /**
     * Get cache lifetime
     *
     * @return int
     */
    public function getMaxAge()
    {
        return $this->max_age;
    }

    /**
     * Set shared cache lifetime
     *
     * @param $s_maxage
     * @return HttpResponseCache
     */
    public function setSMaxage($s_maxage)
    {
        $this->s_maxage = (int)$s_maxage;

        return $this;
    }

    /**
     * Get shared cache lifetime
     *
     * @return int
     */
    public function getSMaxage()
    {
        return $this->s_maxage;
    }

    /**
     * Set custom Cache-Control param
     *
     * @param string $key
     * @param string $value
     * @return HttpResponseCache
     */
    public function setExtension($key, $value = null)
    {
        $this->extensions[$key] = (string)$value;

        return $this;
    }

    /**
     * Get custom Cache-Control param
     *
     * @param string $key
     * @return string
     */
    public function getExtension($key)
    {
        return $this->extensions[$key];
    }


    /**
     * Generate header string
     *
     * @return string
     */
    public function generateCacheControlString()
    {
        $headerString = '';

        if ($this->no_cache) {
            $headerString .= ' no-cache';
        } elseif ($this->private) {
            $headerString .= ' private';
        } elseif ($this->public) {
            $headerString .= ' public';
        } else {
            $headerString .= ' no-cache';
        }

        if ($this->no_store) {
            $headerString .= ' no-store';
        }

        if ($this->no_transform) {
            $headerString .= ' no-transform';
        }

        if ($this->must_revalidate) {
            $headerString .= ' must-revalidate';
        }

        if ($this->proxy_revalidate) {
            $headerString .= ' proxy-revalidate';
        }

        if (is_integer($this->max_age)) {
            $headerString .= ' max-age=' . $this->max_age;
        }

        if (is_integer($this->s_maxage)) {
            $headerString .= ' s-maxage=' . $this->s_maxage;
        }

        foreach ($this->extensions as $extensionName => $extensionValue) {

            if ($extensionValue === '') {
                $headerString .= ' ' . $extensionName;
            } else {
                $headerString .= ' ' . $extensionName . '=' . $extensionValue;
            }

        }

        return 'Cache-Control: ' . trim($headerString);
    }

    /**
     * Send headers
     */
    public function send()
    {

        header($this->generateCacheControlString(), true);
    }
}