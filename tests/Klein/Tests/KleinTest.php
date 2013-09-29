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

namespace Klein\Tests;

use Exception;
use Klein\App;
use Klein\DataCollection\RouteCollection;
use Klein\Exceptions\DispatchHaltedException;
use Klein\Klein;
use Klein\Request;
use Klein\Response;
use Klein\Route;
use Klein\ServiceProvider;
use OutOfBoundsException;

/**
 * KleinTest 
 *
 * @uses AbstractKleinTest
 * @package Klein\Tests
 */
class KleinTest extends AbstractKleinTest
{

    /**
     * Constants
     */

    const TEST_CALLBACK_MESSAGE = 'yay';


    /**
     * Helpers
     */

    protected function getTestCallable($message = self::TEST_CALLBACK_MESSAGE)
    {
        return function () use ($message) {
            return $message;
        };
    }


    /**
     * Tests
     */

    public function testConstructor()
    {
        $klein = new Klein();

        $this->assertNotNull($klein);
        $this->assertTrue($klein instanceof Klein);
    }

    public function testService()
    {
        $service = $this->klein_app->service();

        $this->assertNotNull($service);
        $this->assertTrue($service instanceof ServiceProvider);
    }

    public function testApp()
    {
        $app = $this->klein_app->app();

        $this->assertNotNull($app);
        $this->assertTrue($app instanceof App);
    }

    public function testRoutes()
    {
        $routes = $this->klein_app->routes();

        $this->assertNotNull($routes);
        $this->assertTrue($routes instanceof RouteCollection);
    }

    public function testRequest()
    {
        $this->klein_app->dispatch();

        $request = $this->klein_app->request();

        $this->assertNotNull($request);
        $this->assertTrue($request instanceof Request);
    }

    public function testResponse()
    {
        $this->klein_app->dispatch();

        $response = $this->klein_app->response();

        $this->assertNotNull($response);
        $this->assertTrue($response instanceof Response);
    }

    public function testRespond()
    {
        $route = $this->klein_app->respond($this->getTestCallable());

        $object_id = spl_object_hash($route);

        $this->assertNotNull($route);
        $this->assertTrue($route instanceof Route);
        $this->assertTrue($this->klein_app->routes()->exists($object_id));
        $this->assertSame($route, $this->klein_app->routes()->get($object_id));
    }

    /**
     * Weird PHPUnit bug is causing scope errors for the
     * isolated process tests, unless I run this also in an
     * isolated process
     *
     * @runInSeparateProcess
     */
    public function testWith()
    {
        // Test data
        $test_context = $this;
        $test_namespace = '/test/namespace';
        $test_routes_include = __DIR__ .'/routes/random.php';

        // Test callable and scope
        $this->klein_app->with(
            $test_namespace,
            function ($context) use ($test_context) {
                $test_context->assertTrue($context instanceof Klein);
            }
        );

        // Test file include
        $this->assertEmpty($this->klein_app->routes()->all());
        $this->klein_app->with($test_namespace, $test_routes_include);

        $this->assertNotEmpty($this->klein_app->routes()->all());

        $all_routes = array_values($this->klein_app->routes()->all());
        $test_route = $all_routes[0];

        $this->assertTrue($test_route instanceof Route);
        $this->assertSame($test_namespace . '/?', $test_route->getPath());
    }

    public function testDispatch()
    {
        $request = new Request();
        $response = new Response();

        $this->klein_app->dispatch($request, $response);

        $this->assertSame($request, $this->klein_app->request());
        $this->assertSame($response, $this->klein_app->response());
    }

    public function testGetPathFor()
    {
        // Test data
        $test_path = '/test';
        $test_name = 'Test Route Thing';

        $route = new Route($this->getTestCallable());
        $route->setPath($test_path);
        $route->setName($test_name);

        $this->klein_app->routes()->addRoute($route);

        // Make sure it fails if not prepared
        try {
            $this->klein_app->getPathFor($test_name);
        } catch (Exception $e) {
            $this->assertTrue($e instanceof OutOfBoundsException);
        }

        $this->klein_app->routes()->prepareNamed();

        $returned_path = $this->klein_app->getPathFor($test_name);

        $this->assertNotEmpty($returned_path);
        $this->assertSame($test_path, $returned_path);
    }

