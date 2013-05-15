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


use \Klein\Klein;
use \Klein\Request;
use \Klein\Response;
use \Klein\Validator;
use \Klein\ServiceProvider;
use \Klein\DataCollection\DataCollection;

/**
 * ServiceProviderTest
 *
 * @uses AbstractKleinTest
 * @package Klein\Tests
 */
class ServiceProviderTest extends AbstractKleinTest
{

    protected function getBasicServiceProvider()
    {
        return new ServiceProvider(
            $request = new Request(),
            $response = new Response()
        );
    }

    public function testConstructor()
    {
        $service = new ServiceProvider();

        // Make sure our attributes are first null
        $this->assertAttributeEquals(null, 'request', $service);
        $this->assertAttributeEquals(null, 'response', $service);

        // New service with injected dependencies
        $service = new ServiceProvider(
            $request = new Request(),
            $response = new Response()
        );

        // Make sure our attributes are set
        $this->assertAttributeEquals($request, 'request', $service);
        $this->assertAttributeEquals($response, 'response', $service);
    }

    public function testBinder()
    {
        $service = new ServiceProvider();

        // Make sure our attributes are first null
        $this->assertAttributeEquals(null, 'request', $service);
        $this->assertAttributeEquals(null, 'response', $service);

        // New service with injected dependencies
        $return_val = $service->bind(
            $request = new Request(),
            $response = new Response()
        );

        // Make sure our attributes are set
        $this->assertAttributeEquals($request, 'request', $service);
        $this->assertAttributeEquals($response, 'response', $service);

        // Make sure we're chainable
        $this->assertEquals($service, $return_val);
        $this->assertSame($service, $return_val);
    }

    public function testSharedDataGetter()
    {
        $service = new ServiceProvider();

        $this->assertInternalType('object', $service->sharedData());
        $this->assertTrue($service->sharedData() instanceof DataCollection);
    }


    /*
     * TODO: Missing all of the "session" tests
     * (not quite sure how to do that yet..)
     */


    public function testMarkdownParser()
    {
        // Test basic markdown conversion
        $this->assertSame(
            '<strong>dog</strong> <em>cat</em> <a href="src">name</a>',
            ServiceProvider::markdown('**dog** *cat* [name](src)')
        );

        // Test array arguments
        $this->assertSame(
            '<strong>huh</strong> <em>12</em> <strong>CD</strong>',
            ServiceProvider::markdown('**%s** *%d* **%X**', array('huh', '12', 205))
        );

        // Test variable number of arguments
        $this->assertSame(
            '<strong>huh</strong> <em>12</em> <strong>CD</strong>',
            ServiceProvider::markdown('**%s** *%d* **%X**', 'huh', '12', 205)
        );

        // Test second array argument overrides other arguments
        $this->assertSame(
            '<strong>huh</strong> <em>12</em> <strong>CD</strong>',
            ServiceProvider::markdown('**%s** *%d* **%X**', array('huh', '12', 205), 'dog', 'cheese')
        );
    }

    public function testEscapeCharacters()
    {
        $this->assertSame(
            'H&egrave;&egrave;&egrave;llo! A&amp;W root beer is now 20% off!!',
            ServiceProvider::escape('Hèèèllo! A&W root beer is now 20% off!!')
        );
    }

    public function testRefresh()
    {
        $this->klein_app->respond(
            function ($request, $response, $service) {
                $service->refresh();
            }
        );

        $this->klein_app->dispatch();

        $this->assertSame(
            $this->klein_app->request()->uri(),
            $this->klein_app->response()->headers()->get('location')
        );
        $this->assertTrue($this->klein_app->response()->isLocked());

        // Make sure we got a 3xx response code
        $this->assertGreaterThan(299, $this->klein_app->response()->code());
        $this->assertLessThan(400, $this->klein_app->response()->code());
    }

    public function testBack()
    {
        $url = 'http://google.com/';

        $request = new Request();
        $request->server()->set('HTTP_REFERER', $url);

        $this->klein_app->respond(
            function ($request, $response, $service) {
                $service->back();
            }
        );

        $this->klein_app->dispatch($request);

        $this->assertSame(
            $url,
            $this->klein_app->response()->headers()->get('location')
        );
        $this->assertTrue($this->klein_app->response()->isLocked());

        // Make sure we got a 3xx response code
        $this->assertGreaterThan(299, $this->klein_app->response()->code());
        $this->assertLessThan(400, $this->klein_app->response()->code());
    }

