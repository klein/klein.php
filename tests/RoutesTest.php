<?php

require_once dirname(__FILE__) . '/setup.php';

class RoutesTest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		global $__routes;
		$__routes = array();

		global $__namespace;
		$__namespace = null;

		$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
	}

	public function testBasic() {
		$this->expectOutputString( 'x' );

		respond( '/', function(){ echo 'x'; });
		respond( '/something', function(){ echo 'y'; });
		dispatch( '/' );
	}

	public function testCatchallImplicit() {
		$this->expectOutputString( 'b' );

		respond( '/one', function(){ echo 'a'; });
		respond( function(){ echo 'b'; });
		respond( '/two', function(){ } );
		respond( '/three', function(){ echo 'c'; } );
		dispatch( '/two' );
	}

	public function testCatchallAsterisk() {
		$this->expectOutputString( 'b' );

		respond( '/one', function(){ echo 'a'; } );
		respond( '*', function(){ echo 'b'; } );
		respond( '/two', function(){ } );
		respond( '/three', function(){ echo 'c'; } );
		dispatch( '/two' );
	}

	public function testCatchallImplicitTriggers404() {
		$this->expectOutputString( "bHTTP/1.1 404\n" );
		respond( function(){ echo 'b'; });
		dispatch( '/' );
	}

	public function testRegex() {
		$this->expectOutputString( 'z' );

		respond( '@/bar', function(){ echo 'z'; });
		dispatch( '/bar' );
	}

	public function testRegexNegate() {
		$this->expectOutputString( "y" );

		respond( '!@/foo', function(){ echo 'y'; });
		dispatch( '/bar' );
	}

	public function test404() {
		$this->expectOutputString("HTTP/1.1 404\n");

		respond( '/', function(){ echo 'a'; } );
		dispatch( '/foo' );
	}

	public function testParamsBasic() {
		$this->expectOutputString( 'blue' );

		respond( '/[:color]', function($request){ echo $request->param('color'); });
		dispatch( '/blue' );
	}

	public function testParamsIntegerSuccess() {
		$this->expectOutputString( "string(3) \"987\"\n" );

		respond( '/[i:age]', function($request){ var_dump( $request->param('age') ); });
		dispatch( '/987' );
	}

	public function testParamsIntegerFail() {
		$this->expectOutputString( '404 Code' );

		respond( '/[i:age]', function($request){ var_dump( $request->param('age') ); });
		respond( '404', function(){ echo '404 Code'; } );
		dispatch( '/blue' );
	}
}