    public function testOnErrorWithStringCallables()
    {
        $this->klein_app->onError('test_num_args_wrapper');

        $this->klein_app->respond(
            function ($request, $response, $service) {
                throw new Exception('testing');
            }
        );

        $this->assertSame(
            '4',
            $this->dispatchAndReturnOutput()
        );
    }

    public function testOnErrorWithBadCallables()
    {
        $this->klein_app->onError('this_function_doesnt_exist');

        $this->klein_app->respond(
            function ($request, $response, $service) {
                throw new Exception('testing');
            }
        );

        $this->assertEmpty($this->klein_app->service()->flashes());

        $this->assertSame(
            null,
            $this->dispatchAndReturnOutput()
        );

        $this->assertNotEmpty($this->klein_app->service()->flashes());

        // Clean up
        session_destroy();
    }

    /**
     * @expectedException Klein\Exceptions\UnhandledException
     */
    public function testErrorsWithNoCallbacks()
    {
        $this->klein_app->respond(
            function ($request, $response, $service) {
                throw new Exception('testing');
            }
        );

        $this->klein_app->dispatch();

        $this->assertSame(
            500,
            $this->klein_app->response()->code()
        );
    }

    public function testSkipThis()
    {
        try {
            $this->klein_app->skipThis();
        } catch (Exception $e) {
            $this->assertTrue($e instanceof DispatchHaltedException);
            $this->assertSame(DispatchHaltedException::SKIP_THIS, $e->getCode());
            $this->assertSame(1, $e->getNumberOfSkips());
        }
    }

    public function testSkipNext()
    {
        $number_of_skips = 3;

        try {
            $this->klein_app->skipNext($number_of_skips);
        } catch (Exception $e) {
            $this->assertTrue($e instanceof DispatchHaltedException);
            $this->assertSame(DispatchHaltedException::SKIP_NEXT, $e->getCode());
            $this->assertSame($number_of_skips, $e->getNumberOfSkips());
        }
    }

    public function testSkipRemaining()
    {
        try {
            $this->klein_app->skipRemaining();
        } catch (Exception $e) {
            $this->assertTrue($e instanceof DispatchHaltedException);
            $this->assertSame(DispatchHaltedException::SKIP_REMAINING, $e->getCode());
        }
    }

    public function testAbort()
    {
        $test_code = 503;

        $this->klein_app->respond(
            function ($a, $b, $c, $d, $klein_app) use ($test_code) {
                $klein_app->abort($test_code);
            }
        );

        try {
            $this->klein_app->dispatch();
        } catch (Exception $e) {
            $this->assertTrue($e instanceof DispatchHaltedException);
            $this->assertSame(DispatchHaltedException::SKIP_REMAINING, $e->getCode());
        }

        $this->assertSame($test_code, $this->klein_app->response()->code());
        $this->assertTrue($this->klein_app->response()->isLocked());
    }

    public function testOptions()
    {
        $route = $this->klein_app->options($this->getTestCallable());

        $this->assertNotNull($route);
        $this->assertTrue($route instanceof Route);
        $this->assertSame('OPTIONS', $route->getMethod());
    }

    public function testHead()
    {
        $route = $this->klein_app->head($this->getTestCallable());

        $this->assertNotNull($route);
        $this->assertTrue($route instanceof Route);
        $this->assertSame('HEAD', $route->getMethod());
    }

    public function testGet()
    {
        $route = $this->klein_app->get($this->getTestCallable());

        $this->assertNotNull($route);
        $this->assertTrue($route instanceof Route);
        $this->assertSame('GET', $route->getMethod());
    }

    public function testPost()
    {
        $route = $this->klein_app->post($this->getTestCallable());

        $this->assertNotNull($route);
        $this->assertTrue($route instanceof Route);
        $this->assertSame('POST', $route->getMethod());
    }

    public function testPut()
    {
        $route = $this->klein_app->put($this->getTestCallable());

        $this->assertNotNull($route);
        $this->assertTrue($route instanceof Route);
        $this->assertSame('PUT', $route->getMethod());
    }

    public function testDelete()
    {
        $route = $this->klein_app->delete($this->getTestCallable());

        $this->assertNotNull($route);
        $this->assertTrue($route instanceof Route);
        $this->assertSame('DELETE', $route->getMethod());
    }
}
