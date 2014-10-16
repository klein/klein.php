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

use DateTime;
use Klein\ResponseCookie;

/**
 * ResponseCookieTest
 *
 * @uses AbstractKleinTest
 * @package Klein\Tests
 */
class ResponseCookieTest extends AbstractKleinTest
{
    /**
     * Sample data provider
     *
     * @return array
     */
    public function sampleDataProvider()
    {
        $defaults = array(
            'name' => '',
            'value' => '',
            'expiration' => null,
            'path' => '',
            'domain' => '',
            'secure' => false,
            'http_only' => false,
        );

        $sampleData = array(
            'name' => 'Trevor',
            'value' => 'is a programmer',
            'expiration' => new DateTime(time() + 3600),
            'path' => '/',
            'domain' => 'example.com',
            'secure' => false,
            'http_only' => false,
        );

        $sampleDataOther = array(
            'name' => 'Chris',
            'value' => 'is a boss',
            'expiration' => new DateTime(time() + 60),
            'path' => '/app/',
            'domain' => 'github.com',
            'secure' => true,
            'http_only' => true,
        );

        return array(
            array($defaults, $sampleData, $sampleDataOther),
        );
    }

    /**
     * @dataProvider sampleDataProvider
     * @param array $defaults
     * @param array $sampleData
     * @param array $sampleDataOther
     */
    public function testNameGetSet($defaults, $sampleData, $sampleDataOther)
    {
        $responseCookie = new ResponseCookie($sampleData['name']);

        $this->assertSame($sampleData['name'], $responseCookie->getName());
        $this->assertInternalType('string', $responseCookie->getName());

        $responseCookie->setName($sampleDataOther['name']);

        $this->assertSame($sampleDataOther['name'], $responseCookie->getName());
        $this->assertInternalType('string', $responseCookie->getName());
    }

    /**
     * @dataProvider sampleDataProvider
     * @param array $defaults
     * @param array $sampleData
     * @param array $sampleDataOther
     */
    public function testValueGetSet($defaults, $sampleData, $sampleDataOther)
    {
        $responseCookie = new ResponseCookie($defaults['name'], $sampleData['value']);

        $this->assertSame($sampleData['value'], $responseCookie->getValue());
        $this->assertInternalType('string', $responseCookie->getValue());

        $responseCookie->setValue($sampleDataOther['value']);

        $this->assertSame($sampleDataOther['value'], $responseCookie->getValue());
        $this->assertInternalType('string', $responseCookie->getValue());
    }

    /**
     * @dataProvider sampleDataProvider
     * @param array $defaults
     * @param array $sampleData
     * @param array $sampleDataOther
     */
    public function testExpireGetSet($defaults, $sampleData, $sampleDataOther)
    {
        $responseCookie = new ResponseCookie(
            $defaults['name'],
            null,
            $sampleData['expire']
        );

        $this->assertSame($sampleData['expire'], $responseCookie->getExpire());
        $this->assertInternalType('int', $responseCookie->getExpire());

        $responseCookie->setExpire($sampleDataOther['expire']);

        $this->assertSame($sampleDataOther['expire'], $responseCookie->getExpire());
        $this->assertInternalType('int', $responseCookie->getExpire());
    }

    /**
     * @dataProvider sampleDataProvider
     * @param array $defaults
     * @param array $sampleData
     * @param array $sampleDataOther
     */
    public function testPathGetSet($defaults, $sampleData, $sampleDataOther)
    {
        $responseCookie = new ResponseCookie(
            $defaults['name'],
            null,
            null,
            $sampleData['path']
        );

        $this->assertSame($sampleData['path'], $responseCookie->getPath());
        $this->assertInternalType('string', $responseCookie->getPath());

        $responseCookie->setPath($sampleDataOther['path']);

        $this->assertSame($sampleDataOther['path'], $responseCookie->getPath());
        $this->assertInternalType('string', $responseCookie->getPath());
    }

    /**
     * @dataProvider sampleDataProvider
     * @param array $defaults
     * @param array $sampleData
     * @param array $sampleDataOther
     */
    public function testDomainGetSet($defaults, $sampleData, $sampleDataOther)
    {
        $responseCookie = new ResponseCookie(
            $defaults['name'],
            null,
            null,
            null,
            $sampleData['domain']
        );

        $this->assertSame($sampleData['domain'], $responseCookie->getDomain());
        $this->assertInternalType('string', $responseCookie->getDomain());

        $responseCookie->setDomain($sampleDataOther['domain']);

        $this->assertSame($sampleDataOther['domain'], $responseCookie->getDomain());
        $this->assertInternalType('string', $responseCookie->getDomain());
    }

    /**
     * @dataProvider sampleDataProvider
     * @param array $defaults
     * @param array $sampleData
     * @param array $sampleDataOther
     */
    public function testSecureGetSet($defaults, $sampleData, $sampleDataOther)
    {
        $responseCookie = new ResponseCookie(
            $defaults['name'],
            null,
            null,
            null,
            null,
            $sampleData['secure']
        );

        $this->assertSame($sampleData['secure'], $responseCookie->getSecure());
        $this->assertInternalType('boolean', $responseCookie->getSecure());

        $responseCookie->setSecure($sampleDataOther['secure']);

        $this->assertSame($sampleDataOther['secure'], $responseCookie->getSecure());
        $this->assertInternalType('boolean', $responseCookie->getSecure());
    }

    /**
     * @dataProvider sampleDataProvider
     * @param array $defaults
     * @param array $sampleData
     * @param array $sampleDataOther
     */
    public function testHttpOnlyGetSet($defaults, $sampleData, $sampleDataOther)
    {
        $responseCookie = new ResponseCookie(
            $defaults['name'],
            null,
            null,
            null,
            null,
            null,
            $sampleData['http_only']
        );

        $this->assertSame($sampleData['http_only'], $responseCookie->getHttpOnly());
        $this->assertInternalType('boolean', $responseCookie->getHttpOnly());

        $responseCookie->setHttpOnly($sampleDataOther['http_only']);

        $this->assertSame($sampleDataOther['http_only'], $responseCookie->getHttpOnly());
        $this->assertInternalType('boolean', $responseCookie->getHttpOnly());
    }
}
