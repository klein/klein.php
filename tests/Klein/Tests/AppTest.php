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

use Klein\App;

/**
 * AppTest
 *
 * @uses AbstractKleinTest
 * @package Klein\Tests
 */
class AppTest extends AbstractKleinTest
{
    const TEST_CALLBACK_MESSAGE = 'yay';

    public function testRegisterFiller()
    {
        $func_name = 'yay_func';

        $app = new App();

        $app->register($func_name, $this->getTestCallable());

        return array(
            'app' => $app,
            'func_name' => $func_name,
        );
    }

    protected function getTestCallable($message = self::TEST_CALLBACK_MESSAGE)
    {
        return function () use ($message) {
            return $message;
        };
    }

    /**
     * @depends testRegisterFiller
     */
    public function testGet(array $args)
    {
        // Get our vars from our args
        extract($args);

        $returned = $app->$func_name;

        $this->assertNotNull($returned);
        $this->assertSame(self::TEST_CALLBACK_MESSAGE, $returned);
    }

    /**
     * @expectedException \Klein\Exceptions\UnknownServiceException
     */
    public function testGetBadMethod()
    {
        $app = new App();
        $app->random_thing_that_doesnt_exist;
    }

    /**
     * @depends testRegisterFiller
     */
    public function testCall(array $args)
    {
        // Get our vars from our args
        extract($args);

        $returned = $app->{$func_name}();

        $this->assertNotNull($returned);
        $this->assertSame(self::TEST_CALLBACK_MESSAGE, $returned);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testCallBadMethod()
    {
        $app = new App();
        $app->random_thing_that_doesnt_exist();
    }

    /**
     * @depends testRegisterFiller
     * @expectedException \Klein\Exceptions\DuplicateServiceException
     */
    public function testRegisterDuplicateMethod(array $args)
    {
        // Get our vars from our args
        extract($args);

        $app->register($func_name, $this->getTestCallable());
    }
}
