<?php
/**
 * Klein (klein.php) - A fast & flexible router for PHP
 *
 * @author      Chris O'Hara <cohara87@gmail.com>
 * @author      Trevor Suarez (Rican7) (contributor and v2 refactorer)
 * @copyright   (c) Chris O'Hara
 * @link        https://github.com/klein/klein.php
 * @license     MIT
 */

namespace Klein\DataCollection;

/**
 * HeaderDataCollection
 *
 * A DataCollection for HTTP headers
 */
class HeaderDataCollection extends DataCollection
{

    /**
     * Constants
     */

    /**
     * Normalization option
     *
     * Don't normalize
     *
     * @type int
     */
    const NORMALIZE_NONE = 0;

    /**
     * Normalization option
     *
     * Normalize the outer whitespace of the header
     *
     * @type int
     */
    const NORMALIZE_TRIM = 1;

    /**
     * Normalization option
     *
     * Normalize the delimiters of the header
     *
     * @type int
     */
    const NORMALIZE_DELIMITERS = 2;

    /**
     * Normalization option
     *
     * Normalize the case of the header
     *
     * @type int
     */
    const NORMALIZE_CASE = 4;

    /**
     * Normalization option
     *
     * Normalize the header into canonical format
     *
     * @type int
     */
    const NORMALIZE_CANONICAL = 8;

    /**
     * Normalization option
     *
     * Normalize using all normalization techniques
     *
     * @type int
     */
    const NORMALIZE_ALL = -1;


    /**
     * Properties
     */

    /**
     * The header key normalization technique/style to
     * use when accessing headers in the collection
     *
     * @type int
     */
    protected $normalization = self::NORMALIZE_ALL;


    /**
     * Methods
     */

    /**
     * Constructor
     *
     * @override (doesn't call our parent)
     * @param array $headers        The headers of this collection
     * @param int $normalization    The header key normalization technique/style to use
     */
    public function __construct(array $headers = array(), $normalization = self::NORMALIZE_ALL)
    {
        $this->normalization = (int) $normalization;

        foreach ($headers as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Get the header key normalization technique/style to use
     *
     * @return int
     */
    public function getNormalization()
    {
        return $this->normalization;
    }

    /**
     * Set the header key normalization technique/style to use
     *
     * @param int $normalization
     * @return HeaderDataCollection
     */
    public function setNormalization($normalization)
    {
        $this->normalization = (int) $normalization;

        return $this;
    }

    /**
     * Get a header
     *
     * {@inheritdoc}
     *
     * @see DataCollection::get()
     * @param string $key           The key of the header to return
     * @param mixed  $default_val   The default value of the header if it contains no value
     * @return mixed
     */
    public function get($key, $default_val = null)
    {
        $key = $this->normalizeKey($key);

        return parent::get($key, $default_val);
    }

    /**
     * Set a header
     *
     * {@inheritdoc}
     *
     * @see DataCollection::set()
     * @param string $key   The key of the header to set
     * @param mixed  $value The value of the header to set
     * @return HeaderDataCollection
     */
    public function set($key, $value)
    {
        $key = $this->normalizeKey($key);

        return parent::set($key, $value);
    }

    /**
     * Check if a header exists
     *
     * {@inheritdoc}
     *
     * @see DataCollection::exists()
     * @param string $key   The key of the header
     * @return boolean
     */
    public function exists($key)
    {
        $key = $this->normalizeKey($key);

        return parent::exists($key);
    }

    /**
     * Remove a header
     *
     * {@inheritdoc}
     *
     * @see DataCollection::remove()
     * @param string $key   The key of the header
     * @return void
     */
    public function remove($key)
    {
        $key = $this->normalizeKey($key);

        parent::remove($key);
    }

    /**
     * Normalize a header key based on our set normalization style
     *
     * @param string $key The ("field") key of the header
     * @return string
     */
    protected function normalizeKey($key)
    {
        if ($this->normalization & static::NORMALIZE_TRIM) {
            $key = trim($key);
        }

        if ($this->normalization & static::NORMALIZE_DELIMITERS) {
            $key = static::normalizeKeyDelimiters($key);
        }

        if ($this->normalization & static::NORMALIZE_CASE) {
            $key = strtolower($key);
        }

        if ($this->normalization & static::NORMALIZE_CANONICAL) {
            $key = static::canonicalizeKey($key);
        }

        return $key;
    }

    /**
     * Normalize a header key's delimiters
     *
     * This will convert any space or underscore characters
     * to a more standard hyphen (-) character
     *
     * @param string $key The ("field") key of the header
     * @return string
     */
    public static function normalizeKeyDelimiters($key)
    {
        return str_replace(array(' ', '_'), '-', $key);
    }

    /**
     * Canonicalize a header key
     *
     * The canonical format is all lower case except for
     * the first letter of "words" separated by a hyphen
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.2
     * @param string $key The ("field") key of the header
     * @return string
     */
    public static function canonicalizeKey($key)
    {
        $words = explode('-', strtolower($key));

        foreach ($words as &$word) {
            $word = ucfirst($word);
        }

        return implode('-', $words);
    }

    /**
     * Normalize a header name by formatting it in a standard way
     *
     * This is useful since PHP automatically capitalizes and underscore
     * separates the words of headers
     *
     * @todo Possibly remove in future, here for backwards compatibility
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.2
     * @param string $name              The name ("field") of the header
     * @param boolean $make_lowercase   Whether or not to lowercase the name
     * @deprecated Use the normalization options and the other normalization methods instead
     * @return string
     */
    public static function normalizeName($name, $make_lowercase = true)
    {
        // Warn user of deprecation
        trigger_error(
            'Use the normalization options and the other normalization methods instead.',
            E_USER_DEPRECATED
        );

        /**
         * Lowercasing header names allows for a more uniform appearance,
         * however header names are case-insensitive by specification
         */
        if ($make_lowercase) {
            $name = strtolower($name);
        }

        // Do some formatting and return
        return str_replace(
            array(' ', '_'),
            '-',
            trim($name)
        );
    }
}
