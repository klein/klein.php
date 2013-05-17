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

use \Klein\ResponseCookie;

/**
 * ResponseCookieTest
 *
 * @uses AbstractKleinTest
 * @package Klein\Tests
 */
class ResponseCookieTest extends AbstractKleinTest
{

    /*
     * Data Providers and Methods
     */

    /**
     * Sample data provider
     *
     * @access public
     * @return array
     */
    public function sampleDataProvider()
    {
        // Populate our sample data
        $default_sample_data = array(
            'name' => '',
            'value' => '',
            'expire' => 0,
            'path' => '',
            'domain' => '',
            'secure' => false,
            'http_only' => false,
        );

        $sample_data = array(
            'name' => 'Trevor',
            'value' => 'is a programmer',
            'expire' => 3600,
            'path' => '/',
            'domain' => 'example.com',
            'secure' => false,
            'http_only' => false,
        );

        $sample_data_other = array(
            'name' => 'Chris',
            'value' => 'is a boss',
            'expire' => 60,
            'path' => '/app/',
            'domain' => 'github.com',
            'secure' => true,
            'http_only' => true,
        );

        return array(
            array($default_sample_data, $sample_data, $sample_data_other),
        );
    }


    /*
     * Tests
     */

    /**
     * @dataProvider sampleDataProvider
     */
    public function testNameGetSet($defaults, $sample_data, $sample_data_other)
    {
        $response_cookie = new ResponseCookie($sample_data['name']);

        $this->assertSame($sample_data['name'], $response_cookie->getName());
        $this->assertInternalType('string', $response_cookie->getName());

        $response_cookie->setName($sample_data_other['name']);

        $this->assertSame($sample_data_other['name'], $response_cookie->getName());
        $this->assertInternalType('string', $response_cookie->getName());
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testValueGetSet($defaults, $sample_data, $sample_data_other)
    {
        $response_cookie = new ResponseCookie($defaults['name'], $sample_data['value']);

        $this->assertSame($sample_data['value'], $response_cookie->getValue());
        $this->assertInternalType('string', $response_cookie->getValue());

        $response_cookie->setValue($sample_data_other['value']);

        $this->assertSame($sample_data_other['value'], $response_cookie->getValue());
        $this->assertInternalType('string', $response_cookie->getValue());
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testExpireGetSet($defaults, $sample_data, $sample_data_other)
    {
        $response_cookie = new ResponseCookie(
            $defaults['name'],
            null,
            $sample_data['expire']
        );

        $this->assertSame($sample_data['expire'], $response_cookie->getExpire());
        $this->assertInternalType('int', $response_cookie->getExpire());

        $response_cookie->setExpire($sample_data_other['expire']);

        $this->assertSame($sample_data_other['expire'], $response_cookie->getExpire());
        $this->assertInternalType('int', $response_cookie->getExpire());
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testPathGetSet($defaults, $sample_data, $sample_data_other)
    {
        $response_cookie = new ResponseCookie(
            $defaults['name'],
            null,
            null,
            $sample_data['path']
        );

        $this->assertSame($sample_data['path'], $response_cookie->getPath());
        $this->assertInternalType('string', $response_cookie->getPath());

        $response_cookie->setPath($sample_data_other['path']);

        $this->assertSame($sample_data_other['path'], $response_cookie->getPath());
        $this->assertInternalType('string', $response_cookie->getPath());
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testDomainGetSet($defaults, $sample_data, $sample_data_other)
    {
        $response_cookie = new ResponseCookie(
            $defaults['name'],
            null,
            null,
            null,
            $sample_data['domain']
        );

        $this->assertSame($sample_data['domain'], $response_cookie->getDomain());
        $this->assertInternalType('string', $response_cookie->getDomain());

        $response_cookie->setDomain($sample_data_other['domain']);

        $this->assertSame($sample_data_other['domain'], $response_cookie->getDomain());
        $this->assertInternalType('string', $response_cookie->getDomain());
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testSecureGetSet($defaults, $sample_data, $sample_data_other)
    {
        $response_cookie = new ResponseCookie(
            $defaults['name'],
            null,
            null,
            null,
            null,
            $sample_data['secure']
        );

        $this->assertSame($sample_data['secure'], $response_cookie->getSecure());
        $this->assertInternalType('boolean', $response_cookie->getSecure());

        $response_cookie->setSecure($sample_data_other['secure']);

        $this->assertSame($sample_data_other['secure'], $response_cookie->getSecure());
        $this->assertInternalType('boolean', $response_cookie->getSecure());
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testHttpOnlyGetSet($defaults, $sample_data, $sample_data_other)
    {
        $response_cookie = new ResponseCookie(
            $defaults['name'],
            null,
            null,
            null,
            null,
            null,
            $sample_data['http_only']
        );

        $this->assertSame($sample_data['http_only'], $response_cookie->getHttpOnly());
        $this->assertInternalType('boolean', $response_cookie->getHttpOnly());

        $response_cookie->setHttpOnly($sample_data_other['http_only']);

        $this->assertSame($sample_data_other['http_only'], $response_cookie->getHttpOnly());
        $this->assertInternalType('boolean', $response_cookie->getHttpOnly());
    }
}
