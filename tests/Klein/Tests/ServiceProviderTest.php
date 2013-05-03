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

    public function testCallServiceThroughKlein()
    {
        // Make sure the calls are the same
        $this->assertSame(
            $this->klein_app->sharedData(),
            $this->klein_app->service()->sharedData()
        );
    }
}
