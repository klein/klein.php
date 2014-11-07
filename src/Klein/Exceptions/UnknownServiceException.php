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

use OutOfBoundsException;

/**
 * UnknownServiceException
 *
 * Exception used for when a service was called that doesn't exist
 */
class UnknownServiceException extends OutOfBoundsException implements KleinExceptionInterface
{
}
