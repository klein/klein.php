<?php

require_once dirname(__FILE__) . '/AbstractKleinTest.php';

class TestClass {
	static function GET($r, $r, $a) {
		echo 'ok';
	}
}

use \Klein\Klein;

class RoutesTest extends AbstractKleinTest {

	protected function setUp() {
		parent::setUp();

		$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
	}

	public function testBasic() {
		$this->expectOutputString( 'x' );

		$klein = new Klein();

		$klein->respond( '/', function(){ echo 'x'; });
		$klein->respond( '/something', function(){ echo 'y'; });
		$klein->dispatch( '/' );
	}

	public function testCallable() {
		$this->expectOutputString( 'okok' );

		$klein = new Klein();

		$klein->respond( '/', array('TestClass', 'GET'));
		$klein->respond( '/', 'TestClass::GET');
		$klein->dispatch( '/' );
	}

	public function testAppReference() {
		$this->expectOutputString( 'ab' );

		$klein = new Klein();

		$klein->respond( '/', function($r, $r ,$a){ $a->state = 'a'; });
		$klein->respond( '/', function($r, $r ,$a){ $a->state .= 'b'; });
		$klein->respond( '/', function($r, $r ,$a){ print $a->state; });
		$klein->dispatch( '/' );
	}

	public function testCatchallImplicit() {
		$this->expectOutputString( 'b' );

		$klein = new Klein();

		$klein->respond( '/one', function(){ echo 'a'; });
		$klein->respond( function(){ echo 'b'; });
		$klein->respond( '/two', function(){ } );
		$klein->respond( '/three', function(){ echo 'c'; } );
		$klein->dispatch( '/two' );
	}

	public function testCatchallAsterisk() {
		$this->expectOutputString( 'b' );

		$klein = new Klein();

		$klein->respond( '/one', function(){ echo 'a'; } );
		$klein->respond( '*', function(){ echo 'b'; } );
		$klein->respond( '/two', function(){ } );
		$klein->respond( '/three', function(){ echo 'c'; } );
		$klein->dispatch( '/two' );
	}

	public function testCatchallImplicitTriggers404() {
		$this->expectOutputString("b404\n");

		$klein = new Klein();

		$klein->respond( function(){ echo 'b'; });
		$klein->respond( 404, function(){ echo "404\n"; } );
		$klein->dispatch( '/' );
	}

	public function testRegex() {
		$this->expectOutputString( 'z' );

		$klein = new Klein();

		$klein->respond( '@/bar', function(){ echo 'z'; });
		$klein->dispatch( '/bar' );
	}

	public function testRegexNegate() {
		$this->expectOutputString( "y" );

		$klein = new Klein();

		$klein->respond( '!@/foo', function(){ echo 'y'; });
		$klein->dispatch( '/bar' );
	}

	public function test404() {
		$this->expectOutputString("404\n");

		$klein = new Klein();

		$klein->respond( '/', function(){ echo 'a'; } );
		$klein->respond( 404, function(){ echo "404\n"; } );
		$klein->dispatch( '/foo' );
	}

	public function testParamsBasic() {
		$this->expectOutputString( 'blue' );

		$klein = new Klein();

		$klein->respond( '/[:color]', function($request){ echo $request->param('color'); });
		$klein->dispatch( '/blue' );
	}

	public function testParamsIntegerSuccess() {
		$this->expectOutputString( "string(3) \"987\"\n" );

		$klein = new Klein();

		$klein->respond( '/[i:age]', function($request){ var_dump( $request->param('age') ); });
		$klein->dispatch( '/987' );
	}

	public function testParamsIntegerFail() {
		$this->expectOutputString( '404 Code' );

		$klein = new Klein();

		$klein->respond( '/[i:age]', function($request){ var_dump( $request->param('age') ); });
		$klein->respond( '404', function(){ echo '404 Code'; } );
		$klein->dispatch( '/blue' );
	}

	public function testParamsAlphaNum() {
		$klein = new Klein();

		$klein->respond( '/[a:audible]', function($request){ echo $request->param('audible'); });

		$this->assertOutputSame( 'blue42',  function() use ($klein) { $klein->dispatch('/blue42'); });
		$this->assertOutputSame( '',        function() use ($klein) { $klein->dispatch('/texas-29'); });
		$this->assertOutputSame( '',        function() use ($klein) { $klein->dispatch('/texas29!'); });
	}

