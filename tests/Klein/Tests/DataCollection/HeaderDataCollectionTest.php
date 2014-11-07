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

use Klein\DataCollection\HeaderDataCollection;
use Klein\Tests\AbstractKleinTest;

/**
 * HeaderDataCollectionTest
 */
class HeaderDataCollectionTest extends AbstractKleinTest
{

    /**
     * Non existent key in the sample data
     *
     * @type string
     */
    protected static $nonexistent_key = 'non-standard-header';


    /*
     * Data Providers and Methods
     */

    /**
     * Quickly makes sure that no sample data arrays
     * have any keys that match the "nonexistent_key"
     *
     * @param array $sample_data
     * @return void
     */
    protected function prepareSampleData(&$sample_data)
    {
        if (isset($sample_data[static::$nonexistent_key])) {
            unset($sample_data[static::$nonexistent_key]);
        }

        foreach ($sample_data as &$data) {
            if (is_array($data)) {
                $this->prepareSampleData($data);
            }
        }
    }

    /**
     * Sample data provider
     *
     * @return array
     */
    public function sampleDataProvider()
    {
        // Populate our sample data
        $sample_data = array(
            'HOST' => 'localhost:8000',
            'CONNECTION' => 'keep-alive',
            'CONTENT_LENGTH' => '137',
            'USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.31'
                .' (KHTML, like Gecko) Chrome/26.0.1410.64 Safari/537.31',
            'CACHE_CONTROL' => 'no-cache',
            'ORIGIN' => 'chrome-extension://fdmmgilgnpjigdojojpjoooidkmcomcm',
            'AUTHORIZATION' => 'Basic MTIzOjQ1Ng==',
            'CONTENT_TYPE' => 'multipart/form-data; boundary=----WebKitFormBoundaryDhtDHBYppyHdrZe7',
            'ACCEPT' => '*/*',
            'ACCEPT_ENCODING' => 'gzip,deflate,sdch',
            'ACCEPT_LANGUAGE' => 'en-US,en;q=0.8',
            'ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.3',
        );

        $this->prepareSampleData($sample_data);

        $data_collection = new HeaderDataCollection($sample_data);

        return array(
            array($sample_data, $data_collection),
        );
    }


    /*
     * Tests
     */

    /**
     * @dataProvider sampleDataProvider
     */
    public function testConstructorCorrectlyFormatted($sample_data, $data_collection)
    {
        $this->assertNotSame($sample_data, $data_collection->all());
        $this->assertArrayNotHasKey('HOST', $data_collection->all());
        $this->assertContains('localhost:8000', $data_collection->all());
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testGet($sample_data, $data_collection)
    {
        $default = 'WOOT!';

        $this->assertSame($sample_data['USER_AGENT'], $data_collection->get('user-agent'));
        $this->assertSame($default, $data_collection->get(static::$nonexistent_key, $default));
        $this->assertNull($data_collection->get(static::$nonexistent_key));
    }

    public function testSet()
    {
        // Test data
        $data = array(
            'DOG_NAME' => 'cooper',
        );

        // Create our collection with NO data
        $data_collection = new HeaderDataCollection();

        // Set our data from our test data
        $data_collection->set(key($data), current($data));

        // Make sure the set worked, but the key is different
        $this->assertSame(current($data), $data_collection->get(key($data)));
        $this->assertArrayNotHasKey(key($data), $data_collection->all());
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testExists($sample_data, $data_collection)
    {
        // Make sure the set worked, but the key is different
        $this->assertTrue($data_collection->exists('HOST'));
        $this->assertFalse($data_collection->exists(static::$nonexistent_key));
        $this->assertArrayNotHasKey('HOST', $data_collection->all());
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testRemove($sample_data, $data_collection)
    {
        $this->assertTrue($data_collection->exists('HOST'));
        $this->assertArrayNotHasKey('HOST', $data_collection->all());

        $data_collection->remove('HOST');

        $this->assertFalse($data_collection->exists('HOST'));
    }

    public function testNameNormalizing()
    {
        // Test data
        $data = array(
            'DOG_NAME' => 'cooper',
        );

        // Create our collection with NO data
        $normalized_key = HeaderDataCollection::normalizeName(key($data));
        $normalized_val = HeaderDataCollection::normalizeName(current($data));

        $this->assertNotSame(key($data), $normalized_key);
        $this->assertSame(current($data), $normalized_val);

        $normalized_key_without_case_change = HeaderDataCollection::normalizeName(key($data), false);

        $this->assertTrue(strpos($normalized_key_without_case_change, 'D') !== false);
        $this->assertTrue(strpos($normalized_key_without_case_change, 'd') === false);

        $this->assertTrue(strpos($normalized_key, 'd') !== false);
        $this->assertTrue(strpos($normalized_key, 'D') === false);
    }
}
