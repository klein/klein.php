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

namespace Klein\Tests;

use Klein\Klein;
use Klein\Request;
use Klein\Response;
use Klein\Tests\Mocks\HeadersNoOp;
use PHPUnit_Framework_TestCase;

/**
 * AbstractKleinTest
 *
 * Base test class for PHP Unit testing
 */
abstract class AbstractKleinTest extends PHPUnit_Framework_TestCase
{

    /**
     * The automatically created test Klein instance
     * (for easy testing and less boilerplate)
     * 
     * @type Klein
     */
    protected $klein_app;


    /**
     * Setup our test
     * (runs before each test)
     * 
     * @return void
     */
    protected function setUp()
    {
        // Create a new klein app,
        // since we need one pretty much everywhere
        $this->klein_app = new Klein();
    }

    /**
     * Quick method for dispatching and returning our output from our shared Klein instance
     *
     * This is mostly useful, since the tests would otherwise have to make a bunch of calls
     * concerning the argument order and constants. DRY, bitch. ;)
     *
     * @param Request $request      Custom Klein "Request" object
     * @param Response $response    Custom Klein "Response" object
     * @return mixed The output of the dispatch call
     */
    protected function dispatchAndReturnOutput($request = null, $response = null)
    {
        return $this->klein_app->dispatch(
            $request,
            $response,
            false,
            Klein::DISPATCH_CAPTURE_AND_RETURN
        );
    }

    /**
     * Runs a callable and asserts that the output from the executed callable
     * matches the passed in expected output
     * 
     * @param mixed $expected The expected output
     * @param callable $callback The callable function
     * @param string $message (optional) A message to display if the assertion fails
     * @return void
     */
    protected function assertOutputSame($expected, $callback, $message = '')
    {
        // Start our output buffer so we can capture our output
        ob_start();

        call_user_func($callback);

        // Grab our output from our buffer
        $out = ob_get_contents();

        // Clean our buffer and destroy it, so its like no output ever happened. ;)
        ob_end_clean();

        // Use PHPUnit's built in assertion
        $this->assertSame($expected, $out, $message);
    }

    /**
     * Loads externally defined routes under the filename's namespace
     * 
     * @param Klein $app_context The application context to attach the routes to
     * @return array
     */
    protected function loadExternalRoutes(Klein $app_context = null)
    {
        // Did we not pass an instance?
        if (is_null($app_context)) {
            $app_context = $this->klein_app ?: new Klein();
        }

        $route_directory = __DIR__ . '/routes/';
        $route_files = scandir($route_directory);
        $route_namespaces = array();

        foreach ($route_files as $file) {
            if (is_file($route_directory . $file)) {
                $route_namespace = '/' . basename($file, '.php');
                $route_namespaces[] = $route_namespace;

                $app_context->with($route_namespace, $route_directory . $file);
            }
        }

        return $route_namespaces;
    }
}
