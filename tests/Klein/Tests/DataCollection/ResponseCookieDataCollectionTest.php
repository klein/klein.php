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

namespace Klein\Tests\DataCollection;

use DateTime;
use Klein\DataCollection\ResponseCookieDataCollection;
use Klein\ResponseCookie;
use Klein\Tests\AbstractKleinTest;

/**
 * ResponseCookieDataCollectionTest
 *
 * @uses AbstractKleinTest
 * @package Klein\Tests\DataCollection
 */
class ResponseCookieDataCollectionTest extends AbstractKleinTest
{
    /**
     * Sample data provider
     *
     * @return array
     */
    public function sampleDataProvider()
    {
        $sampleCookie = new ResponseCookie(
            'Trevor',
            'is a programmer',
            new DateTime(time() + 3600),
            '/',
            'example.com',
            false,
            false
        );

        $sampleOtherCookie = new ResponseCookie(
            'Chris',
            'is a boss',
            new DateTime(time() + 60),
            '/app/',
            'github.com',
            true,
            true
        );

        return array(
            array($sampleCookie, $sampleOtherCookie),
        );
    }

    /**
     * @dataProvider sampleDataProvider
     * @param array $sampleCookie
     */
    public function testSet($sampleCookie)
    {
        // Create our collection with NO data
        $dataCollection = new ResponseCookieDataCollection();

        // Set our data from our test data
        $dataCollection->set('first', $sampleCookie);

        $this->assertSame($sampleCookie, $dataCollection->get('first'));
        $this->assertTrue($dataCollection->get('first') instanceof ResponseCookie);
    }

    public function testSetStringConvertsToCookie()
    {
        // Create our collection with NO data
        $dataCollection = new ResponseCookieDataCollection();

        // Set our data from our test data
        $dataCollection->set('first', 'value');

        $this->assertNotSame('value', $dataCollection->get('first'));
        $this->assertTrue($dataCollection->get('first') instanceof ResponseCookie);
    }

    /**
     * @dataProvider sampleDataProvider
     * @param array $sampleCookie
     * @param array $sampleOtherCookie
     */
    public function testConstructorRoutesThroughSet($sampleCookie, $sampleOtherCookie)
    {
        $arrayOfCookieInstances = array(
            $sampleCookie,
            $sampleOtherCookie,
            new ResponseCookie('test'),
        );

        // Create our collection with NO data
        $dataCollection = new ResponseCookieDataCollection($arrayOfCookieInstances);
        $this->assertSame($arrayOfCookieInstances, $dataCollection->all());

        foreach ($dataCollection as $cookie) {
            $this->assertTrue($cookie instanceof ResponseCookie);
        }
    }
}
