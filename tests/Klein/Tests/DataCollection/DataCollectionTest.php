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

use \stdClass;
use \Klein\Tests\AbstractKleinTest;
use \Klein\DataCollection\DataCollection;

/**
 * DataCollectionTest
 *
 * @uses AbstractKleinTest
 * @package Klein\Tests\DataCollection
 */
class DataCollectionTest extends AbstractKleinTest
{

    /**
     * Non existent key in the sample data
     *
     * @static
     * @var string
     * @access protected
     */
    protected static $nonexistent_key = 'key-name-doesnt-exist';


    /*
     * Data Providers and Methods
     */

    /**
     * Quickly makes sure that no sample data arrays
     * have any keys that match the "nonexistent_key"
     *
     * @param array $sample_data
     * @access protected
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
     * @access public
     * @return array
     */
    public function sampleDataProvider()
    {
        // Populate our sample data
        $sample_data = array(
            'id' => 1337,
            'name' => array(
                'first' => 'Trevor',
                'last'  => 'Suarez',
            ),
            'float' => 13.37,
            'thing' => new stdClass(),
        );

        $this->prepareSampleData($sample_data);

        $data_collection = new DataCollection($sample_data);

        return array(
            array($sample_data, $data_collection),
        );
    }

    /**
     * Totally different sample data provider
     *
     * @access public
     * @return array
     */
    public function totallyDifferentSampleDataProvider()
    {
        // Populate our sample data
        $totally_different_sample_data = array(
            '_why' => 'the lucky stiff',
            'php'  => 'has become beautiful',
            'yay'  => 'life is very good. :)',
        );

        $this->prepareSampleData($totally_different_sample_data);

        return array(
            array($totally_different_sample_data),
        );
    }


    /*
     * Tests
     */

