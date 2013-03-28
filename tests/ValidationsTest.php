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


/**
 * ValidationsTest 
 * 
 * @uses AbstractKleinTest
 * @package Klein\Tests
 */
class ValidationsTest extends AbstractKleinTest {

	public function setUp() {
		parent::setUp();

        // Setup our error handler
        $this->klein_app->respond( function( $request, $response ) {
            $response->onError( array( $this, 'errorHandler' ), false );
        } );
	}

    public function errorHandler( $response, $message, $type, $exception ) {
        if ( !is_null( $message ) && !empty( $message ) ) {
            echo $message;
        }
        else {
            echo 'fail';
        }
    }

    public function testCustomValidationMessage() {
        $custom_message = 'This is a custom error message...';

		$this->klein_app->respond( '/[:test_param]', function( $request ) use ( $custom_message ) {
			$request->validate( 'test_param', $custom_message )
			        ->notNull()
			        ->isLen( 0 );

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( $custom_message, function(){ $this->klein_app->dispatch('/test'); });
    }

	public function testStringLengthExact() {
		$this->klein_app->respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->isLen( 2 );

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/ab'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/test'); });
	}

	public function testStringLengthRange() {
		$this->klein_app->respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->isLen( 3, 5 );

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/dog'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/dogg'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/doggg'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/t'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/te'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/testin'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/testing'); });
	}

	public function testInt() {
		$this->klein_app->respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->isInt();

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/2'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/12318935'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/2.5'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/2,5'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/~2'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/2 5'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/test'); });
	}

	public function testFloat() {
		$this->klein_app->respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->isFloat();

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/2'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/2.5'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/3.14'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/2.'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/2,5'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/~2'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/2 5'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/test'); });
	}

	public function testEmail() {
		$this->klein_app->respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->isEmail();

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/test@test.com'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/test@test.co.uk'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/test'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/test@'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/2 5'); });
	}

	public function testAlpha() {
		$this->klein_app->respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->isAlpha();

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/test'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/Test'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/TesT'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/test1'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/1test'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/@test'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/-test'); });
	}

	public function testAlnum() {
		$this->klein_app->respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->isAlnum();

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/test'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/Test'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/TesT'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/test1'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/1test'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/@test'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/-test'); });
	}

	public function testContains() {
		$this->klein_app->respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->contains( 'dog' );

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/bigdog'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/dogbig'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/cat-dog'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/catdogbear'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/DOG'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/doog'); });
	}

	public function testChars() {
		$this->klein_app->respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->isChars( 'c-f' );

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/cdef'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/cfed'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/cf'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/cdefg'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/dog'); });
	}

	public function testRegex() {
		$this->klein_app->respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->isRegex( '/cat-[dog|bear|thing]/' );

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/cat-dog'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/cat-bear'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/cat-thing'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/cat'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/cat-'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/dog-cat'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/catdog'); });
	}

	public function testNotRegex() {
		$this->klein_app->respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->notRegex( '/cat-[dog|bear|thing]/' );

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/cat'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/cat-'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/dog-cat'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/catdog'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/cat-dog'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/cat-bear'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/cat-thing'); });
	}

	public function testCustomValidator() {
        // Add our custom validator
        $this->klein_app->addValidator( 'donkey', function( $string, $color ) {
            $regex_str = $color . '[-_]?donkey';

            return preg_match( '/' . $regex_str . '/', $string );
        });

		$this->klein_app->respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->isDonkey( 'brown' );

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/browndonkey'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/brown-donkey'); });
		$this->assertOutputSame( 'yup!', function(){ $this->klein_app->dispatch('/brown_donkey'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/bluedonkey'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/blue-donkey'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/blue_donkey'); });
		$this->assertOutputSame( 'fail', function(){ $this->klein_app->dispatch('/brown_donk'); });
	}

} // End class ValidationsTest
