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

use Klein\DataCollection\ServerDataCollection;
use Klein\Tests\AbstractKleinTest;

/**
 * ServerDataCollectionTest
 */
class ServerDataCollectionTest extends AbstractKleinTest
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
        // Populate our sample data
        $sample_data = array(
            'DOCUMENT_ROOT' => '/cygdrive/d/Trevor/tmp',
            'REMOTE_ADDR' => '::1',
            'REMOTE_PORT' => '58526',
            'SERVER_SOFTWARE' => 'PHP 5.4.11 Development Server',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '8000',
            'REQUEST_URI' => '/test.php',
            'REQUEST_METHOD' => 'POST',
            'SCRIPT_NAME' => '/test.php',
            'SCRIPT_FILENAME' => '/cygdrive/d/Trevor/tmp/test.php',
            'PHP_SELF' => '/test.php',
            'HTTP_HOST' => 'localhost:8000',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_CONTENT_LENGTH' => '137',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.31'
                .' (KHTML, like Gecko) Chrome/26.0.1410.64 Safari/537.31',
            'HTTP_CACHE_CONTROL' => 'no-cache',
            'HTTP_ORIGIN' => 'chrome-extension://fdmmgilgnpjigdojojpjoooidkmcomcm',
            'HTTP_AUTHORIZATION' => 'Basic MTIzOjQ1Ng==',
            'HTTP_CONTENT_TYPE' => 'multipart/form-data; boundary=----WebKitFormBoundaryDhtDHBYppyHdrZe7',
            'HTTP_ACCEPT' => '*/*',
            'HTTP_ACCEPT_ENCODING' => 'gzip,deflate,sdch',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8',
            'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.3',
            'PHP_AUTH_USER' => '123',
            'PHP_AUTH_PW' => '456',
            'REQUEST_TIME_FLOAT' => 1366699956.0699,
            'REQUEST_TIME' => 1366699956,
        );

        $data_collection = new ServerDataCollection($sample_data);

        return array(
            array($sample_data, $data_collection),
        );
    }


    /*
     * Tests
     */

    public function testHasPrefix()
    {
        $this->assertTrue(ServerDataCollection::hasPrefix('dog_wierd', 'dog'));
        $this->assertTrue(ServerDataCollection::hasPrefix('_dog_wierd', '_dog'));
        $this->assertFalse(ServerDataCollection::hasPrefix('_dog_wierd', 'dog'));
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testGetHeaders($sample_data, $data_collection)
    {
        $http_headers = $data_collection->getHeaders();

        // We should have less headers than our sample data provided for key/values
        $this->assertLessThan(count($sample_data), count($http_headers));

        // Test for the stripping of the prefix
        $this->assertArrayNotHasKey('HTTP_USER_AGENT', $http_headers);
        $this->assertArrayHasKey('USER_AGENT', $http_headers);

        // Make sure non-headers didn't end up in there
        $this->assertArrayNotHasKey('REQUEST_URI', $http_headers);
    }
}