    /**
     * @dataProvider sampleDataProvider
     */
    public function testAll($sample_data, $data_collection)
    {
        // Test basic data similarity
        $this->assertSame($sample_data, $data_collection->all());

        // Create mask
        $mask = array('float', static::$nonexistent_key);

        $this->assertArrayHasKey($mask[0], $data_collection->all($mask));
        $this->assertArrayHasKey($mask[1], $data_collection->all($mask));
        $this->assertArrayNotHasKey(key($sample_data), $data_collection->all($mask));

        // Test more "magical" way of inputting mask
        $this->assertArrayHasKey($mask[0], $data_collection->all($mask[0], $mask[1]));
        $this->assertArrayHasKey($mask[1], $data_collection->all($mask[0], $mask[1]));
        $this->assertArrayNotHasKey(key($sample_data), $data_collection->all($mask[0], $mask[1]));
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testGet($sample_data, $data_collection)
    {
        $default = 'WOOT!';

        $this->assertSame($sample_data['id'], $data_collection->get('id'));
        $this->assertSame($default, $data_collection->get(static::$nonexistent_key, $default));
        $this->assertNull($data_collection->get(static::$nonexistent_key));
    }

    public function testSet()
    {
        // Test data
        $data = array(
            'dog' => 'cooper',
        );

        // Create our collection with NO data
        $data_collection = new DataCollection();

        // Make sure its first empty
        $this->assertSame(array(), $data_collection->all());

        // Set our data from our test data
        $return_val = $data_collection->set(key($data), current($data));

        // Make sure the set worked
        $this->assertSame(current($data), $data_collection->get(key($data)));

        // Make sure it returned the instance during "set"
        $this->assertEquals($return_val, $data_collection);
        $this->assertSame($return_val, $data_collection);
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testReplace($sample_data, $data_collection)
    {
        $totally_different_sample_data = current(
            current($this->totallyDifferentSampleDataProvider())
        );

        $data_collection->replace($totally_different_sample_data);

        $this->assertNotSame($sample_data, $totally_different_sample_data);
        $this->assertNotSame($sample_data, $data_collection->all());
        $this->assertSame($totally_different_sample_data, $data_collection->all());
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testMerge($sample_data, $data_collection)
    {
        $totally_different_sample_data = current(
            current($this->totallyDifferentSampleDataProvider())
        );

        $merged_data = array_merge($sample_data, $totally_different_sample_data);

        $data_collection->merge($totally_different_sample_data);

        $this->assertNotSame($sample_data, $totally_different_sample_data);
        $this->assertNotSame($sample_data, $data_collection->all());
        $this->assertNotSame($totally_different_sample_data, $data_collection->all());
        $this->assertSame($merged_data, $data_collection->all());
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testMergeHard($sample_data, $data_collection)
    {
        $totally_different_sample_data = current(
            current($this->totallyDifferentSampleDataProvider())
        );

        $replaced_data = array_replace($sample_data, $totally_different_sample_data);

        $data_collection->merge($totally_different_sample_data, true);

        $this->assertNotSame($sample_data, $totally_different_sample_data);
        $this->assertNotSame($sample_data, $data_collection->all());
        $this->assertNotSame($totally_different_sample_data, $data_collection->all());
        $this->assertSame($replaced_data, $data_collection->all());
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testExists($sample_data, $data_collection)
    {
        $this->assertTrue($data_collection->exists(key($sample_data)));
        $this->assertFalse($data_collection->exists(static::$nonexistent_key));
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testRemove($sample_data, $data_collection)
    {
        $this->assertTrue($data_collection->exists(key($sample_data)));

        $data_collection->remove(key($sample_data));

        $this->assertFalse($data_collection->exists(key($sample_data)));
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testMagicGet($sample_data, $data_collection)
    {
        $this->assertSame($sample_data['float'], $data_collection->float);
        $this->assertNull($data_collection->{static::$nonexistent_key});
    }

    public function testMagicSet()
    {
        // Test data
        $data = array(
            'dog' => 'cooper',
        );

        // Create our collection with NO data
        $data_collection = new DataCollection();

        // Set our data from our test data
        $data_collection->{key($data)} = current($data);

        // Make sure the set worked
        $this->assertSame(current($data), $data_collection->get(key($data)));
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testMagicIsset($sample_data, $data_collection)
    {
        $this->assertTrue(isset($data_collection->{key($sample_data)}));
        $this->assertFalse(isset($data_collection->{static::$nonexistent_key}));
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testMagicUnset($sample_data, $data_collection)
    {
        $this->assertTrue(isset($data_collection->{key($sample_data)}));

        unset($data_collection->{key($sample_data)});

        $this->assertFalse(isset($data_collection->{key($sample_data)}));
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testIteratorAggregate($sample_data, $data_collection)
    {
        $filled_data = array();

        foreach ($data_collection as $key => $data) {
            $filled_data[$key] = $data;
        }

        $this->assertSame($filled_data, $sample_data);
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testArrayAccessGet($sample_data, $data_collection)
    {
        $this->assertSame($sample_data['float'], $data_collection['float']);
        $this->assertNull($data_collection[static::$nonexistent_key]);
    }

    public function testArrayAccessSet()
    {
        // Test data
        $data = array(
            'dog' => 'cooper',
        );

        // Create our collection with NO data
        $data_collection = new DataCollection();

        // Set our data from our test data
        $data_collection[key($data)] = current($data);

        // Make sure the set worked
        $this->assertSame(current($data), $data_collection->get(key($data)));
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testArrayAccessIsset($sample_data, $data_collection)
    {
        $this->assertTrue(isset($data_collection[key($sample_data)]));
        $this->assertFalse(isset($data_collection[static::$nonexistent_key]));
    }

    /**
     * @dataProvider sampleDataProvider
     */
    public function testArrayAccessUnset($sample_data, $data_collection)
    {
        $this->assertTrue(isset($data_collection[key($sample_data)]));

        unset($data_collection[key($sample_data)]);

        $this->assertFalse(isset($data_collection[key($sample_data)]));
    }
}
