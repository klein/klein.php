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
use \Klein\Tests\Mocks\MockRequestFactory;

/**
 * ValidationsTest 
 * 
 * @uses AbstractKleinTest
 * @package Klein\Tests
 */
class ValidationsTest extends AbstractKleinTest
{

    public function setUp()
    {
        parent::setUp();

        // Setup our error handler
        $this->klein_app->onError(array($this, 'errorHandler'), false);
    }

    public function errorHandler($response, $message, $type, $exception)
    {
        if (!is_null($message) && !empty($message)) {
            echo $message;
        } else {
            echo 'fail';
        }
    }

    public function testCustomValidationMessage()
    {
        $custom_message = 'This is a custom error message...';

        $this->klein_app->respond(
            '/[:test_param]',
            function ($request, $response, $service) use ($custom_message) {
                $service->validateParam('test_param', $custom_message)
                    ->notNull()
                    ->isLen(0);

                // We should only get here if we passed our validations
                echo 'yup!';
            }
        );

        $this->assertSame(
            $custom_message,
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/test')
            )
        );
    }

    public function testStringLengthExact()
    {
        $this->klein_app->respond(
            '/[:test_param]',
            function ($request, $response, $service) {
                $service->validateParam('test_param')
                    ->notNull()
                    ->isLen(2);

                // We should only get here if we passed our validations
                echo 'yup!';
            }
        );

        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/ab')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/test')
            )
        );
    }

    public function testStringLengthRange()
    {
        $this->klein_app->respond(
            '/[:test_param]',
            function ($request, $response, $service) {
                $service->validateParam('test_param')
                    ->notNull()
                    ->isLen(3, 5);

                // We should only get here if we passed our validations
                echo 'yup!';
            }
        );

        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/dog')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/dogg')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/doggg')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/t')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/te')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/testin')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/testing')
            )
        );
    }

    public function testInt()
    {
        $this->klein_app->respond(
            '/[:test_param]',
            function ($request, $response, $service) {
                $service->validateParam('test_param')
                    ->notNull()
                    ->isInt();

                // We should only get here if we passed our validations
                echo 'yup!';
            }
        );

        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/2')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/12318935')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/2.5')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/2,5')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/~2')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/2 5')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/test')
            )
        );
    }

    public function testFloat()
    {
        $this->klein_app->respond(
            '/[:test_param]',
            function ($request, $response, $service) {
                $service->validateParam('test_param')
                    ->notNull()
                    ->isFloat();

                // We should only get here if we passed our validations
                echo 'yup!';
            }
        );

        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/2')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/2.5')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/3.14')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/2.')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/2,5')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/~2')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/2 5')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/test')
            )
        );
    }

    public function testEmail()
    {
        $this->klein_app->respond(
            '/[:test_param]',
            function ($request, $response, $service) {
                $service->validateParam('test_param')
                    ->notNull()
                    ->isEmail();

                // We should only get here if we passed our validations
                echo 'yup!';
            }
        );

        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/test@test.com')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/test@test.co.uk')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/test')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/test@')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/2 5')
            )
        );
    }

    public function testAlpha()
    {
        $this->klein_app->respond(
            '/[:test_param]',
            function ($request, $response, $service) {
                $service->validateParam('test_param')
                    ->notNull()
                    ->isAlpha();

                // We should only get here if we passed our validations
                echo 'yup!';
            }
        );

        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/test')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/Test')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/TesT')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/test1')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/1test')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/@test')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/-test')
            )
        );
    }

    public function testAlnum()
    {
        $this->klein_app->respond(
            '/[:test_param]',
            function ($request, $response, $service) {
                $service->validateParam('test_param')
                    ->notNull()
                    ->isAlnum();

                // We should only get here if we passed our validations
                echo 'yup!';
            }
        );

        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/test')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/Test')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/TesT')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/test1')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/1test')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/@test')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/-test')
            )
        );
    }

    public function testContains()
    {
        $this->klein_app->respond(
            '/[:test_param]',
            function ($request, $response, $service) {
                $service->validateParam('test_param')
                    ->notNull()
                    ->contains('dog');

                // We should only get here if we passed our validations
                echo 'yup!';
            }
        );

        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/bigdog')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/dogbig')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/cat-dog')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/catdogbear')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/DOG')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/doog')
            )
        );
    }

    public function testChars()
    {
        $this->klein_app->respond(
            '/[:test_param]',
            function ($request, $response, $service) {
                $service->validateParam('test_param')
                    ->notNull()
                    ->isChars('c-f');

                // We should only get here if we passed our validations
                echo 'yup!';
            }
        );

        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/cdef')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/cfed')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/cf')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/cdefg')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/dog')
            )
        );
    }

    public function testRegex()
    {
        $this->klein_app->respond(
            '/[:test_param]',
            function ($request, $response, $service) {
                $service->validateParam('test_param')
                    ->notNull()
                    ->isRegex('/cat-[dog|bear|thing]/');

                // We should only get here if we passed our validations
                echo 'yup!';
            }
        );

        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/cat-dog')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/cat-bear')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/cat-thing')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/cat')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/cat-')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/dog-cat')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/catdog')
            )
        );
    }

    public function testNotRegex()
    {
        $this->klein_app->respond(
            '/[:test_param]',
            function ($request, $response, $service) {
                $service->validateParam('test_param')
                    ->notNull()
                    ->notRegex('/cat-[dog|bear|thing]/');

                // We should only get here if we passed our validations
                echo 'yup!';
            }
        );

        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/cat')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/cat-')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/dog-cat')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/catdog')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/cat-dog')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/cat-bear')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/cat-thing')
            )
        );
    }

    public function testCustomValidator()
    {
        // Add our custom validator
        $this->klein_app->service()->addValidator(
            'donkey',
            function ($string, $color) {
                $regex_str = $color . '[-_]?donkey';

                return preg_match('/' . $regex_str . '/', $string);
            }
        );

        $this->klein_app->respond(
            '/[:test_param]',
            function ($request, $response, $service) {
                $service->validateParam('test_param')
                    ->notNull()
                    ->isDonkey('brown');

                // We should only get here if we passed our validations
                echo 'yup!';
            }
        );

        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/browndonkey')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/brown-donkey')
            )
        );
        $this->assertSame(
            'yup!',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/brown_donkey')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/bluedonkey')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/blue-donkey')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/blue_donkey')
            )
        );
        $this->assertSame(
            'fail',
            $this->dispatchAndReturnOutput(
                MockRequestFactory::create('/brown_donk')
            )
        );
    }
}
