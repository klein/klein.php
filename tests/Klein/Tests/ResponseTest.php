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
use \Klein\Response;
use \Klein\HttpStatus;
use \Klein\DataCollection\HeaderDataCollection;

use \Klein\Tests\Mocks\MockRequestFactory;

/**
 * ResponsesTest 
 * 
 * @uses AbstractKleinTest
 * @package Klein\Tests
 */
class ResponsesTest extends AbstractKleinTest
{

    public function testProtocolVersionGetSet()
    {
        $version_reg_ex = '/^[0-9]\.[0-9]$/';

        // Empty constructor
        $response = new Response();

        $this->assertNotNull($response->protocolVersion());
        $this->assertInternalType('string', $response->protocolVersion());
        $this->assertRegExp($version_reg_ex, $response->protocolVersion());

        // Set in method
        $response = new Response();
        $response->protocolVersion('2.0');

        $this->assertSame('2.0', $response->protocolVersion());
    }

    public function testBodyGetSet()
    {
        // Empty constructor
        $response = new Response();

        $this->assertEmpty($response->body());

        // Body set in constructor
        $response = new Response('dog');

        $this->assertSame('dog', $response->body());

        // Body set in method
        $response = new Response();
        $response->body('testing');

        $this->assertSame('testing', $response->body());
    }

    public function testCodeGetSet()
    {
        // Empty constructor
        $response = new Response();

        $this->assertNotNull($response->code());
        $this->assertInternalType('int', $response->code());

        // Code set in constructor
        $response = new Response(null, 503);

        $this->assertSame(503, $response->code());

        // Code set in method
        $response = new Response();
        $response->code(204);

        $this->assertSame(204, $response->code());
    }

    public function testStatusGetter()
    {
        $response = new Response();

        $this->assertInternalType('object', $response->status());
        $this->assertTrue($response->status() instanceof HttpStatus);
    }

    public function testHeadersGetter()
    {
        $response = new Response();

        $this->assertInternalType('object', $response->headers());
        $this->assertTrue($response->headers() instanceof HeaderDataCollection);
    }

    public function testPrepend()
    {
        $response = new Response('ein');
        $response->prepend('Kl');

        $this->assertSame('Klein', $response->body());
    }

    public function testAppend()
    {
        $response = new Response('Kl');
        $response->append('ein');

        $this->assertSame('Klein', $response->body());
    }

    public function testLockToggleAndGetters()
    {
        $response = new Response();

        $this->assertFalse($response->isLocked());

        $response->lock();

        $this->assertTrue($response->isLocked());

        $response->unlock();

        $this->assertFalse($response->isLocked());
    }

    public function testLockedNotModifiable()
    {
        $response = new Response();
        $response->lock();

        // Get initial values
        $protocol_version = $response->protocolVersion();
        $body = $response->body();
        $code = $response->code();

        // Attempt to modify
        $response->protocolVersion('2.0');
        $response->body('WOOT!');
        $response->code(204);
        $response->prepend('cat');
        $response->append('dog');

        // Assert nothing has changed
        $this->assertSame($protocol_version, $response->protocolVersion());
        $this->assertSame($body, $response->body());
        $this->assertSame($code, $response->code());
    }

    /**
     * Testing headers is a pain in the ass. ;)
     *
     * Technically... we can't. So, yea.
     */
    public function testSendHeaders()
    {
        $response = new Response('woot!');
        $response->headers()->set('test', 'sure');
        $response->headers()->set('Authorization', 'Basic asdasd');

        $response->sendHeaders();

        $this->expectOutputString(null);
    }

    public function testSendBody()
    {
        $response = new Response('woot!');
        $response->sendBody();

        $this->expectOutputString('woot!');
    }

    public function testSend()
    {
        $response = new Response('woot!');
        $response->send();

        $this->expectOutputString('woot!');
        $this->assertTrue($response->isLocked());
    }

    public function testChunk()
    {
        $content = array(
            'more',
            'content',
        );

        $response = new Response('initialll');

        $response->chunk();
        $response->chunk($content[0]);
        $response->chunk($content[1]);

        $this->expectOutputString(
            strlen($content[0])."\r\n"
            ."$content[0]\r\n"
            .strlen($content[1])."\r\n"
            ."$content[1]\r\n"
        );
    }

    public function testHeader()
    {
        $headers = array(
            'test' => 'woot!',
            'test' => 'sure',
            'okay' => 'yup',
        );

        $response = new Response();

        // Make sure the headers are initially empty
        $this->assertEmpty($response->headers()->all());

        // Set the headers
        foreach ($headers as $name => $value) {
            $response->header($name, $value);
        }

        $this->assertNotEmpty($response->headers()->all());

        // Set the headers
        foreach ($headers as $name => $value) {
            $this->assertSame($value, $response->headers()->get($name));
        }
    }

    public function testNoCache()
    {
        $response = new Response();

        // Make sure the headers are initially empty
        $this->assertEmpty($response->headers()->all());

        $response->noCache();

        $this->assertContains('no-cache', $response->headers()->all());
    }

    public function testRedirect()
    {
        $url = 'http://google.com/';
        $code = 302;

        $response = new Response();
        $response->redirect($url, $code);

        $this->assertSame($code, $response->code());
        $this->assertSame($url, $response->headers()->get('location'));
        $this->assertTrue($response->isLocked());
    }

    public function testDump()
    {
        $response = new Response();

        $this->assertEmpty($response->body());

        $response->dump('test');

        $this->assertContains('test', $response->body());
    }

    public function testJSON()
    {
        // Create a test object to be JSON encoded/decoded
        $test_object = (object) array(
            'cheese',
            'dog' => 'bacon',
            1.5 => 'should be 1 (thanks PHP casting...)',
            'integer' => 1,
            'double' => 1.5,
            '_weird' => true,
            'uniqid' => uniqid(),
        );

        $this->klein_app->respond(
            '/json',
            function ($request, $response, $service) use ($test_object) {
                $service->json($test_object);
            }
        );

        $this->klein_app->dispatch(
            MockRequestFactory::create('/json')
        );

        // Expect our output to match our json encoded test object
        $this->expectOutputString(
            json_encode($test_object)
        );

        // Assert headers were passed
        $this->assertEquals(
            'no-cache',
            $this->klein_app->response()->headers()->get('Pragma')
        );
        $this->assertEquals(
            'no-store, no-cache',
            $this->klein_app->response()->headers()->get('Cache-Control')
        );
        $this->assertEquals(
            'application/json',
            $this->klein_app->response()->headers()->get('Content-Type')
        );
    }
}