	public function testParamsHex() {
		$klein = new Klein();

		$klein->respond( '/[h:hexcolor]', function($request){ echo $request->param('hexcolor'); });

		$this->assertOutputSame( '00f',     function() use ($klein) { $klein->dispatch('/00f'); });
		$this->assertOutputSame( 'abc123',  function() use ($klein) { $klein->dispatch('/abc123'); });
		$this->assertOutputSame( '',        function() use ($klein) { $klein->dispatch('/876zih'); });
		$this->assertOutputSame( '',        function() use ($klein) { $klein->dispatch('/00g'); });
		$this->assertOutputSame( '',        function() use ($klein) { $klein->dispatch('/hi23'); });
	}

	public function test404TriggersOnce() {
		$this->expectOutputString( 'd404 Code' );

		$klein = new Klein();

		$klein->respond( function(){ echo "d"; } );
		$klein->respond( '404', function(){ echo '404 Code'; } );
		$klein->dispatch( '/notroute' );
	}

	public function testMethodCatchAll() {
		$this->expectOutputString( 'yup!123' );

		$klein = new Klein();

		$klein->respond( 'POST', null, function($request){ echo 'yup!'; });
		$klein->respond( 'POST', '*', function($request){ echo '1'; });
		$klein->respond( 'POST', '/', function($request){ echo '2'; });
		$klein->respond( function($request){ echo '3'; });
		$klein->dispatch( '/', 'POST' );
	}

	public function testLazyTrailingMatch() {
		$this->expectOutputString( 'this-is-a-title-123' );

		$klein = new Klein();

		$klein->respond( '/posts/[*:title][i:id]', function($request){
			echo $request->param('title')
				. $request->param('id');
		});
		$klein->dispatch( '/posts/this-is-a-title-123' );
	}

	public function testFormatMatch() {
		$this->expectOutputString( 'xml' );

		$klein = new Klein();

		$klein->respond( '/output.[xml|json:format]', function($request){
			echo $request->param('format');
		});
		$klein->dispatch( '/output.xml' );
	}

	public function testDotSeparator() {
		$this->expectOutputString( 'matchA:slug=ABCD_E--matchB:slug=ABCD_E--' );

		$klein = new Klein();

		$klein->respond('/[*:cpath]/[:slug].[:format]',   function($rq){ echo 'matchA:slug='.$rq->param("slug").'--';});
		$klein->respond('/[*:cpath]/[:slug].[:format]?',  function($rq){ echo 'matchB:slug='.$rq->param("slug").'--';});
		$klein->respond('/[*:cpath]/[a:slug].[:format]?', function($rq){ echo 'matchC:slug='.$rq->param("slug").'--';});
		$klein->dispatch("/category1/categoryX/ABCD_E.php");

		$this->assertOutputSame(
			'matchA:slug=ABCD_E--matchB:slug=ABCD_E--',
			function() use ($klein) { $klein->dispatch( '/category1/categoryX/ABCD_E.php' );}
		);
		$this->assertOutputSame(
			'matchB:slug=ABCD_E--',
			function() use ($klein) { $klein->dispatch( '/category1/categoryX/ABCD_E' );}
		);
	}

	public function testControllerActionStyleRouteMatch() {
		$this->expectOutputString( 'donkey-kick' );

		$klein = new Klein();

		$klein->respond( '/[:controller]?/[:action]?', function($request){
			echo $request->param('controller')
				. '-' . $request->param('action');
		});
		$klein->dispatch( '/donkey/kick' );
	}

	public function testRespondArgumentOrder() {
		$this->expectOutputString( 'abcdef' );

		$klein = new Klein();

		$klein->respond( function(){ echo 'a'; });
		$klein->respond( null, function(){ echo 'b'; });
		$klein->respond( '/endpoint', function(){ echo 'c'; });
		$klein->respond( 'GET', null, function(){ echo 'd'; });
		$klein->respond( array( 'GET', 'POST' ), null, function(){ echo 'e'; });
		$klein->respond( array( 'GET', 'POST' ), '/endpoint', function(){ echo 'f'; });
		$klein->dispatch( '/endpoint' );
	}

