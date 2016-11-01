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

use Klein\AbstractRouteFactory;
use Klein\Route;

/**
 * AbstractRouteFactoryTest
 */
class AbstractRouteFactoryTest extends AbstractKleinTest
{

    /**
     * Helpers
     */

    protected function getDefaultMethodsToMock()
    {
        return array(
            'build',
        );
    }

    protected function getMockForFactory()
    {
        return $this->getMockForAbstractClass('\Klein\AbstractRouteFactory');
    }

    protected function getMockBuilderForFactory(array $methods_to_mock = null)
    {
        $methods_to_mock = $methods_to_mock ?: $this->getDefaultMethodsToMock();

        return $this->getMockBuilder('\Klein\AbstractRouteFactory')
            ->setMethods($methods_to_mock);
    }


    /**
     * Tests
     */

    public function testNamespaceGetSet()
    {
        // Test data
        $test_namespace = '/users';

        // Empty constructor
        $factory = $this->getMockForFactory();

        $this->assertNull($factory->getNamespace());

        // Set in constructor
        $factory = $this->getMockBuilderForFactory()
            ->setConstructorArgs(
                array(
                    $test_namespace,
                )
            )
            ->getMock();

        $this->assertSame($test_namespace, $factory->getNamespace());

        // Set in method
        $factory = $this->getMockForFactory();
        $factory->setNamespace($test_namespace);

        $this->assertSame($test_namespace, $factory->getNamespace());
    }

    public function testAppendNamespace()
    {
        // Test data
        $test_namespace = '/users';
        $test_namespace_append = '/names';

        $factory = $this->getMockForFactory();
        $factory->setNamespace($test_namespace);
        $factory->appendNamespace($test_namespace_append);

        $this->assertSame(
            $test_namespace . $test_namespace_append,
            $factory->getNamespace()
        );
    }
}
