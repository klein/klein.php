<?php
namespace Klein\Tests;

use Klein\GetParameters;

/**
 * GetParametersTest
 *
 * @uses AbstractKleinTest
 * @package Klein\Tests
 */
class GetParametersTest extends AbstractKleinTest
{

    public function testReturnsArrayOfParametersFromFunction()
    {
        $params = GetParameters::forMethod('parameter_foobar');
        $this->assertEquals(array('foo', 'bar'), $params);
    }

    public function testFindsParametersFromInstance()
    {
        $instance = new Mocks\MockParameterFooBar;
        $params = GetParameters::forMethod(array($instance, 'barBaz'));
        $this->assertEquals(array('bar', 'baz'), $params);
    }

    public function testFindsParametersFromStaticMethod()
    {
        $params = GetParameters::forMethod(array(__NAMESPACE__ . '\Mocks\MockParameterFooBar', 'fooBarBaz'));
        $this->assertEquals(array('foo', 'bar', 'baz'), $params);
    }

    public function testReturnsEmptyArrayForMissingFunction()
    {
        $params = GetParameters::forMethod('missing_for_lols');
        $this->assertEquals(array(), $params);
    }
}
