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

use Klein\HttpStatus;

/**
 * HttpStatusTests
 */
class HttpStatusTests extends AbstractKleinTest
{

    public function testStaticMessageFromCode()
    {
        // Set our test data
        $code = 404;
        $message = 'Not Found'; // HTTP 1.1 404 status message

        $this->assertSame($message, HttpStatus::getMessageFromCode($code));
    }

    public function testManualEntryViaConstructor()
    {
        // Set our manual test data
        $code = 666;
        $message = 'The devil\'s mark';

        $http_status = new HttpStatus($code, $message);

        $this->assertSame($code, $http_status->getCode());
        $this->assertSame($message, $http_status->getMessage());
    }

    public function testManualEntryViaSetters()
    {
        // Set our manual test data
        $constructor_code = 123;
        $code = 666;
        $message = 'The devil\'s mark';

        $http_status = new HttpStatus($constructor_code);
        $http_status->setCode($code);
        $http_status->setMessage($message);

        $this->assertNotSame($constructor_code, $http_status->getCode());
        $this->assertSame($code, $http_status->getCode());
        $this->assertSame($message, $http_status->getMessage());
    }

    public function testAutomaticMessage()
    {
        $code = 201;
        $expected_message = 'Created';

        $http_status = new HttpStatus($code);

        $this->assertSame($code, $http_status->getCode());
        $this->assertSame($expected_message, $http_status->getMessage());
    }

    public function testStringOutput()
    {
        // Set our manual test data
        $code = 404;
        $expected_string = '404 Not Found';

        // Create and echo our status
        $http_status = new HttpStatus($code);
        echo $http_status;

        $this->expectOutputString($expected_string);

        $this->assertSame($expected_string, $http_status->getFormattedString());
    }
}
