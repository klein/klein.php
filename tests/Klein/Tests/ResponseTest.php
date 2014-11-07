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

use Klein\DataCollection\HeaderDataCollection;
use Klein\DataCollection\ResponseCookieDataCollection;
use Klein\Exceptions\LockedResponseException;
use Klein\HttpStatus;
use Klein\Klein;
use Klein\Response;
use Klein\ResponseCookie;
use Klein\Tests\Mocks\MockRequestFactory;

/**
 * ResponsesTest 
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

    public function testCookiesGetter()
    {
        $response = new Response();

        $this->assertInternalType('object', $response->cookies());
        $this->assertTrue($response->cookies() instanceof ResponseCookieDataCollection);
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
        try {
            $response->protocolVersion('2.0');
        } catch (LockedResponseException $e) {
        }

        try {
            $response->body('WOOT!');
        } catch (LockedResponseException $e) {
        }

        try {
            $response->code(204);
        } catch (LockedResponseException $e) {
        }

        try {
            $response->prepend('cat');
        } catch (LockedResponseException $e) {
        }

        try {
            $response->append('dog');
        } catch (LockedResponseException $e) {
        }


        // Assert nothing has changed
        $this->assertSame($protocol_version, $response->protocolVersion());
        $this->assertSame($body, $response->body());
        $this->assertSame($code, $response->code());
    }

    /**
     * Testing headers is a pain in the ass. ;)
     *
     * Technically... we can't. So, yea.
     *
     * Attempt to run in a separate process so we can
     * at least call our internal methods
     */
    public function testSendHeaders()
    {
        $response = new Response('woot!');
        $response->headers()->set('test', 'sure');
        $response->headers()->set('Authorization', 'Basic asdasd');

        $response->sendHeaders();

        $this->expectOutputString(null);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendHeadersInIsolateProcess()
    {
        $this->testSendHeaders();
    }

    /**
     * Testing cookies is exactly like testing headers
     * ... So, yea.
     */
    public function testSendCookies()
    {
        $response = new Response();
        $response->cookies()->set('test', 'woot!');
        $response->cookies()->set('Cookie-name', 'wtf?');

        $response->sendCookies();

        $this->expectOutputString(null);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendCookiesInIsolateProcess()
    {
        $this->testSendCookies();
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

    /**
     * @expectedException \Klein\Exceptions\ResponseAlreadySentException
     */
    public function testSendWhenAlreadySent()
    {
        $response = new Response();
        $response->send();

        $this->assertTrue($response->isLocked());

        $response->send();
    }

    /**
     * This uses some crazy exploitation to make sure that the
     * `fastcgi_finish_request()` function gets called.
     * Because of this, this MUST be run in a separate process
     *
     * @runInSeparateProcess
     */
    public function testSendCallsFastCGIFinishRequest()
    {
        // Custom fastcgi function
        implement_custom_fastcgi_function();

        $response = new Response();
        $response->send();

        $this->expectOutputString('fastcgi_finish_request');
    }

    public function testChunk()
    {
        $content = array(
            'initial content',
            'more',
            'content',
        );

        $response = new Response($content[0]);

        $response->chunk();
        $response->chunk($content[1]);
        $response->chunk($content[2]);

        $this->expectOutputString(
            dechex(strlen($content[0]))."\r\n"
            ."$content[0]\r\n"
            .dechex(strlen($content[1]))."\r\n"
            ."$content[1]\r\n"
            .dechex(strlen($content[2]))."\r\n"
            ."$content[2]\r\n"
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

    /**
     * @group testCookie
     */
    public function testCookie()
    {
        $test_cookie_data = array(
            'name'      => 'name',
            'value'    => 'value',
            'expiry'   => null,
            'path'     => '/path',
            'domain'   => 'whatever.com',
            'secure'   => true,
            'httponly' => true
        );

        $test_cookie = new ResponseCookie(
            $test_cookie_data['name'],
            $test_cookie_data['value'],
            $test_cookie_data['expiry'],
            $test_cookie_data['path'],
            $test_cookie_data['domain'],
            $test_cookie_data['secure'],
            $test_cookie_data['httponly']
        );

        $response = new Response();

        // Make sure the cookies are initially empty
        $this->assertEmpty($response->cookies()->all());

        // Set a cookies
        $response->cookie(
            $test_cookie_data['name'],
            $test_cookie_data['value'],
            $test_cookie_data['expiry'],
            $test_cookie_data['path'],
            $test_cookie_data['domain'],
            $test_cookie_data['secure'],
            $test_cookie_data['httponly']
        );

        $this->assertNotEmpty($response->cookies()->all());

        $the_cookie = $response->cookies()->get($test_cookie_data['name']);

        $this->assertNotNull($the_cookie);
        $this->assertTrue($the_cookie instanceof ResponseCookie);
        $this->assertSame($test_cookie_data['name'], $the_cookie->getName());
        $this->assertSame($test_cookie_data['value'], $the_cookie->getValue());
        $this->assertSame($test_cookie_data['path'], $the_cookie->getPath());
        $this->assertSame($test_cookie_data['domain'], $the_cookie->getDomain());
        $this->assertSame($test_cookie_data['secure'], $the_cookie->getSecure());
        $this->assertSame($test_cookie_data['httponly'], $the_cookie->getHttpOnly());
        $this->assertNotNull($the_cookie->getExpire());
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

    public function testDumpArray()
    {
        $response = new Response();

        $this->assertEmpty($response->body());

        $response->dump(array('sure', 1, 10, 17, 'ok' => 'no'));

        $this->assertNotEmpty($response->body());
        $this->assertNotEquals('<pre></pre>', $response->body());
    }

    public function testFileSend()
    {
        $file_name = 'testing';
        $file_mime = 'text/plain';

        $this->klein_app->respond(
            function ($request, $response, $service) use ($file_name, $file_mime) {
                $response->file(__FILE__, $file_name, $file_mime);
            }
        );

        $this->klein_app->dispatch();

        // Expect our output to match our json encoded test object
        $this->expectOutputString(
            file_get_contents(__FILE__)
        );

        // Assert headers were passed
        $this->assertEquals(
            $file_mime,
            $this->klein_app->response()->headers()->get('Content-Type')
        );
        $this->assertEquals(
            filesize(__FILE__),
            $this->klein_app->response()->headers()->get('Content-Length')
        );
        $this->assertContains(
            $file_name,
            $this->klein_app->response()->headers()->get('Content-Disposition')
        );
    }

    public function testFileSendLooseArgs()
    {
        $this->klein_app->respond(
            function ($request, $response, $service) {
                $response->file(__FILE__);
            }
        );

        $this->klein_app->dispatch();

        // Expect our output to match our json encoded test object
        $this->expectOutputString(
            file_get_contents(__FILE__)
        );

        // Assert headers were passed
        $this->assertEquals(
            filesize(__FILE__),
            $this->klein_app->response()->headers()->get('Content-Length')
        );
        $this->assertNotNull(
            $this->klein_app->response()->headers()->get('Content-Type')
        );
        $this->assertNotNull(
            $this->klein_app->response()->headers()->get('Content-Disposition')
        );
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
            function ($request, $response, $service) use ($test_object) {
                $response->json($test_object);
            }
        );

        $this->klein_app->dispatch();

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

    public function testJSONWithPrefix()
    {
        // Create a test object to be JSON encoded/decoded
        $test_object = array(
            'cheese',
        );
        $prefix = 'dogma';

        $this->klein_app->respond(
            function ($request, $response, $service) use ($test_object, $prefix) {
                $response->json($test_object, $prefix);
            }
        );

        $this->klein_app->dispatch();

        // Expect our output to match our json encoded test object
        $this->expectOutputString(
            'dogma('. json_encode($test_object) .');'
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
            'text/javascript',
            $this->klein_app->response()->headers()->get('Content-Type')
        );
    }
}
