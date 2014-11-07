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

namespace Klein\Exceptions;

use RuntimeException;

/**
 * HttpException
 *
 * An HTTP error exception
 */
class HttpException extends RuntimeException implements HttpExceptionInterface
{

    /**
     * Methods
     */

    /**
     * Create an HTTP exception from nothing but an HTTP code
     *
     * @param int $code
     * @return HttpException
     */
    public static function createFromCode($code)
    {
        return new static(null, (int) $code);
    }
}
