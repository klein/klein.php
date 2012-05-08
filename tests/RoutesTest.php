<?php

require_once dirname(__FILE__) . '/setup.php';

class RoutesTest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		global $__routes;
		$__routes = array();

		global $__namespace;
		$__namespace = null;
	}

	public function testBasic() {
		respond( '/', function(){ echo 'x'; });
		respond( '/something', function(){ echo 'y'; });

		$out = dispatch( '/', 'GET', null, true );
		$this->assertSame( 'x', $out );
	}

	public function testCatchall() {
		respond( function(){ echo 'a'; });
		respond( '/not', function(){ echo 'b'; });
		respond( '*', function(){ echo 'c'; });
		respond( '/something', function(){ echo 'd'; });
		respond( '*', function(){ echo 'e'; });
		respond( '/not', function(){ echo 'f'; });
		respond( function(){ echo 'g'; });

		$out = dispatch( '/something', 'GET', null, true );
		$this->assertSame( 'acdeg', $out );
	}

	public function testRegex() {
		respond( '@/bar', function(){ echo 'z'; });

		$out = dispatch( '/bar', 'GET', null, true );
		$this->assertSame( 'z', $out );
	}

	public function testRegexNegate() {
		respond( '!@/foo', function(){ echo 'y'; });

		$out = dispatch( '/bar', 'GET', null, true );
		$this->assertSame( 'y', $out );
	}

	public function test404() {
		$this->expectOutputString("HTTP/1.1 404\n");

		$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
		respond( '/', function(){ echo 'a'; } );

		dispatch( '/foo' );
	}

	public function testParamsBasic() {
		respond( '/[:color]', function($request){ echo $request->param('color'); });

		$out = dispatch( '/blue', 'GET', null, true );
		$this->assertSame( 'blue', $out );
	}

	public function testParamsIntegerSuccess() {
		respond( '/[i:age]', function($request){ var_dump( $request->param('age') ); });

		$out = dispatch( '/987', 'GET', null, true );
		$this->assertSame( 'string(3) "987"', trim($out) );
	}

	public function testParamsIntegerFail() {
		respond( '/[i:age]', function($request){ var_dump( $request->param('age') ); });
		respond( '404', function(){ echo '404'; } );

		$out = dispatch( '/blue', 'GET', null, true );
		$this->assertSame( '404', trim($out) );
	}
}
