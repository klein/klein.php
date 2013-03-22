<?php

require_once dirname(__FILE__) . '/setup.php';

use \Klein\Klein;

class ResponsesTest extends PHPUnit_Framework_TestCase {

	/**
	 * Class properties
	 */
	protected $header_vals = array();

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

		$klein = new Klein( new HeadersSave( $this->header_vals ) );

		$klein->respond( '/json', function( $request, $response ) use ( $test_object ) {
			$response->json( $test_object );
		});
		$klein->dispatch( '/json' );

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
