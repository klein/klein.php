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


use \Klein\Headers;

use \Klein\Tests\Mocks\HeadersEcho;

/**
 * HeadersTest 
 * 
 * @uses AbstractKleinTest
 * @package Klein\Tests
 */
class HeadersTest extends AbstractKleinTest
{

    public function setUp()
    {
        parent::setUp();

        $this->headers = new HeadersEcho;
    }

    public function testResponseCode()
    {
        $this->expectOutputString("HTTP/1.1 404 Not Found\n");
        $this->headers->header('HTTP/1.1 404 Not Found');
    }

    public function testBlankHeader()
    {
        $this->expectOutputString("Foo: \n");
        $this->headers->header('Foo', '');
    }

    public function testHeaderKeyValue()
    {
        $this->expectOutputString("Foo: Bar\n");
        $this->headers->header('Foo', 'Bar');
    }

    public function testHeaderKeyTransform()
    {
        $this->expectOutputString("Foo-Bar: baz\n");
        $this->headers->header('foo bar', 'baz');
    }
}