    public function testLayoutGetSet()
    {
        $test_layout = 'boom!! :D';

        $service = new ServiceProvider();

        $this->assertEmpty($service->layout());

        $service->layout($test_layout);

        $this->assertSame($test_layout, $service->layout());
    }

    /**
     * NOTE: Also tests "yield()"
     */
    public function testRender()
    {
        $test_data = array(
            'name' => 'trevor suarez',
            'title' => 'about',
            'verb' => 'woot',
        );
 
        $this->klein_app->respond(
            function ($request, $response, $service) use ($test_data) {
                // Set some data manually
                $service->sharedData()->set('name', 'should be overwritten');

                // Set our layout
                $service->layout(__DIR__.'/views/layout.php');

                // Render our view, and pass some MORE data
                $service->render(
                    __DIR__.'/views/test.php',
                    $test_data
                );
            }
        );

        $this->klein_app->dispatch();

        $this->expectOutputString(
            '<h1>About</h1>' . PHP_EOL
            .'My name is Trevor Suarez.' . PHP_EOL
            .'WOOT!' . PHP_EOL
            .'<div>footer</div>' . PHP_EOL
        );
    }

    public function testPartial()
    {
        $test_data = array(
            'name' => 'trevor suarez',
            'title' => 'about',
            'verb' => 'woot',
        );
 
        $this->klein_app->respond(
            function ($request, $response, $service) use ($test_data) {
                // Set our layout
                $service->layout(__DIR__.'/views/layout.php');

                // Render our view, and pass some MORE data
                $service->partial(
                    __DIR__.'/views/test.php',
                    $test_data
                );
            }
        );

        $this->klein_app->dispatch();

        // Make sure the layout doesn't get included
        $this->expectOutputString(
            'My name is Trevor Suarez.' . PHP_EOL
            .'WOOT!' . PHP_EOL
        );
    }

    public function testAddValidator()
    {
        $service = new ServiceProvider();

        // Initially empty
        $this->assertEmpty(Validator::$methods);

        $test_callback = function () {
            echo 'test';
        };

        $service->addValidator('awesome', $test_callback);

        $this->assertNotEmpty(Validator::$methods);
        $this->assertArrayHasKey('awesome', Validator::$methods);
        $this->assertContains($test_callback, Validator::$methods);
    }

    /**
     * @expectedException \Klein\Exceptions\ValidationException
     */
    public function testValidate()
    {
        $this->klein_app->onError(
            function ($a, $b, $c, $exception) {
                throw $exception;
            }
        );

        $this->klein_app->respond(
            function ($request, $response, $service) {
                $service->validate('thing')->isLen(3);
            }
        );

        $this->klein_app->dispatch();
    }

    /**
     * @expectedException \Klein\Exceptions\ValidationException
     */
    public function testValidateParam()
    {
        $this->klein_app->onError(
            function ($a, $b, $c, $exception) {
                throw $exception;
            }
        );

        $this->klein_app->respond(
            function ($request, $response, $service) {
                // Set a test param
                $request->paramsNamed()->set('name', 'trevor');

                $service->validateParam('name')->notNull()->isLen(3);
            }
        );

        $this->klein_app->dispatch();
    }

    // Test ALL of the magic setter, getter, exists, and removal methods
    public function testMagicGetSetExistsRemove()
    {
        $test_data = array(
            'name' => 'huh?',
        );

        $service = new ServiceProvider();

        $this->assertEmpty($service->sharedData()->all());
        $this->assertNull($service->sharedData()->get('test_data'));
        $this->assertNull($service->name);
        $this->assertFalse(isset($service->name));

        $service->name = $test_data['name'];

        $this->assertTrue(isset($service->name));
        $this->assertSame($test_data['name'], $service->name);

        unset($service->name);

        $this->assertEmpty($service->sharedData()->all());
        $this->assertNull($service->sharedData()->get('test_data'));
        $this->assertNull($service->name);
        $this->assertFalse(isset($service->name));
    }
}
