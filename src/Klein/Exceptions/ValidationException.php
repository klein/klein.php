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

use \UnexpectedValueException;

/**
 * ValidationException 
 *
 * Exception used for Validation errors
 * 
 * @uses       Exception
 * @package    Klein\Exceptions
 */
class ValidationException extends UnexpectedValueException implements KleinExceptionInterface
{
}
