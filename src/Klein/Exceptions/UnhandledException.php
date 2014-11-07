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
 * UnhandledException
 *
 * Exception used for when a exception isn't correctly handled by the Klein error callbacks
 */
class UnhandledException extends RuntimeException implements KleinExceptionInterface
{
}
