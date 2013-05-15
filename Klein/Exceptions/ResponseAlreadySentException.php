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

namespace Klein\Exceptions;

use \RuntimeException;

/**
 * ResponseAlreadySentException
 *
 * Exception used for when a response is attempted to be sent after its already been sent
 * 
 * @uses       RuntimeException
 * @package    Klein\Exceptions
 */
class ResponseAlreadySentException extends RuntimeException implements KleinExceptionInterface
{
}
