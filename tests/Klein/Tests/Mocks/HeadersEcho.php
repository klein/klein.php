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

class HeadersEcho extends Headers
{

    public function header($key, $value = null)
    {
        echo $this->createHeaderString($key, $value) . "\n";
    }
}
