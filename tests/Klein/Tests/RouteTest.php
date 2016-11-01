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

namespace Klein\Tests;

use InvalidArgumentException;
use Klein\Klein;
use Klein\Route;

/**
 * RouteTest
 */
class RouteTest extends AbstractKleinTest
{

    protected function getTestCallable()
    {
        return function () {
            echo 'dog';
        };
    }

    public function testCallbackGetSet()
    {
        // Test functions
        $test_callable = $this->getTestCallable();
        $test_class_callable = __NAMESPACE__ . '\Mocks\TestClass::GET';

        // Callback set in constructor
        $route = new Route($test_callable);

        $this->assertSame($test_callable, $route->getCallback());
        $this->assertInternalType('callable', $route->getCallback());

        // Callback set in method
        $route = new Route($test_callable);
        $route->setCallback($test_class_callable);

        $this->assertSame($test_class_callable, $route->getCallback());
        $this->assertInternalType('callable', $route->getCallback());
    }

    public function testPathGetSet()
    {
        // Test data
        $test_callable = $this->getTestCallable();
        $test_path = '/this-is-a-path';

        // Empty constructor
        $route = new Route($test_callable);

        $this->assertNotNull($route->getPath());
        $this->assertInternalType('string', $route->getPath());

        // Set in constructor
        $route = new Route($test_callable, $test_path);

        $this->assertSame($test_path, $route->getPath());

        // Set in method
        $route = new Route($test_callable);
        $route->setPath($test_path);

        $this->assertSame($test_path, $route->getPath());
    }

    public function testMethodGetSet()
    {
        // Test data
        $test_callable = $this->getTestCallable();
        $test_method_string = 'POST';
        $test_method_array = array('POST', 'PATCH');

        // Empty constructor
        $route = new Route($test_callable);

        $this->assertNull($route->getMethod());

        // Set in constructor
        $route = new Route($test_callable, null, $test_method_string);

        $this->assertSame($test_method_string, $route->getMethod());

        // Set in method
        $route = new Route($test_callable);
        $route->setMethod($test_method_array);

        $this->assertSame($test_method_array, $route->getMethod());
    }

    public function testCountMatchGetSet()
    {
        // Test data
        $test_callable = $this->getTestCallable();
        $test_count_match = false;

        // Empty constructor
        $route = new Route($test_callable);

        $this->assertTrue($route->getCountMatch());

        // Set in constructor
        $route = new Route($test_callable, null, null, $test_count_match);

        $this->assertSame($test_count_match, $route->getCountMatch());

        // Set in count_match
        $route = new Route($test_callable);
        $route->setCountMatch($test_count_match);

        $this->assertSame($test_count_match, $route->getCountMatch());
    }

    public function testNameGetSet()
    {
        // Test data
        $test_callable = $this->getTestCallable();
        $test_name = 'trevor';

        // Empty constructor
        $route = new Route($test_callable);

        $this->assertNull($route->getName());

        // Set in constructor
        $route = new Route($test_callable, null, null, null, $test_name);

        $this->assertSame($test_name, $route->getName());

        // Set in method
        $route = new Route($test_callable);
        $route->setName($test_name);

        $this->assertSame($test_name, $route->getName());
    }

    public function testInvokeMethod()
    {
        // Test data
        $test_callable = function ($id, $name) {
            return array($id, $name);
        };
        $test_arguments = array(7, 'Trevor');

        $route = new Route($test_callable);

        $this->assertSame(
            call_user_func_array($test_callable, $test_arguments),
            call_user_func_array($route, $test_arguments)
        );
    }

    /**
     * Exception tests
     */

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCallbackSetWithIncorrectType()
    {
        $route = new Route($this->getTestCallable());

        // Test setting with the WRONG type
        $route->setCallback(100);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMethodSetWithIncorrectType()
    {
        $route = new Route($this->getTestCallable());

        // Test setting with the WRONG type
        $route->setMethod(100);
    }
}
