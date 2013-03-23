<?php

require_once dirname(__FILE__) . '/AbstractKleinTest.php';

class ValidationsTest extends AbstractKleinTest {

	public function setUp() {
		parent::setUp();

        // Setup our error handler
        respond( function( $request, $response ) {
            $response->onError( array( $this, 'errorHandler' ) );
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

		respond( '/[:test_param]', function( $request ) use ( $custom_message ) {
			$request->validate( 'test_param', $custom_message )
			        ->notNull()
			        ->isLen( 0 );

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( $custom_message, function(){ dispatch('/test'); });
    }

	public function testStringLengthExact() {
		respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->isLen( 2 );

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ dispatch('/ab'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/test'); });
	}

	public function testStringLengthRange() {
		respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->isLen( 3, 5 );

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ dispatch('/dog'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/dogg'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/doggg'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/t'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/te'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/testin'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/testing'); });
	}

	public function testInt() {
		respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->isInt();

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ dispatch('/2'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/12318935'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/2.5'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/2,5'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/~2'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/2 5'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/test'); });
	}

	public function testFloat() {
		respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->isFloat();

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ dispatch('/2'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/2.5'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/3.14'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/2.'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/2,5'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/~2'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/2 5'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/test'); });
	}

	public function testEmail() {
		respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->isEmail();

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ dispatch('/test@test.com'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/test@test.co.uk'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/test'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/test@'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/2 5'); });
	}

	public function testAlpha() {
		respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->isAlpha();

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ dispatch('/test'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/Test'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/TesT'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/test1'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/1test'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/@test'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/-test'); });
	}

	public function testAlnum() {
		respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->isAlnum();

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ dispatch('/test'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/Test'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/TesT'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/test1'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/1test'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/@test'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/-test'); });
	}

	public function testContains() {
		respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->contains( 'dog' );

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ dispatch('/bigdog'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/dogbig'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/cat-dog'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/catdogbear'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/DOG'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/doog'); });
	}

	public function testChars() {
		respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->isChars( 'c-f' );

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ dispatch('/cdef'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/cfed'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/cf'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/cdefg'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/dog'); });
	}

	public function testRegex() {
		respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->isRegex( '/cat-[dog|bear|thing]/' );

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ dispatch('/cat-dog'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/cat-bear'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/cat-thing'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/cat'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/cat-'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/dog-cat'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/catdog'); });
	}

	public function testNotRegex() {
		respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->notRegex( '/cat-[dog|bear|thing]/' );

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ dispatch('/cat'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/cat-'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/dog-cat'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/catdog'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/cat-dog'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/cat-bear'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/cat-thing'); });
	}

	public function testCustomValidator() {
        // Add our custom validator
        addValidator( 'donkey', function( $string, $color ) {
            $regex_str = $color . '[-_]?donkey';

            return preg_match( '/' . $regex_str . '/', $string );
        });

		respond( '/[:test_param]', function( $request ) {
			$request->validate( 'test_param' )
			        ->notNull()
			        ->isDonkey( 'brown' );

            // We should only get here if we passed our validations
            echo 'yup!';
		} );

		$this->assertOutputSame( 'yup!', function(){ dispatch('/browndonkey'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/brown-donkey'); });
		$this->assertOutputSame( 'yup!', function(){ dispatch('/brown_donkey'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/bluedonkey'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/blue-donkey'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/blue_donkey'); });
		$this->assertOutputSame( 'fail', function(){ dispatch('/brown_donk'); });
	}

}
