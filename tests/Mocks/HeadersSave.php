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

namespace Klein\Tests\Mocks;

use \Klein\Headers;

class HeadersSave extends Headers
{
    public $headers_values = array();

    public function __construct(&$headers_array_ref)
    {
        $this->headers_values = &$headers_array_ref;
    }

    public function header($key, $value = null)
    {
        $this->headers_values[] = $this->createHeaderString($key, $value) . "\n";

        return $this->headers_values;
    }
}
