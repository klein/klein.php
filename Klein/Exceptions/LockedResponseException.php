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
 * LockedResponseException
 *
 * Exception used for when a response is attempted to be modified while its locked
 * 
 * @uses       RuntimeException
 * @package    Klein\Exceptions
 */
class LockedResponseException extends RuntimeException implements KleinExceptionInterface
{
}
