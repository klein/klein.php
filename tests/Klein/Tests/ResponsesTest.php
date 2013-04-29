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


use \Klein\Klein;

use \Klein\Tests\Mocks\HeadersSave;
use \Klein\Tests\Mocks\MockRequestFactory;

/**
 * ResponsesTest 
 * 
 * @uses AbstractKleinTest
 * @package Klein\Tests
 */
class ResponsesTest extends AbstractKleinTest
{

    public function testJSON()
    {
        // Create a test object to be JSON encoded/decoded
        $test_object = (object) array(
            'cheese',
            'dog' => 'bacon',
            1.5 => 'should be 1 (thanks PHP casting...)',
            'integer' => 1,
            'double' => 1.5,
            '_weird' => true,
            'uniqid' => uniqid(),
        );

        $this->klein_app->respond(
            '/json',
            function ($request, $response) use ($test_object) {
                $response->json($test_object);
            }
        );

        $this->klein_app->dispatch(
            MockRequestFactory::create('/json')
        );

        // Expect our output to match our json encoded test object
        $this->expectOutputString(
            json_encode($test_object)
        );

        // Assert headers were passed
        $this->assertEquals(
            'no-cache',
            $this->klein_app->response()->headers()->get('Pragma')
        );
        $this->assertEquals(
            'no-store, no-cache',
            $this->klein_app->response()->headers()->get('Cache-Control')
        );
        $this->assertEquals(
            'application/json',
            $this->klein_app->response()->headers()->get('Content-Type')
        );
    }
}
