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

namespace Klein\Exceptions;

use Exception;
use Klein\Route;
use RuntimeException;
use Throwable;

/**
 * RoutePathCompilationException
 *
 * Exception used for when a route's path fails to compile
 */
class RoutePathCompilationException extends RuntimeException implements KleinExceptionInterface
{

    /**
     * Constants
     */

    /**
     * The exception message format
     *
     * @type string
     */
    const MESSAGE_FORMAT = 'Route failed to compile with path "%s".';

    /**
     * The extra failure message format
     *
     * @type string
     */
    const FAILURE_MESSAGE_TITLE_FORMAT = 'Failed with message: "%s"';


    /**
     * Properties
     */

    /**
     * The route that failed to compile
     *
     * @type Route
     */
    protected $route;


    /**
     * Methods
     */

    /**
     * Create a RoutePathCompilationException from a route
     * and an optional previous exception
     *
     * TODO: Change the `$previous` parameter to type-hint against `Throwable`
     * once PHP 5.x support is no longer necessary.
     *
     * @param Route $route          The route that failed to compile
     * @param Exception|Throwable $previous   The previous exception
     * @return RoutePathCompilationException
     */
    public static function createFromRoute(Route $route, $previous = null)
    {
        $error = (null !== $previous) ? $previous->getMessage() : null;
        $code  = (null !== $previous) ? $previous->getCode() : null;

        $message = sprintf(static::MESSAGE_FORMAT, $route->getPath());
        $message .= ' '. sprintf(static::FAILURE_MESSAGE_TITLE_FORMAT, $error);

        $exception = new static($message, $code, $previous);
        $exception->setRoute($route);

        return $exception;
    }

    /**
     * Gets the value of route
     *
     * @sccess public
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Sets the value of route
     *
     * @param Route The route that failed to compile
     * @sccess protected
     * @return RoutePathCompilationException
     */
    protected function setRoute(Route $route)
    {
        $this->route = $route;

        return $this;
    }
}
