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

}
