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

use BadMethodCallException;
use Klein\Klein;
use Klein\Request;
use Klein\Response;
use Klein\Tests\Mocks\MockRequestFactory;
use Klein\Validator;

/**
 * ValidationsTest
 */
class ValidationsTest extends AbstractKleinTest
{

    public function setUp()
    {
        parent::setUp();

        // Bind objects to our service
        $this->klein_app->service()->bind(new Request(), new Response());

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

    protected function validator($string, $error_message = null)
    {
        return new Validator($string, $error_message);
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

    public function testUrl()
    {
        // Is
        $this->validator('http://www.test.com/path/file.ext?query=param#anchor')->isUrl();
        $this->validator('http://www.test.com/path/file.ext?query=param')->isUrl();
        $this->validator('http://www.test.com/path/file.ext#anchor')->isUrl();
        $this->validator('http://www.test.com/path/file.ext')->isUrl();
        $this->validator('http://www.test.com/path/')->isUrl();
        $this->validator('http://www.test.com/file.ext')->isUrl();
        $this->validator('http://www.test.com/page')->isUrl();
        $this->validator('http://test.com/')->isUrl();
        $this->validator('http://test.com')->isUrl();

        // Not
        $this->validator('test.com')->notUrl();
        $this->validator('test')->notUrl();
        $this->validator('www.com')->notUrl();
    }

    public function testIp()
    {
        // Is
        $this->validator('0000:0000:0000:0000:0000:0000:0000:0001')->isIp();
        $this->validator('2001:0db8:0000:0000:0000:ff00:0042:8329')->isIp();
        $this->validator('2001:db8:0:0:0:ff00:42:8329')->isIp();
        $this->validator('2001:db8::ff00:42:8329')->isIp();
        $this->validator('::ffff:192.0.2.128')->isIp();
        $this->validator('192.168.1.1')->isIp();
        $this->validator('192.168.0.1')->isIp();
        $this->validator('10.0.0.1')->isIp();
        $this->validator('169.254.0.0')->isIp();
        $this->validator('127.0.0.1')->isIp();
        $this->validator('0.0.0.0')->isIp();

        // Not
        $this->validator('0')->notIp();
        $this->validator('10')->notIp();
        $this->validator('10,000')->notIp();
        $this->validator('string')->notIp();
    }

    public function testRemoteIp()
    {
        // Is
        $this->validator('2001:0db5:86a3:0000:0000:8a2e:0370:7335')->isRemoteIp();
        $this->validator('ff02:0:0:0:0:1:ff00::')->isRemoteIp();
        $this->validator('2001:db8::ff00:42:8329')->isRemoteIp();
        $this->validator('::ffff:192.0.2.128')->isRemoteIp();
        $this->validator('74.125.226.192')->isRemoteIp();
        $this->validator('204.232.175.90')->isRemoteIp();
        $this->validator('98.139.183.24')->isRemoteIp();
        $this->validator('205.186.173.52')->isRemoteIp();

        // Not
        $this->validator('192.168.1.1')->notRemoteIp();
        $this->validator('192.168.0.1')->notRemoteIp();
        $this->validator('10.0.0.1')->notRemoteIp();
        $this->validator('169.254.0.0')->notRemoteIp();
        $this->validator('127.0.0.1')->notRemoteIp();
        $this->validator('0.0.0.0')->notRemoteIp();
        $this->validator('0')->notRemoteIp();
        $this->validator('10')->notRemoteIp();
        $this->validator('10,000')->notRemoteIp();
        $this->validator('string')->notRemoteIp();
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

    public function testCustomValidatorWithManyArgs()
    {
        // Add our custom validator
        $this->klein_app->service()->addValidator(
            'booleanEqual',
            function ($string, $args) {
                // Get the args
                $args = func_get_args();
                array_shift($args);

                $previous = null;

                foreach ($args as $arg) {
                    if (null !== $previous) {
                        if ((bool) $arg !== (bool) $previous) {
                            return false;
                        }
                    } else {
                        $previous = $arg;
                    }
                }

                return true;
            }
        );

        $this->klein_app->service()->validateParam('tRUe')
            ->isBooleanEqual(1, true, 'true');

        $this->klein_app->service()->validateParam('false')
            ->isBooleanEqual(0, null, '', array(), '0', false);
    }

    public function testValidatorReturnsResult()
    {
        $result = $this->klein_app->service()->validateParam('12', false)
            ->isInt();

        $this->assertNotNull($result);
        $this->assertFalse($result);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testValidatorThatDoesntExist()
    {
        $result = $this->klein_app->service()->validateParam('12')
            ->isALongNameOfAThingThatDoesntExist();
    }
}
