<?php

require_once dirname(__FILE__) . '/setup.php';

class TestClass {
	static function GET($r, $r, $a) {
		echo 'ok';
	}
}

class RoutesTest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		global $__routes;
		$__routes = array();

		global $__namespace;
		$__namespace = null;

		$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
		$_SERVER['PHPUNIT'] = true;
	}

	public function testBasic() {
		$this->expectOutputString( 'x' );

		respond( '/', function(){ echo 'x'; });
		respond( '/something', function(){ echo 'y'; });
		dispatch( '/' );
	}

	public function testCallable() {
		$this->expectOutputString( 'okok' );
		respond( '/', array('TestClass', 'GET'));
		respond( '/', 'TestClass::GET');
		dispatch( '/' );
	}

	public function testAppReference() {
		$this->expectOutputString( 'ab' );
		respond( '/', function($r, $r ,$a){ $a->state = 'a'; });
		respond( '/', function($r, $r ,$a){ $a->state .= 'b'; });
		respond( '/', function($r, $r ,$a){ print $a->state; });
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

	public function test404TriggersOnce() {
		$this->expectOutputString( 'd404 Code' );

		respond( function(){ echo "d"; } );
		respond( '404', function(){ echo '404 Code'; } );
		dispatch( '/notroute' );
	}

	public function testStarRouteTriggers404() {
		$this->expectOutputString( 'c404 Code' );

		respond( '*', function(){ echo 'c'; });
		respond( '404', function(){ echo '404 Code'; } );
		dispatch( '/notroute' );
	}

	public function testNullRouteTriggers404() {
		$this->expectOutputString( 'c404 Code' );

		respond( function(){ echo 'c'; });
		respond( '404', function(){ echo '404 Code'; } );
		dispatch( '/notroute' );
	}

	public function testMethodSingle() {
		$this->expectOutputString( 'd' );

		respond( "GET",  "/a", function(){ echo 'd'; });
		respond( "POST", "/a", function(){ echo 'e'; });
		dispatch( '/a' );
	}

	public function testMethodMultiple() {
		$this->expectOutputString( 'd' );

		respond( "GET|POST",  "/a", function(){ echo 'd'; });
		dispatch( '/a' );
	}

	public function testGetUrl() {
		$expect = "";

		respond('home', 'GET|POST','/', function(){});
		respond('GET','/users/', function(){});
		respond('users_show', 'GET','/users/[i:id]', function(){});
		respond('users_do', 'POST','/users/[i:id]/[delete|update:action]', function(){});
		respond('posts_do', 'GET', '/posts/[create|edit:action]?/[i:id]?', function(){});

		echo getUrl('home'); echo "\n";
		$expect .= "/" . "\n";
		echo getUrl('users_show', array('id' => 14)); echo "\n";
		$expect .= "/users/14" . "\n";
		echo getUrl('users_do', array('id' => 17, 'action'=>'delete')); echo "\n";
		$expect .= "/users/17/delete" . "\n";
		echo getUrl('posts_do', array('id' => 16)); echo "\n";
		$expect .= "/posts/16" . "\n";
		echo getUrl('posts_do', array('action' => 'edit', 'id' => 15)); echo "\n";
		$expect .= "/posts/edit/15" . "\n";
		$this->expectOutputString( $expect );
	}

	public function testOptsParam() {
		$this->expectOutputString( "action=,id=16" );
		respond('users_do', 'GET','/posts/[create|edit:action]?/[i:id]?', function($rq,$rs,$ap){echo "action=".$rq->param("action").",id=".$rq->param("id");});

		dispatch("/posts/16");
	}

	public function testGetUrlPlaceHolders() {
		$expect = "";

		respond('home', 'GET|POST','/', function(){});
		respond('GET','/users/', function(){});
		respond('users_show', 'GET','/users/[i:id]', function(){});
		respond('posts_do', 'GET', '/posts/[create|edit:action]?/[i:id]?', function(){});

		echo getUrl('home', true); echo "\n";
		$expect .= "/" . "\n";
		echo getUrl('users_show', array('id' => 14), true); echo "\n";
		$expect .= "/users/14" . "\n";
		echo getUrl('users_show', array(), true); echo "\n";
		$expect .= "/users/[:id]" . "\n";
		echo getUrl('users_show', true); echo "\n";
		$expect .= "/users/[:id]" . "\n";
		echo getUrl('posts_do', array('action' => 'edit', 'id' => 15), true); echo "\n";
		$expect .= "/posts/edit/15" . "\n";
		echo getUrl('posts_do', array('id' => 15), true); echo "\n";
		$expect .= "/posts/[:action]/15" . "\n";
		echo getUrl('posts_do', array('action' => "edit"), true); echo "\n";
		$expect .= "/posts/edit/[:id]" . "\n";
		$this->expectOutputString( $expect );
	}


	public function testPlaceHoldersException1() {
		$this->setExpectedException('OutOfRangeException', "does not exist");

		respond('users', 'GET','/users/[i:id]/[:action]', function(){});

		echo getUrl('notset');
	}

	public function testPlaceHoldersException2() {
		$this->setExpectedException('InvalidArgumentException', "not set for route");

		respond('users', 'GET','/users/[i:id]/[:action]', function(){});

		echo getUrl('users', array('id' => "10"));
	}

	public function testDot1() {
		$this->expectOutputString( 'matchA:slug=ABCD_E--matchB:slug=ABCD_E--' );

		respond('/[*:cpath]/[:slug].[:format]',   function($rq){ echo 'matchA:slug='.$rq->param("slug").'--';});
		respond('/[*:cpath]/[:slug].[:format]?',  function($rq){ echo 'matchB:slug='.$rq->param("slug").'--';});
		respond('/[*:cpath]/[a:slug].[:format]?', function($rq){ echo 'matchC:slug='.$rq->param("slug").'--';});
		dispatch("/category1/categoryX/ABCD_E.php");
	}

	public function testDot2() {
		$this->expectOutputString( 'matchB:slug=ABCD_E--' );

		respond('/[*:cpath]/[:slug].[:format]',   function($rq){ echo 'matchA:slug='.$rq->param("slug").'--';});
		respond('/[*:cpath]/[:slug].[:format]?',  function($rq){ echo 'matchB:slug='.$rq->param("slug").'--';});
		respond('/[*:cpath]/[a:slug].[:format]?', function($rq){ echo 'matchC:slug='.$rq->param("slug").'--';});
		dispatch("/category1/categoryX/ABCD_E");
	}

}
