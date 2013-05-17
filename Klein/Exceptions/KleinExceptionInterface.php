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

/**
 * KleinExceptionInterface
 *
 * Exception interface that Klein's exceptions should implement
 *
 * This is mostly for having a simple, common Interface class/namespace
 * that can be type-hinted/instance-checked against, therefore making it
 * easier to handle Klein exceptions while still allowing the different
 * exception classes to properly extend the corresponding SPL Exception type
 *
 * @package    Klein\Exceptions
 */
interface KleinExceptionInterface
{
}
