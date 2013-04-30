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

        $this->assertOutputSame(
            $custom_message,
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/test')
                );
            }
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

        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/ab')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/test')
                );
            }
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

        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/dog')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/dogg')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/doggg')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/t')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/te')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/testin')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/testing')
                );
            }
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

        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/2')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/12318935')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/2.5')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/2,5')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/~2')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/2 5')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/test')
                );
            }
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

        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/2')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/2.5')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/3.14')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/2.')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/2,5')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/~2')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/2 5')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/test')
                );
            }
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

        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/test@test.com')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/test@test.co.uk')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/test')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/test@')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/2 5')
                );
            }
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

        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/test')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/Test')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/TesT')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/test1')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/1test')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/@test')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/-test')
                );
            }
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

        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/test')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/Test')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/TesT')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/test1')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/1test')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/@test')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/-test')
                );
            }
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

        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/bigdog')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/dogbig')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/cat-dog')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/catdogbear')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/DOG')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/doog')
                );
            }
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

        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/cdef')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/cfed')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/cf')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/cdefg')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/dog')
                );
            }
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

        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/cat-dog')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/cat-bear')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/cat-thing')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/cat')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/cat-')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/dog-cat')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/catdog')
                );
            }
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

        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/cat')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/cat-')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/dog-cat')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/catdog')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/cat-dog')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/cat-bear')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/cat-thing')
                );
            }
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

        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/browndonkey')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/brown-donkey')
                );
            }
        );
        $this->assertOutputSame(
            'yup!',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/brown_donkey')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/bluedonkey')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/blue-donkey')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/blue_donkey')
                );
            }
        );
        $this->assertOutputSame(
            'fail',
            function () {
                $this->klein_app->dispatch(
                    MockRequestFactory::create('/brown_donk')
                );
            }
        );
    }
}