	public function testTrailingMatch() {
		$klein = new Klein();

		$klein->respond( '/?[*:trailing]/dog/?', function($request){ echo 'yup'; });

		$this->assertOutputSame( 'yup', function() use ($klein) { $klein->dispatch('/cat/dog'); });
		$this->assertOutputSame( 'yup', function() use ($klein) { $klein->dispatch('/cat/cheese/dog'); });
		$this->assertOutputSame( 'yup', function() use ($klein) { $klein->dispatch('/cat/ball/cheese/dog/'); });
		$this->assertOutputSame( 'yup', function() use ($klein) { $klein->dispatch('/cat/ball/cheese/dog'); });
		$this->assertOutputSame( 'yup', function() use ($klein) { $klein->dispatch('cat/ball/cheese/dog/'); });
		$this->assertOutputSame( 'yup', function() use ($klein) { $klein->dispatch('cat/ball/cheese/dog'); });
	}

	public function testTrailingPossessiveMatch() {
		$klein = new Klein();

		$klein->respond( '/sub-dir/[**:trailing]', function($request){ echo 'yup'; });

		$this->assertOutputSame( 'yup', function() use ($klein) { $klein->dispatch('/sub-dir/dog'); });
		$this->assertOutputSame( 'yup', function() use ($klein) { $klein->dispatch('/sub-dir/cheese/dog'); });
		$this->assertOutputSame( 'yup', function() use ($klein) { $klein->dispatch('/sub-dir/ball/cheese/dog/'); });
		$this->assertOutputSame( 'yup', function() use ($klein) { $klein->dispatch('/sub-dir/ball/cheese/dog'); });
	}

	public function testNSDispatch() {
		$klein = new Klein();

		$klein->with('/u', function () use ($klein) {
			$klein->respond('GET', '/?',     function ($request, $response) { echo "slash";   });
			$klein->respond('GET', '/[:id]', function ($request, $response) { echo "id"; });
		});
		$klein->respond(404, function ($request, $response) { echo "404"; });

		$this->assertOutputSame("slash",          function() use ($klein) { $klein->dispatch("/u");});
		$this->assertOutputSame("slash",          function() use ($klein) { $klein->dispatch("/u/");});
		$this->assertOutputSame("id",             function() use ($klein) { $klein->dispatch("/u/35");});
		$this->assertOutputSame("404",             function() use ($klein) { $klein->dispatch("/35");});
	}

	public function testNSDispatchExternal() {
		$klein = new Klein();

		$ext_namespaces = $this->loadExternalRoutes( $klein );

		$klein->respond(404, function ($request, $response) { echo "404"; });

		foreach ( $ext_namespaces as $namespace ) {
			$this->assertOutputSame('yup',  function() use ( $klein, $namespace ) { $klein->dispatch( $namespace . '/' ); });
			$this->assertOutputSame('yup',  function() use ( $klein, $namespace ) { $klein->dispatch( $namespace . '/testing/' ); });
		}
	}

	public function testNSDispatchExternalRerequired() {
		$klein = new Klein();

		$ext_namespaces = $this->loadExternalRoutes( $klein );

		$klein->respond(404, function ($request, $response) { echo "404"; });

		foreach ( $ext_namespaces as $namespace ) {
			$this->assertOutputSame('yup',  function() use ( $klein, $namespace ) { $klein->dispatch( $namespace . '/' ); });
			$this->assertOutputSame('yup',  function() use ( $klein, $namespace ) { $klein->dispatch( $namespace . '/testing/' ); });
		}
	}

	public function test405Routes() {
		$resultArray = array();

		$this->expectOutputString( '_' );

		$klein = new Klein();

		$klein->respond( function(){ echo '_'; });
		$klein->respond( 'GET', null, function(){ echo 'fail'; });
		$klein->respond( array( 'GET', 'POST' ), null, function(){ echo 'fail'; });
		$klein->respond( 405, function($a,$b,$c,$d,$methods) use ( &$resultArray ) {
			$resultArray = $methods;
		});
		$klein->dispatch( '/sure', 'DELETE' );

		$this->assertCount( 2, $resultArray );
		$this->assertContains( 'GET', $resultArray );
		$this->assertContains( 'POST', $resultArray );
	}

}
