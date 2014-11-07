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

namespace Klein\Tests\DataCollection;

use Klein\DataCollection\RouteCollection;
use Klein\Route;
use Klein\Tests\AbstractKleinTest;

/**
 * RouteCollectionTest
 */
class RouteCollectionTest extends AbstractKleinTest
{

    /*
     * Data Providers and Methods
     */

    /**
     * Sample data provider
     *
     * @return array
     */
    public function sampleDataProvider()
    {
        $sample_route = new Route(
            function () {
                echo 'woot!';
            },
            '/test/path',
            'PUT',
            true
        );

        $sample_other_route = new Route(
            function () {
                echo 'huh?';
            },
            '/test/dafuq',
            'HEAD',
            false
        );

        $sample_named_route = new Route(
            function () {
                echo 'TREVOR!';
            },
            '/trevor/is/weird',
            'OPTIONS',
            false,
            'trevor'
        );


        return array(
            array($sample_route, $sample_other_route, $sample_named_route),
        );
    }


    /*
     * Tests
     */

    /**
     * @dataProvider sampleDataProvider
     */
    public function testSet($sample_route, $sample_other_route)
    {
        // Create our collection with NO data
        $routes = new RouteCollection();

        // Set our data from our test data
        $routes->set('first', $sample_route);

        $this->assertSame($sample_route, $routes->get('first'));
        $this->assertTrue($routes->get('first') instanceof Route);
    }

    public function testSetCallableConvertsToRoute()
    {
        // Create our collection with NO data
        $routes = new RouteCollection();

        // Set our data
        $routes->set(
            'first',
            function () {
            }
        );

        $this->assertNotSame('value', $routes->get('first'));
        $this->assertTrue($routes->get('first') instanceof Route);
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testConstructorRoutesThroughAdd($sample_route, $sample_other_route)
    {
        $array_of_route_instances = array(
            $sample_route,
            $sample_other_route,
            new Route(
                function () {
                }
            ),
        );

        // Create our collection
        $routes = new RouteCollection($array_of_route_instances);
        $this->assertSame($array_of_route_instances, array_values($routes->all()));
        $this->assertNotSame(array_keys($array_of_route_instances), $routes->keys());

        foreach ($routes as $route) {
            $this->assertTrue($route instanceof Route);
        }
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testAddRoute($sample_route, $sample_other_route)
    {
        $array_of_routes = array(
            $sample_route,
            $sample_other_route,
        );

        // Create our collection
        $routes = new RouteCollection();

        foreach ($array_of_routes as $route) {
            $routes->addRoute($route);
        }

        $this->assertSame($array_of_routes, array_values($routes->all()));
    }

    public function testAddCallableConvertsToRoute()
    {
        // Create our collection with NO data
        $routes = new RouteCollection();

        $callable = function () {
        };

        // Add our data
        $routes->add($callable);

        $this->assertNotSame($callable, current($routes->all()));
        $this->assertTrue(current($routes->all()) instanceof Route);
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testPrepareNamed($sample_route, $sample_other_route, $sample_named_route)
    {
        $array_of_routes = array(
            $sample_route,
            $sample_other_route,
            $sample_named_route,
        );

        $route_name = $sample_named_route->getName();

        // Create our collection
        $routes = new RouteCollection($array_of_routes);

        $original_keys = $routes->keys();

        // Prepare the named routes
        $routes->prepareNamed();

        $this->assertNotSame($original_keys, $routes->keys());
        $this->assertSame(count($original_keys), count($routes->keys()));
        $this->assertSame($sample_named_route, $routes->get($route_name));
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testRouteOrderDoesntChangeAfterPreparing()
    {
        // Get the provided data dynamically
        $array_of_routes = func_get_args();

        // Set the number of times we should loop
        $loop_num = 10;

        // Loop a set number of times to check different permutations
        for ($i = 0; $i < $loop_num; $i++) {
            // Shuffle the sample routes array
            shuffle($array_of_routes);

            // Create our collection and prepare the routes
            $routes = new RouteCollection($array_of_routes);
            $routes->prepareNamed();

            $this->assertSame(
                array_values($routes->all()),
                array_values($array_of_routes)
            );
        }
    }
}
