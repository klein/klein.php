<?php

require_once dirname(__FILE__) . '/AbstractKleinTest.php';

class ResponsesTest extends AbstractKleinTest {

	/**
	 * Class properties
	 */
	protected $header_vals = array();

	public function setUp() {
		parent::setUp();

		$this->headers = new HeadersSave( $this->header_vals );
		_Request::$_headers = _Response::$_headers = new HeadersSave( $this->header_vals );
	}

	public function testJSON() {
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

		respond( '/json', function( $request, $response ) use ( $test_object ) {
			$response->json( $test_object );
		});
		dispatch( '/json' );

		// Expect our output to match our json encoded test object
		$this->expectOutputString(
			json_encode( $test_object )
		);

		// Assert headers were passed
		$this->assertContains(
			'Pragma: no-cache' . "\n",
			$this->header_vals
		);
		$this->assertContains(
			'Cache-Control: no-store, no-cache' . "\n",
			$this->header_vals
		);
		$this->assertContains(
			'Content-Type: application/json' . "\n",
			$this->header_vals
		);
	}

}
